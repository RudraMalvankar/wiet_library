<?php
/**
 * Database Migration Runner API
 * Executes the footfall enhancement migration
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db_connect.php';

function verifyFootfallColumns(PDO $pdo): array {
    $stmt = $pdo->query("DESCRIBE Footfall");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $requiredColumns = ['EntryTime', 'ExitTime', 'Purpose', 'Status', 'EntryMethod', 'WorkstationUsed'];
    $missing = array_values(array_diff($requiredColumns, $columns));

    return [$requiredColumns, $missing];
}

try {
    $migrationFile = __DIR__ . '/../database/migrations/006_enhance_footfall_tracking.sql';

    if (!file_exists($migrationFile)) {
        throw new Exception('Migration file not found.');
    }

    $sql = file_get_contents($migrationFile);
    if ($sql === false || trim($sql) === '') {
        throw new Exception('Migration file is empty.');
    }

    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        static function ($stmt) {
            return $stmt !== '' && !preg_match('/^\s*--/', $stmt);
        }
    );

    $results = [];

    foreach ($statements as $statement) {
        $stmt = $pdo->prepare($statement);
        $stmt->execute();

        if (preg_match('/ALTER TABLE/i', $statement)) {
            $results[] = 'Altered Footfall table structure';
        } elseif (preg_match('/UPDATE/i', $statement)) {
            $results[] = 'Updated ' . $stmt->rowCount() . ' existing records';
        } elseif (preg_match('/CREATE INDEX.*idx_entry_time/i', $statement)) {
            $results[] = 'Created index: idx_entry_time';
        } elseif (preg_match('/CREATE INDEX.*idx_status/i', $statement)) {
            $results[] = 'Created index: idx_status';
        } elseif (preg_match('/CREATE INDEX.*idx_entry_method/i', $statement)) {
            $results[] = 'Created index: idx_entry_method';
        } elseif (preg_match('/CREATE.*VIEW.*FootfallDailyStats/i', $statement)) {
            $results[] = 'Created view: FootfallDailyStats';
        } elseif (preg_match('/CREATE.*VIEW.*FootfallHourlyStats/i', $statement)) {
            $results[] = 'Created view: FootfallHourlyStats';
        } elseif (preg_match('/CREATE.*VIEW.*MemberFootfallSummary/i', $statement)) {
            $results[] = 'Created view: MemberFootfallSummary';
        }
    }

    [$requiredColumns, $missing] = verifyFootfallColumns($pdo);
    if (count($missing) > 0) {
        throw new Exception('Migration completed but required columns are missing: ' . implode(', ', $missing));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Migration 006 completed successfully.',
        'details' => $results,
        'columns_added' => $requiredColumns,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (PDOException $e) {
    $errorMessage = $e->getMessage();

    if (strpos($errorMessage, 'Duplicate column') !== false || strpos($errorMessage, 'already exists') !== false) {
        try {
            [$requiredColumns, $missing] = verifyFootfallColumns($pdo);

            if (count($missing) === 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Migration already applied. All required columns exist.',
                    'details' => [
                        'All required columns already exist',
                        'Database is ready for footfall system'
                    ],
                    'columns_added' => $requiredColumns,
                    'already_migrated' => true
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Partial migration detected',
                    'error' => 'Some required columns are still missing: ' . implode(', ', $missing),
                    'suggestion' => 'Please run the migration manually via phpMyAdmin.'
                ]);
            }
        } catch (Throwable $e2) {
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
            'code' => $e->getCode(),
            'suggestion' => 'Try running the migration manually via phpMyAdmin (Option 2).'
        ]);
    }
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed',
        'error' => $e->getMessage(),
        'suggestion' => 'Please run the migration manually via phpMyAdmin (Option 2 on the page).'
    ]);
}
