<?php
declare(strict_types=1);
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/validation_guru.php';

function find_guru(string $code): array|false
{
    $query = db()->prepare('SELECT g.*, p.nama_pelajaran FROM guru g LEFT JOIN pelajaran p ON p.kd_pelajaran = g.kd_pelajaran WHERE g.id_guru = ?');
    $query->execute([$code]);
    return $query->fetch();
}

function guru_value(array $data, string $key): string
{
    if ($key === 'kd_pelajaran') {
        return $data['nama_pelajaran'] ?? '-';
    }
    if ($key === 'jenis_kelamin') {
        return ['L' => 'Laki-laki', 'P' => 'Perempuan'][$data[$key] ?? ''] ?? '-';
    }
    return ($data[$key] ?? '') !== '' && $data[$key] !== null ? (string) $data[$key] : '-';
}