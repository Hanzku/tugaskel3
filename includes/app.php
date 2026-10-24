<?php
declare(strict_types=1);

ini_set('display_errors', '0');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'use_strict_mode' => true,
]);

require_once __DIR__ . '/validation.php';

set_exception_handler(static function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>Gangguan aplikasi</title><body><h1>Aplikasi belum dapat digunakan</h1><p>Periksa konfigurasi dan koneksi database. Detail kesalahan dicatat di log server.</p><a href="pelajaran.php">Coba lagi</a></body></html>';
});

function db(): PDO
{
    static $connection = null;
    if ($connection === null) {
        $path = __DIR__ . '/../config/database.php';
        $config = is_file($path) ? require $path : [];
        if (!is_array($config)) {
            throw new RuntimeException('Konfigurasi database tidak valid.');
        }

        foreach (['host' => 'DB_HOST', 'port' => 'DB_PORT', 'name' => 'DB_NAME', 'user' => 'DB_USER', 'password' => 'DB_PASSWORD'] as $key => $environmentVariable) {
            $value = getenv($environmentVariable);
            if ($value !== false && $value !== '') {
                $config[$key] = $value;
            }
        }
        $config['port'] ??= '3306';
        foreach (['host', 'name', 'user'] as $key) {
            if (!isset($config[$key]) || $config[$key] === '') {
                throw new RuntimeException('Konfigurasi database belum lengkap. Atur DB_HOST, DB_NAME, DB_USER, dan DB_PASSWORD.');
            }
        }
        if (!array_key_exists('password', $config)) {
            throw new RuntimeException('Konfigurasi database belum lengkap. Atur DB_HOST, DB_NAME, DB_USER, dan DB_PASSWORD.');
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        if (getenv('DB_SSL') === '1') {
            $caPath = getenv('DB_SSL_CA');
            if (!$caPath || !is_file($caPath)) {
                $caPath = is_file(__DIR__ . '/../config/ca.pem')
                    ? __DIR__ . '/../config/ca.pem'
                    : __DIR__ . '/../config/isrgrootx1.pem';
            }
            if (!is_file($caPath)) {
                throw new RuntimeException('Sertifikat CA untuk koneksi database TLS tidak tersedia.');
            }
            if (class_exists('Pdo\Mysql', false) && defined('Pdo\Mysql::ATTR_SSL_CA')) {
                $options[Pdo\Mysql::ATTR_SSL_CA] = $caPath;
                $options[Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = true;
            } else {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
            }
        }

        $connection = new PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['name']),
            $config['user'],
            $config['password'],
            $options
        );
    }
    return $connection;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function token(): string
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_token(): void
{
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals(token(), $_POST['csrf'])) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }
}

function go(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function find_pelajaran(string $code): array|false
{
    $query = db()->prepare('SELECT kd_pelajaran, nama_pelajaran, bobot FROM pelajaran WHERE kd_pelajaran = ?');
    $query->execute([$code]);
    return $query->fetch();
}

function bobot_label(string $value): string
{
    return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . '%';
}