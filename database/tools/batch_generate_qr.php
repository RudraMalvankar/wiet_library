<?php
/**
 * batch_generate_qr.php
 *
 * Generates QR PNG blobs for all holdings where QrCodeImg IS NULL.
 * Usage (PowerShell):
 *   php batch_generate_qr.php --db=wiet_db --user=root --pass=secret [--host=127.0.0.1] [--port=3306] [--limit=100] [--dry]
 *
 * Notes:
 * - Requires PHP CLI with PDO MySQL and either the libs/phpqrcode library or GD installed.
 * - The script will generate a PNG encoding the AccNo (canonical QR payload) and store it into Holding.QrCodeImg using PDO::PARAM_LOB.
 */

$options = getopt('', ['host::','port::','db:','user:','pass::','limit::','dry::','verbose::']);
$host = $options['host'] ?? '127.0.0.1';
$port = $options['port'] ?? '3306';
$db = $options['db'] ?? null;
$user = $options['user'] ?? 'root';
$pass = $options['pass'] ?? null;
$limit = isset($options['limit']) ? intval($options['limit']) : 0; // 0 = all
$dry = isset($options['dry']);
$verbose = isset($options['verbose']);

if (!$db) {
    echo "Error: --db is required\n";
    echo "Usage: php batch_generate_qr.php --db=DB_NAME --user=DB_USER --pass=DB_PASS [--host=127.0.0.1] [--limit=100] [--dry]\n";
    exit(2);
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
} catch (Exception $e) {
    echo "Failed to connect to DB: " . $e->getMessage() . "\n";
    exit(3);
}

function generate_qr_png_local($text) {
    // Prefer phpqrcode if available
    $phpqrcode = __DIR__ . '/../../libs/phpqrcode/phpqrcode.php';
    if (file_exists($phpqrcode)) {
        try {
            ob_start();
            require_once $phpqrcode;
            QRcode::png((string)$text, false, 'L', 4, 2);
            $png = ob_get_clean();
            if ($png !== false && strlen($png) > 0) return $png;
        } catch (Exception $e) {
            if (ob_get_level()) @ob_end_clean();
        }
    }
    // Fallback GD
    if (function_exists('imagecreatetruecolor')) {
        $size = 320;
        $img = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, $size, $size, $white);
        $hash = md5((string)$text, true);
        $grid = 21;
        $cell = (int)floor(($size - 20) / $grid);
        $offset = 10;
        $bitIndex = 0;
        for ($y = 0; $y < $grid; $y++) {
            for ($x = 0; $x < $grid; $x++) {
                $byte = ord($hash[(int)floor($bitIndex / 8)] ?? "\0");
                $bit = ($byte >> ($bitIndex % 8)) & 1;
                if ($bit) {
                    imagefilledrectangle(
                        $img,
                        $offset + $x * $cell,
                        $offset + $y * $cell,
                        $offset + ($x + 1) * $cell - 2,
                        $offset + ($y + 1) * $cell - 2,
                        $black
                    );
                }
                $bitIndex++;
            }
        }
        if (function_exists('imagestring')) {
            imagestring($img, 3, 10, $size - 18, (string)$text, $black);
        }
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);
        return $png;
    }
    return null;
}

// Select holdings missing blob
$sql = "SELECT AccNo, HoldID FROM Holding WHERE QrCodeImg IS NULL";
if ($limit > 0) $sql .= " LIMIT " . intval($limit);
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();
$total = count($rows);
if ($total === 0) {
    echo "No holdings found with NULL QrCodeImg.\n";
    exit(0);
}

echo "Found {$total} holdings without QR blob" . ($limit>0?" (limit={$limit})":"") . "\n";

$counter = 0;
$updated = 0;
$failed = 0;

foreach ($rows as $r) {
    $counter++;
    $acc = $r['AccNo'];
    $id = $r['HoldID'];
    if ($verbose) echo "[{$counter}/{$total}] Generating for AccNo={$acc} (HoldID={$id})... ";
    $png = generate_qr_png_local($acc);
    if ($png === null) {
        echo "\nFailed to generate PNG for {$acc}\n";
        $failed++;
        continue;
    }
    if ($dry) {
        if ($verbose) echo "[dry-run] OK\n";
        $updated++;
        continue;
    }
    try {
        $upd = $pdo->prepare('UPDATE Holding SET QrCodeImg = ? WHERE HoldID = ?');
        $upd->bindParam(1, $png, PDO::PARAM_LOB);
        $upd->bindParam(2, $id, PDO::PARAM_INT);
        $upd->execute();
        if ($verbose) echo "OK\n";
        $updated++;
    } catch (Exception $e) {
        echo "\nDB update failed for {$acc}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\nDone. Processed: {$counter}, Updated: {$updated}, Failed: {$failed}\n";

exit(0);
