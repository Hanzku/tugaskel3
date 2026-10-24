<?php
declare(strict_types=1);
require __DIR__ . '/includes/guru.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Operasi ini hanya menerima POST.');
}
verify_token();
$action = text_input($_POST, 'action');
if (!in_array($action, ['add', 'edit', 'delete'], true)) {
    http_response_code(400);
    exit('Operasi tidak dikenal.');
}
$original = text_input($_POST, 'original_code');
if ($action !== 'add' && !find_guru($original)) {
    flash('error', 'Guru tidak ditemukan atau sudah dihapus.');
    go('guru.php');
}

if ($action === 'delete') {
    try {
        $query = db()->prepare('DELETE FROM guru WHERE id_guru = ?');
        $query->execute([$original]);
        flash($query->rowCount() ? 'success' : 'error', $query->rowCount() ? 'Guru berhasil dihapus.' : 'Guru sudah dihapus oleh permintaan lain.');
    } catch (PDOException $error) {
        error_log((string) $error);
        flash('error', (int) ($error->errorInfo[1] ?? 0) === 1451
            ? 'Guru tidak dapat dihapus karena masih digunakan oleh data lain.'
            : 'Guru gagal dihapus. Silakan coba lagi atau periksa log server.');
    }
    go('guru.php');
}

[$data, $errors] = validate_guru($_POST);
if ($action === 'edit' && $data['id_guru'] !== $original) {
    $errors['id_guru'] = 'Kode adalah identitas tetap. Gunakan kode asli saat mengedit.';
    $data['id_guru'] = $original;
}
if (!$errors) {
    if ($data['kd_pelajaran'] !== '' && !find_pelajaran($data['kd_pelajaran'])) {
        $errors['kd_pelajaran'] = 'Pelajaran tidak ditemukan. Pilih pelajaran yang tersedia.';
    }
}
if (!$errors) {
    try {
        $fields = array_keys(guru_fields());
        $values = array_map(static fn(string $key): ?string => $data[$key] === '' ? null : $data[$key], $fields);
        if ($action === 'add') {
            $query = db()->prepare('INSERT INTO guru (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')');
            $query->execute($values);
        } else {
            // Lock the row so a concurrent deletion cannot produce a false success message.
            db()->beginTransaction();
            $lock = db()->prepare('SELECT id_guru FROM guru WHERE id_guru = ? FOR UPDATE');
            $lock->execute([$original]);
            if (!$lock->fetch()) {
                db()->rollBack();
                flash('error', 'Guru sudah dihapus oleh permintaan lain.');
                go('guru.php');
            }
            array_shift($fields);
            array_shift($values);
            $values[] = $original;
            $query = db()->prepare('UPDATE guru SET ' . implode(', ', array_map(static fn(string $key): string => "$key = ?", $fields)) . ' WHERE id_guru = ?');
            $query->execute($values);
            db()->commit();
        }
        unset($_SESSION['form_guru']);
        flash('success', $action === 'add' ? 'Guru berhasil ditambahkan.' : 'Perubahan guru berhasil disimpan.');
        go('detail_guru.php?kode=' . rawurlencode($data['id_guru']));
    } catch (PDOException $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        error_log((string) $error);
        if ((int) ($error->errorInfo[1] ?? 0) === 1062) {
            $errors['id_guru'] = 'ID Guru, NIP, atau NUPTK sudah digunakan. Periksa identitas yang diisi.';
        } elseif ((int) ($error->errorInfo[1] ?? 0) === 1452) {
            $errors['kd_pelajaran'] = 'Pelajaran tidak tersedia lagi. Pilih kembali.';
        } else {
            $errors['nama_guru'] = 'Data gagal disimpan. Coba lagi atau periksa log server.';
        }
    }
}
$_SESSION['form_guru'] = ['code' => $action === 'edit' ? $original : '', 'data' => $data, 'errors' => $errors];
go('form_guru.php' . ($action === 'edit' ? '?kode=' . rawurlencode($original) : ''));