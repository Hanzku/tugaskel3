<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

echo "=== SIA SMK Malaka — Diagnostik Database & Tables ===\n\n";

require_once __DIR__ . '/includes/app.php';

try {
    $pdo = db();
    echo "CONNECTION: SUCCESS!\n\n";

    $currDb = $pdo->query('SELECT DATABASE()')->fetchColumn();
    echo "Current connected database: " . $currDb . "\n\n";

    echo "Available databases:\n";
    $dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($dbs as $dbName) {
        echo "  - " . $dbName . "\n";
    }
    echo "\n";

    echo "Tables in current database ($currDb):\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "  " . (count($tables) > 0 ? implode(', ', $tables) : '(none)') . "\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== END ===\n";
