<?php
declare(strict_types=1);
// Diagnostic halaman sementara — HAPUS setelah debugging selesai.
// Tidak menampilkan kredensial, password, atau connection string apapun.
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');

echo "=== SIA SMK Malaka — Diagnostik Koneksi Database ===\n\n";

// 1. PHP version & extensions
echo "[1] PHP version: " . PHP_VERSION . "\n";
echo "    pdo_mysql loaded: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n";
echo "    openssl loaded:   " . (extension_loaded('openssl') ? 'YES' : 'NO') . "\n\n";

// 2. Config file
$configPath = __DIR__ . '/config/database.php';
echo "[2] config/database.php exists: " . (is_file($configPath) ? 'YES' : 'NO') . "\n\n";

// 3. Environment variables (existence only, NOT values)
$vars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_SSL', 'DB_SSL_CA'];
echo "[3] Environment variables via getenv():\n";
foreach ($vars as $var) {
    $val = getenv($var);
    echo "    $var: " . ($val !== false && $val !== '' ? 'SET (length=' . strlen($val) . ')' : 'NOT SET') . "\n";
}
echo "\n";

echo "[4] Environment variables via \$_ENV:\n";
foreach ($vars as $var) {
    echo "    $var: " . (isset($_ENV[$var]) && $_ENV[$var] !== '' ? 'SET (length=' . strlen($_ENV[$var]) . ')' : 'NOT SET') . "\n";
}
echo "\n";

echo "[5] Environment variables via \$_SERVER:\n";
foreach ($vars as $var) {
    echo "    $var: " . (isset($_SERVER[$var]) && $_SERVER[$var] !== '' ? 'SET (length=' . strlen((string)$_SERVER[$var]) . ')' : 'NOT SET') . "\n";
}
echo "\n";

// 4. CA certificate
$caPath = __DIR__ . '/config/isrgrootx1.pem';
echo "[6] CA certificate (isrgrootx1.pem) exists: " . (is_file($caPath) ? 'YES' : 'NO') . "\n";
$aivCaPath = __DIR__ . '/config/ca.pem';
echo "    CA certificate (ca.pem) exists: " . (is_file($aivCaPath) ? 'YES' : 'NO') . "\n\n";

// 5. Try database connection
echo "[7] Database connection test:\n";

// Read config like app.php does
$config = is_file($configPath) ? require $configPath : [];
if (!is_array($config)) $config = [];

foreach (['host' => 'DB_HOST', 'port' => 'DB_PORT', 'name' => 'DB_NAME', 'user' => 'DB_USER', 'password' => 'DB_PASSWORD'] as $key => $envVar) {
    $value = getenv($envVar);
    if ($value !== false && $value !== '') {
        $config[$key] = $value;
    }
}
$config['port'] ??= '3306';

// Check completeness
$missing = [];
foreach (['host', 'name', 'user'] as $k) {
    if (!isset($config[$k]) || $config[$k] === '') $missing[] = $k;
}
if (!array_key_exists('password', $config)) $missing[] = 'password';

if ($missing) {
    echo "    FAIL: Missing config keys: " . implode(', ', $missing) . "\n";
    echo "    This means environment variables are not being read.\n";
    echo "\n=== END ===\n";
    exit;
}

echo "    Config keys present: host, port, name, user, password — OK\n";
echo "    Host length: " . strlen($config['host']) . " chars\n";
echo "    DB name length: " . strlen($config['name']) . " chars\n";

// Try connection
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$sslEnabled = getenv('DB_SSL') === '1';
echo "    DB_SSL=1: " . ($sslEnabled ? 'YES' : 'NO') . "\n";

if ($sslEnabled) {
    $actualCaPath = getenv('DB_SSL_CA') ?: __DIR__ . '/config/isrgrootx1.pem';
    echo "    SSL CA path: " . basename($actualCaPath) . "\n";
    echo "    SSL CA file exists: " . (is_file($actualCaPath) ? 'YES' : 'NO') . "\n";
    if (is_file($actualCaPath)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $actualCaPath;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }
}

try {
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['name']);
    $pdo = new PDO($dsn, $config['user'], $config['password'], $options);
    echo "    CONNECTION: SUCCESS\n\n";

    // Test query
    echo "[8] SELECT 1 test: ";
    $r = $pdo->query('SELECT 1 AS ok')->fetch();
    echo ($r && $r['ok'] == 1 ? 'OK' : 'FAIL') . "\n";

    // Check tables
    echo "[9] Tables check:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "    Tables found: " . (count($tables) > 0 ? implode(', ', $tables) : 'NONE') . "\n";

    if (in_array('pelajaran', $tables)) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM pelajaran")->fetchColumn();
        echo "    pelajaran rows: $cnt\n";
    }
    if (in_array('guru', $tables)) {
        $cnt = $pdo->query("SELECT COUNT(*) FROM guru")->fetchColumn();
        echo "    guru rows: $cnt\n";
    }

} catch (PDOException $e) {
    echo "    CONNECTION: FAILED\n";
    // Show error category without leaking credentials
    $msg = $e->getMessage();
    // Redact any host/user/password that might appear in error
    foreach (['host', 'user', 'password', 'name'] as $k) {
        if (isset($config[$k]) && $config[$k] !== '') {
            $msg = str_replace($config[$k], '[REDACTED]', $msg);
        }
    }
    echo "    Error code: " . $e->getCode() . "\n";
    echo "    Error message (redacted): " . $msg . "\n";
}

echo "\n=== END ===\n";
