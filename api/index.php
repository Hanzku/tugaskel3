<?php
declare(strict_types=1);

$routes = [
    'guru.php' => 'guru.php',
    'form_guru.php' => 'form_guru.php',
    'detail_guru.php' => 'detail_guru.php',
    'hapus_guru.php' => 'hapus_guru.php',
    'proses_guru.php' => 'proses_guru.php',
    'index.php' => 'index.php',
    'pelajaran.php' => 'pelajaran.php',
    'form_pelajaran.php' => 'form_pelajaran.php',
    'detail_pelajaran.php' => 'detail_pelajaran.php',
    'hapus_pelajaran.php' => 'hapus_pelajaran.php',
    'proses_pelajaran.php' => 'proses_pelajaran.php',
];
$script = $_GET['script'] ?? 'index.php';

if (!is_string($script) || !isset($routes[$script])) {
    http_response_code(404);
    exit('Halaman tidak ditemukan.');
}

require __DIR__ . '/../' . $routes[$script];
