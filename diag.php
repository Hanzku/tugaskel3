<?php
declare(strict_types=1);
// Temporary safe diagnostics page - to be removed after verification.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

echo "=== SIA SMK Malaka — Diagnostik Koneksi Database ===\n\n";

echo "[1] PHP version: " . PHP_VERSION . "\n";
echo "    pdo_mysql loaded: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n";
echo "    openssl loaded:   " . (extension_loaded('openssl') ? 'YES' : 'NO') . "\n\n";

echo "[2] CA certificates:\n";
echo "    ca.pem exists:          " . (is_file(__DIR__ . '/config/ca.pem') ? 'YES' : 'NO') . "\n";
echo "    isrgrootx1.pem exists:  " . (is_file(__DIR__ . '/config/isrgrootx1.pem') ? 'YES' : 'NO') . "\n\n";

require_once __DIR__ . '/includes/app.php';

echo "[3] Testing db() function from includes/app.php:\n";
try {
    $pdo = db();
    echo "    CONNECTION: SUCCESS!\n\n";

    echo "[4] SELECT 1 test: ";
    $row = $pdo->query('SELECT 1 AS ok')->fetch();
    echo ($row && $row['ok'] == 1 ? 'OK' : 'FAIL') . "\n\n";

    echo "[5] Tables check:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "    Tables found: " . (count($tables) > 0 ? implode(', ', $tables) : 'NONE') . "\n";

    if (in_array('pelajaran', $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM pelajaran")->fetchColumn();
        echo "    Row count in 'pelajaran': $count\n";
    } else {
        echo "    WARNING: 'pelajaran' table NOT found!\n";
    }

    if (in_array('guru', $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM guru")->fetchColumn();
        echo "    Row count in 'guru': $count\n";
    } else {
        echo "    WARNING: 'guru' table NOT found!\n";
    }

} catch (Throwable $e) {
    echo "    CONNECTION: FAILED\n";
    $msg = $e->getMessage();
    // Redact any credential info if present
    foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $var) {
        $val = getenv($var);
        if ($val !== false && $val !== '') {
            $msg = str_replace($val, '[REDACTED]', $msg);
        }
    }
    echo "    Error: " . $msg . "\n";
}

echo "\n=== END ===\n";
