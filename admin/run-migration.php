<?php
/**
 * Database Migration Runner API
 * Executes the footfall enhancement migration
 */

header('Content-Type: application/json');

require_once '../includes/db_connect.php';

try {
    $migrationFile = '../database/migrations/006_enhance_footfall_tracking.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: {$migrationFile}");
    }
    
    // Read migration SQL
    $sql = file_get_contents($migrationFile);
    
    if (empty($sql)) {
        throw new Exception("Migration file is empty");
    }
    
    // Split into individual statements (simple split by semicolon)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    $results = [];
    $conn->beginTransaction();
    
    try {
        foreach ($statements as $statement) {
            if (empty(trim($statement))) continue;
            
            $stmt = $conn->prepare($statement);
            $stmt->execute();
            
            // Track what was executed
            if (preg_match('/ALTER TABLE/i', $statement)) {
                $results[] = "✓ Altered Footfall table structure";
            } elseif (preg_match('/UPDATE/i', $statement)) {
                $affected = $stmt->rowCount();
                $results[] = "✓ Updated {$affected} existing records";
            } elseif (preg_match('/CREATE INDEX.*idx_entry_time/i', $statement)) {
                $results[] = "✓ Created index: idx_entry_time";
            } elseif (preg_match('/CREATE INDEX.*idx_status/i', $statement)) {
                $results[] = "✓ Created index: idx_status";
            } elseif (preg_match('/CREATE INDEX.*idx_entry_method/i', $statement)) {
                $results[] = "✓ Created index: idx_entry_method";
            } elseif (preg_match('/CREATE.*VIEW.*FootfallDailyStats/i', $statement)) {
                $results[] = "✓ Created view: FootfallDailyStats";
            } elseif (preg_match('/CREATE.*VIEW.*FootfallHourlyStats/i', $statement)) {
                $results[] = "✓ Created view: FootfallHourlyStats";
            } elseif (preg_match('/CREATE.*VIEW.*MemberFootfallSummary/i', $statement)) {
                $results[] = "✓ Created view: MemberFootfallSummary";
            }
        }
        
        $conn->commit();
        
        // Verify columns were added
        $stmt = $conn->query("DESCRIBE Footfall");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        $requiredColumns = ['EntryTime', 'ExitTime', 'Purpose', 'Status', 'EntryMethod', 'WorkstationUsed'];
        $missing = array_diff($requiredColumns, $columns);
        
        if (count($missing) > 0) {
            throw new Exception("Migration completed but columns missing: " . implode(', ', $missing));
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Migration 006 completed successfully! All columns, indexes, and views have been created.',
            'details' => $results,
            'columns_added' => $requiredColumns,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    // Check if error is because objects already exist
    $errorCode = $e->getCode();
    $errorMessage = $e->getMessage();
    
    if (strpos($errorMessage, 'Duplicate column') !== false || 
        strpos($errorMessage, 'already exists') !== false) {
        
        // Columns already exist, verify them
        try {
            $stmt = $conn->query("DESCRIBE Footfall");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            
            $requiredColumns = ['EntryTime', 'ExitTime', 'Purpose', 'Status', 'EntryMethod', 'WorkstationUsed'];
            $missing = array_diff($requiredColumns, $columns);
            
            if (count($missing) === 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Migration already applied! All required columns exist.',
                    'details' => [
                        '✓ All 6 columns already exist',
                        '✓ Database is ready for footfall system'
                    ],
                    'already_migrated' => true
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Partial migration detected',
                    'error' => 'Some columns exist but others are missing: ' . implode(', ', $missing),
                    'suggestion' => 'Please run the migration manually via phpMyAdmin'
                ]);
            }
        } catch (Exception $e2) {
            echo json_encode([
                'success' => false,
                'message' => 'Could not verify migration status',
                'error' => $e2->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database error during migration',
            'error' => $errorMessage,
            'code' => $errorCode,
            'suggestion' => 'Try running the migration manually via phpMyAdmin (Option 2)'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed',
        'error' => $e->getMessage(),
        'suggestion' => 'Please run the migration manually via phpMyAdmin (Option 2 on the page)'
    ]);
}
