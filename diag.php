<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

echo "=== SIA SMK Malaka — Tables in sia_malaka_guru_test ===\n\n";

require_once __DIR__ . '/includes/app.php';

try {
    $pdo = db();
    echo "Tables in sia_malaka_guru_test:\n";
    $tables = $pdo->query("SHOW TABLES FROM `sia_malaka_guru_test`")->fetchAll(PDO::FETCH_COLUMN);
    echo "  " . (count($tables) > 0 ? implode(', ', $tables) : '(none)') . "\n";

    if (in_array('pelajaran', $tables)) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `sia_malaka_guru_test`.`pelajaran`")->fetchColumn();
        echo "  pelajaran row count: $cnt\n";
    }
    if (in_array('guru', $tables)) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM `sia_malaka_guru_test`.`guru`")->fetchColumn();
        echo "  guru row count: $cnt\n";
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== END ===\n";
