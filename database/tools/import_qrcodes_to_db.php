<?php
/**
 * import_qrcodes_to_db.php
 *
 * Usage (PowerShell):
 *   php import_qrcodes_to_db.php --host=127.0.0.1 --port=3306 --db=wiet_db --user=root --pass=secret --dir=..\..\storage\qrcodes
 *
 * The script will:
 *  - Ensure the Holding.QrCodeImg LONGBLOB column exists (ALTER TABLE if needed)
 *  - Scan the given directory for files named like qr_<accNo>.png (or similar)
 *  - Try to match each filename to a Holding.AccNo; if matched and QrCodeImg is empty, it will store the PNG bytes into the blob
 *  - Print a summary
 *
 * Notes:
 *  - Run this from the workspace folder: c:\xampp\htdocs\wiet_lib\database\tools
 *  - Requires PHP CLI with PDO MySQL enabled
 *  - ho[pe] we win :)
 */

$options = getopt('', ['host::','port::','db:','user:','pass::','dir::','dry::']);
$host = $options['host'] ?? '127.0.0.1';
$port = $options['port'] ?? '3306';
$db = $options['db'] ?? null;
$user = $options['user'] ?? 'root';
$pass = $options['pass'] ?? null;
$dir = $options['dir'] ?? __DIR__ . '/../../storage/qrcodes';
$dry = isset($options['dry']);

if (!$db) {
    echo "Error: --db is required\n";
    echo "Usage: php import_qrcodes_to_db.php --db=DB_NAME [--host=127.0.0.1] [--user=root] [--pass=PWD] [--dir=path] [--dry]\n";
    exit(2);
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (Exception $e) {
    echo "Failed to connect to DB: " . $e->getMessage() . "\n";
    exit(3);
}

function ensureColumn(PDO $pdo) {
    $sql = "SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Holding' AND COLUMN_NAME = 'QrCodeImg'";
    $c = $pdo->query($sql)->fetchColumn();
    if ($c > 0) return true;
    echo "QrCodeImg column not present. Attempting to add...\n";
    $pdo->exec("ALTER TABLE Holding ADD COLUMN QrCodeImg LONGBLOB NULL AFTER QRCode");
    echo "Added column QrCodeImg.\n";
    return true;
}

ensureColumn($pdo);

$dir = realpath($dir);
if (!$dir || !is_dir($dir)) {
    echo "QR directory not found: {$dir}\n";
    exit(4);
}

$files = glob($dir . DIRECTORY_SEPARATOR . '*');
$summary = ['scanned' => 0, 'matched' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0];

echo "Scanning directory: {$dir}\n";
foreach ($files as $file) {
    if (!is_file($file)) continue;
    $summary['scanned']++;
    $name = basename($file);
    // try to derive AccNo from filename: strip prefix qr_, qr-, etc. and extension
    $base = preg_replace('/^qr[_-]?/i', '', $name);
    $base = preg_replace('/\.(png|jpg|jpeg|gif)$/i', '', $base);
    // Try a few candidate transforms
    $candidates = [];
    $candidates[] = $base;
    $candidates[] = str_replace('_', '-', $base);
    $candidates[] = str_replace('-', '_', $base);
    $candidates[] = str_replace('_', '', $base);
    $candidates[] = str_replace('-', '', $base);

    $matched = false;
    foreach ($candidates as $cand) {
        if (!$cand) continue;
        // try to find an exact AccNo match
        $stmt = $pdo->prepare('SELECT AccNo, QrCodeImg IS NOT NULL AS hasblob FROM Holding WHERE AccNo = ? LIMIT 1');
        $stmt->execute([$cand]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $matched = $row['AccNo'];
            $hasBlob = (bool)$row['hasblob'];
            break;
        }
        // also try URL-decoded candidate
        $decoded = urldecode($cand);
        if ($decoded !== $cand) {
            $stmt->execute([$decoded]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) { $matched = $row['AccNo']; $hasBlob = (bool)$row['hasblob']; break; }
        }
    }

    if (!$matched) {
        echo "Not matched to any AccNo: {$name}\n";
        $summary['skipped']++;
        continue;
    }

    $summary['matched']++;
    if ($hasBlob) {
        echo "Already has blob: {$matched} (file: {$name}) - skipping\n";
        $summary['skipped']++;
        continue;
    }

    // read file contents
    $data = @file_get_contents($file);
    if ($data === false) {
        echo "Failed to read file: {$file}\n";
        $summary['errors']++;
        continue;
    }

    echo "Importing {$name} -> AccNo={$matched} (bytes=" . strlen($data) . ")\n";
    if ($dry) { echo "[dry-run] would update DB\n"; $summary['updated']++; continue; }

    try {
        $upd = $pdo->prepare('UPDATE Holding SET QrCodeImg = ? WHERE AccNo = ?');
        $upd->bindParam(1, $data, PDO::PARAM_LOB);
        $upd->bindParam(2, $matched, PDO::PARAM_STR);
        $upd->execute();
        $summary['updated']++;
    } catch (Exception $e) {
        echo "DB update failed for {$matched}: " . $e->getMessage() . "\n";
        $summary['errors']++;
    }
}

echo "\nSummary:\n";
echo " Scanned: {$summary['scanned']}\n";
echo " Matched: {$summary['matched']}\n";
echo " Updated: {$summary['updated']}\n";
echo " Skipped: {$summary['skipped']}\n";
echo " Errors: {$summary['errors']}\n";

exit(0);
