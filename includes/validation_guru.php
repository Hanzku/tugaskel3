<?php
declare(strict_types=1);
require_once __DIR__ . '/validation.php';

function guru_fields(): array
{
    return ['id_guru' => ['ID Guru', 20], 'nama_guru' => ['Nama Lengkap', 100],
        'jenis_kelamin' => ['Jenis Kelamin', 1], 'kd_pelajaran' => ['Mata Pelajaran Utama', 20],
        'nip' => ['NIP', 18], 'nuptk' => ['NUPTK', 16], 'email' => ['Email', 254],
        'telepon' => ['Nomor Telepon', 30], 'alamat' => ['Alamat', 500],
        'jabatan' => ['Jabatan / Status Kepegawaian', 100], 'kelas' => ['Kelas yang Diajar', 100],
        'keterangan' => ['Keterangan', 1000]];
}

function validate_guru(array $input): array
{
    $data = $errors = [];
    foreach (guru_fields() as $key => [$label, $limit]) {
        $value = text_input($input, $key);
        $data[$key] = $value;
        $length = preg_match_all('/./us', $value);
        if ((isset($input[$key]) && !is_string($input[$key])) || $length === false || $length > $limit || preg_match('/[\x00-\x1F\x7F]/', is_string($input[$key] ?? null) ? $input[$key] : '')) {
            $errors[$key] = "$label maksimal $limit karakter, tanpa karakter kontrol.";
        }
    }
    $data['id_guru'] = strtoupper($data['id_guru']);
    if (!preg_match('/\A[A-Z0-9_-]{1,20}\z/', $data['id_guru'])) {
        $errors['id_guru'] = 'ID wajib diisi, maksimal 20 huruf, angka, tanda hubung atau garis bawah.';
    }
    if (preg_match('/\A\s*\z/u', $data['nama_guru'])) {
        $errors['nama_guru'] = 'Nama lengkap wajib diisi.';
    }
    if (!in_array($data['jenis_kelamin'], ['', 'L', 'P'], true)) {
        $errors['jenis_kelamin'] = 'Pilih jenis kelamin yang tersedia.';
    }
    if ($data['kd_pelajaran'] !== '' && !preg_match('/\A[A-Za-z0-9_-]{1,20}\z/', $data['kd_pelajaran'])) {
        $errors['kd_pelajaran'] = 'Kode pelajaran tidak valid.';
    }
    foreach (['nip' => 18, 'nuptk' => 16] as $key => $length) {
        if ($data[$key] !== '' && !preg_match('/\A[0-9]{' . $length . '}\z/', $data[$key])) {
            $errors[$key] = strtoupper($key) . " harus terdiri dari $length angka atau dikosongkan.";
        }
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }
    if ($data['telepon'] !== '' && (!preg_match('/\A\+?[0-9 ()-]+\z/', $data['telepon']) || strlen(preg_replace('/\D/', '', $data['telepon'])) < 7 || strlen(preg_replace('/\D/', '', $data['telepon'])) > 15)) {
        $errors['telepon'] = 'Nomor telepon harus memiliki 7-15 angka; awalan +, spasi, kurung dan tanda hubung diperbolehkan.';
    }
    return [$data, $errors];
}