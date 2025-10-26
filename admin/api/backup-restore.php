<?php
/**
 * Backup & Restore API
 * Handles database backup, restore, scheduling, and history
 */

require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

session_start();

// Get admin ID and verify it exists
$adminId = $_SESSION['admin_id'] ?? $_SESSION['AdminID'] ?? null;

// If we have an admin ID, verify it exists in the database
if ($adminId !== null) {
    try {
        $stmt = $pdo->prepare("SELECT AdminID FROM Admin WHERE AdminID = ?");
        $stmt->execute([$adminId]);
        if (!$stmt->fetch()) {
            // Admin ID doesn't exist, set to null
            $adminId = null;
        }
    } catch (Exception $e) {
        // If there's an error checking, set to null
        $adminId = null;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Database configuration
$dbHost = DB_HOST;
$dbName = DB_NAME;
$dbUser = DB_USER;
$dbPass = DB_PASS;

$backupDir = realpath(__DIR__ . '/../../storage/backups');
if (!$backupDir) { 
    $backupDir = __DIR__ . '/../../storage/backups';
}

if (!is_dir($backupDir)) { 
    @mkdir($backupDir, 0775, true); 
}

try {
    // Create Backup
    if ($action === 'create-backup') {
        $backupType = $_POST['backupType'] ?? 'full';
        $compression = $_POST['compression'] ?? 'none';
        $includeTables = $_POST['tables'] ?? [];
        $description = $_POST['description'] ?? '';
        
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_" . $backupType . "_" . $timestamp . ".sql";
        $filepath = $backupDir . '/' . $filename;
        
        // Determine mysqldump command - check multiple possible locations
        $possiblePaths = [
            'C:/xampp/mysql/bin/mysqldump.exe',
            'C:/xampp/mysql/bin/mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'mysqldump'
        ];
        
        $mysqldumpPath = 'mysqldump';
        foreach ($possiblePaths as $path) {
            if (file_exists($path) || ($path === 'mysqldump')) {
                $mysqldumpPath = $path;
                break;
            }
        }
        
        // Build command - don't redirect stderr to stdout, capture it separately
        $command = sprintf(
            '"%s" --host=%s --user=%s',
            $mysqldumpPath,
            escapeshellarg($dbHost),
            escapeshellarg($dbUser)
        );
        
        // Add password only if not empty
        if (!empty($dbPass)) {
            $command .= ' --password=' . escapeshellarg($dbPass);
        }
        
        if ($backupType === 'structure') {
            $command .= ' --no-data';
        } elseif ($backupType === 'data') {
            $command .= ' --no-create-info';
        }
        
        $command .= ' ' . escapeshellarg($dbName);
        
        if ($backupType === 'custom' && !empty($includeTables)) {
            foreach ($includeTables as $table) {
                $command .= ' ' . escapeshellarg($table);
            }
        }
        
        $command .= ' > ' . escapeshellarg($filepath) . ' 2>&1';
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filepath)) {
            $fileSize = filesize($filepath);
            
            // Apply compression
            if ($compression === 'zip' && class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                $zipFile = $filepath . '.zip';
                if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
                    $zip->addFile($filepath, $filename);
                    $zip->close();
                    unlink($filepath);
                    $filename .= '.zip';
                    $filepath = $zipFile;
                    $fileSize = filesize($zipFile);
                }
            } elseif ($compression === 'gzip' && function_exists('gzencode')) {
                $gzFile = $filepath . '.gz';
                $gz = gzopen($gzFile, 'w9');
                gzwrite($gz, file_get_contents($filepath));
                gzclose($gz);
                unlink($filepath);
                $filename .= '.gz';
                $filepath = $gzFile;
                $fileSize = filesize($gzFile);
            }
            
            // Save to history
            $stmt = $pdo->prepare("INSERT INTO BackupHistory (FileName, FileSize, BackupType, Description, CreatedBy, CreatedAt) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$filename, $fileSize, $backupType, $description, $adminId]);
            
            sendJson([
                'success' => true,
                'message' => 'Backup created successfully',
                'filename' => $filename,
                'size' => round($fileSize / 1024 / 1024, 2) . ' MB'
            ]);
        } else {
            // Better error reporting
            $errorMsg = 'Backup failed.';
            
            if ($returnCode !== 0) {
                $errorMsg .= ' Return code: ' . $returnCode;
            }
            
            if (!file_exists($filepath)) {
                $errorMsg .= ' Backup file was not created.';
            } else {
                $fileSize = filesize($filepath);
                if ($fileSize === 0) {
                    $errorMsg .= ' Backup file is empty.';
                }
            }
            
            // Try to read error from the output file if it exists
            if (file_exists($filepath) && filesize($filepath) > 0) {
                $fileContent = file_get_contents($filepath);
                if (strpos($fileContent, 'ERROR') !== false || strpos($fileContent, 'error') !== false) {
                    $errorMsg .= ' Check: ' . substr($fileContent, 0, 500);
                }
            }
            
            // Add command output if available
            if (!empty($output)) {
                $errorMsg .= ' Output: ' . implode(' ', $output);
            }
            
            // Add mysqldump path info
            $errorMsg .= ' Using: ' . $mysqldumpPath;
            
            sendJson([
                'success' => false, 
                'message' => $errorMsg
            ], 500);
        }
    }
    
    // List Backups
    elseif ($action === 'list-backups') {
        $backups = [];
        $files = glob($backupDir . '/backup_*.{sql,sql.zip,sql.gz}', GLOB_BRACE);
        
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => round(filesize($file) / 1024 / 1024, 2) . ' MB',
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'path' => $file
            ];
        }
        
        usort($backups, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        
        sendJson([
            'success' => true,
            'backups' => $backups,
            'count' => count($backups)
        ]);
    }
    
    // Get Backup History
    elseif ($action === 'backup-history') {
        $stmt = $pdo->query("
            SELECT bh.*, a.Name AS AdminName 
            FROM BackupHistory bh
            LEFT JOIN Admin a ON bh.CreatedBy = a.AdminID
            ORDER BY bh.CreatedAt DESC
            LIMIT 50
        ");
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        sendJson([
            'success' => true,
            'history' => $history
        ]);
    }
    
    // Restore Backup
    elseif ($action === 'restore-backup') {
        $filename = $_POST['filename'] ?? '';
        
        if (empty($filename)) {
            // Handle file upload
            if (isset($_FILES['backupFile']) && $_FILES['backupFile']['error'] === UPLOAD_ERR_OK) {
                $uploadedFile = $_FILES['backupFile']['tmp_name'];
                $filename = basename($_FILES['backupFile']['name']);
                $filepath = $backupDir . '/' . $filename;
                
                if (!move_uploaded_file($uploadedFile, $filepath)) {
                    sendJson(['success' => false, 'message' => 'Failed to upload backup file']);
                }
            } else {
                sendJson(['success' => false, 'message' => 'No backup file specified']);
            }
        } else {
            $filepath = $backupDir . '/' . $filename;
        }
        
        if (!file_exists($filepath)) {
            sendJson(['success' => false, 'message' => 'Backup file not found']);
        }
        
        // Decompress if needed
        $sqlFile = $filepath;
        if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
            $sqlFile = str_replace('.gz', '', $filepath);
            $gz = gzopen($filepath, 'rb');
            $sql = gzread($gz, filesize($filepath) * 10);
            gzclose($gz);
            file_put_contents($sqlFile, $sql);
        } elseif (pathinfo($filepath, PATHINFO_EXTENSION) === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($filepath) === TRUE) {
                $zip->extractTo($backupDir);
                $sqlFile = $backupDir . '/' . str_replace('.zip', '', $filename);
                $zip->close();
            }
        }
        
        // Restore using mysql command - check multiple possible locations
        $possibleMysqlPaths = [
            'C:/xampp/mysql/bin/mysql.exe',
            'C:/xampp/mysql/bin/mysql',
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            'mysql'
        ];
        
        $mysqlPath = 'mysql';
        foreach ($possibleMysqlPaths as $path) {
            if (file_exists($path) || ($path === 'mysql')) {
                $mysqlPath = $path;
                break;
            }
        }
        
        // Build command
        $command = sprintf(
            '"%s" --host=%s --user=%s',
            $mysqlPath,
            escapeshellarg($dbHost),
            escapeshellarg($dbUser)
        );
        
        // Add password only if not empty
        if (!empty($dbPass)) {
            $command .= ' --password=' . escapeshellarg($dbPass);
        }
        
        $command .= ' ' . escapeshellarg($dbName) . ' < ' . escapeshellarg($sqlFile) . ' 2>&1';
        
        exec($command, $output, $returnCode);
        
        // Cleanup temporary files
        if ($sqlFile !== $filepath) {
            @unlink($sqlFile);
        }
        
        if ($returnCode === 0) {
            // Log restore
            $stmt = $pdo->prepare("INSERT INTO BackupHistory (FileName, BackupType, Description, CreatedBy, CreatedAt) VALUES (?, 'restore', 'Database restored from backup', ?, NOW())");
            $stmt->execute([$filename, $adminId]);
            
            sendJson([
                'success' => true,
                'message' => 'Database restored successfully from ' . $filename
            ]);
        } else {
            sendJson([
                'success' => false,
                'message' => 'Restore failed. Error: ' . implode("\n", $output)
            ], 500);
        }
    }
    
    // Delete Backup
    elseif ($action === 'delete-backup') {
        $filename = $_POST['filename'] ?? '';
        $filepath = $backupDir . '/' . $filename;
        
        if (file_exists($filepath)) {
            if (unlink($filepath)) {
                $stmt = $pdo->prepare("DELETE FROM BackupHistory WHERE FileName = ?");
                $stmt->execute([$filename]);
                
                sendJson(['success' => true, 'message' => 'Backup deleted successfully']);
            } else {
                sendJson(['success' => false, 'message' => 'Failed to delete backup file']);
            }
        } else {
            sendJson(['success' => false, 'message' => 'Backup file not found']);
        }
    }
    
    // Download Backup
    elseif ($action === 'download-backup') {
        $filename = $_GET['filename'] ?? '';
        $filepath = $backupDir . '/' . $filename;
        
        if (file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Content-Length: ' . filesize($filepath));
            header('Cache-Control: must-revalidate');
            readfile($filepath);
            exit;
        } else {
            sendJson(['success' => false, 'message' => 'Backup file not found'], 404);
        }
    }
    
    // Get Database Tables
    elseif ($action === 'get-tables') {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        sendJson([
            'success' => true,
            'tables' => $tables
        ]);
    }
    
    // Save Auto Backup Settings
    elseif ($action === 'save-auto-backup') {
        $enabled = $_POST['enabled'] ?? '0';
        $frequency = $_POST['frequency'] ?? 'daily';
        $time = $_POST['time'] ?? '02:00';
        $retention = $_POST['retention'] ?? 30;
        $email = $_POST['email'] ?? '';
        
        $settings = json_encode([
            'enabled' => $enabled,
            'frequency' => $frequency,
            'time' => $time,
            'retention' => $retention,
            'email' => $email
        ]);
        
        $stmt = $pdo->prepare("UPDATE Settings SET SettingValue = ? WHERE SettingKey = 'auto_backup_settings'");
        
        if ($stmt->execute([$settings])) {
            sendJson(['success' => true, 'message' => 'Auto backup settings saved']);
        } else {
            sendJson(['success' => false, 'message' => 'Failed to save settings']);
        }
    }
    
    // Get Auto Backup Settings
    elseif ($action === 'get-auto-backup') {
        $stmt = $pdo->prepare("SELECT SettingValue FROM Settings WHERE SettingKey = 'auto_backup_settings'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $settings = $result ? json_decode($result['SettingValue'], true) : [
            'enabled' => '0',
            'frequency' => 'daily',
            'time' => '02:00',
            'retention' => 30,
            'email' => ''
        ];
        
        sendJson([
            'success' => true,
            'settings' => $settings
        ]);
    }
    
    // Cleanup Old Backups
    elseif ($action === 'cleanup-backups') {
        $days = $_POST['days'] ?? 30;
        $cutoffDate = strtotime("-$days days");
        
        $files = glob($backupDir . '/backup_*.{sql,sql.zip,sql.gz}', GLOB_BRACE);
        $deleted = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffDate) {
                if (unlink($file)) {
                    $deleted++;
                    
                    $stmt = $pdo->prepare("DELETE FROM BackupHistory WHERE FileName = ?");
                    $stmt->execute([basename($file)]);
                }
            }
        }
        
        sendJson([
            'success' => true,
            'message' => "Deleted $deleted old backup(s)",
            'count' => $deleted
        ]);
    }
    
    else {
        sendJson(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    sendJson(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
}
?>
