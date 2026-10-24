<?php
declare(strict_types=1);
require __DIR__ . '/includes/guru.php';
require __DIR__ . '/includes/layout.php';
$code = text_input($_GET, 'kode');
$editing = $code !== '';
$data = $editing ? find_guru($code) : array_fill_keys(array_keys(guru_fields()), '');
if (!$data) {
    http_response_code(404);
    page_start('Guru tidak ditemukan', '', true);
    echo '<section class="panel empty"><p>Data tidak tersedia atau sudah dihapus.</p><a class="button" href="guru.php">Kembali ke Daftar</a></section>';
    page_end();
    exit;
}
$errors = [];
$previous = $_SESSION['form_guru'] ?? null;
unset($_SESSION['form_guru']);
if ($previous && $previous['code'] === $code) {
    $data = $previous['data'];
    $errors = $previous['errors'];
}
$subjects = db()->query('SELECT kd_pelajaran, nama_pelajaran FROM pelajaran ORDER BY nama_pelajaran, kd_pelajaran')->fetchAll();
page_start($editing ? 'Edit Guru' : 'Tambah Guru', $editing ? 'Edit Data' : 'Tambah Data', true);
?>
<section class="panel form-panel">
  <div class="panel-heading"><h2>Informasi Guru</h2><p>ID Guru dan nama lengkap wajib diisi. Kolom lainnya opsional. Mata pelajaran merupakan pelajaran utama, bukan jadwal mengajar.</p></div>
  <?php if ($errors): ?><div class="form-summary alert error" role="alert">Data belum disimpan. Periksa kolom yang ditandai.</div><?php endif; ?>
  <form method="post" action="proses_guru.php">
    <input type="hidden" name="csrf" value="<?= e(token()) ?>">
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <?php if ($editing): ?><input type="hidden" name="original_code" value="<?= e($code) ?>"><?php endif; ?>
    <?php foreach (guru_fields() as $key => [$label, $limit]): ?>
    <div class="form-row">
      <label for="<?= e($key) ?>"><?= e($label) ?><?= in_array($key, ['id_guru', 'nama_guru'], true) ? ' *' : '' ?></label>
      <div class="field">
      <?php if (in_array($key, ['jenis_kelamin', 'kd_pelajaran'], true)): ?>
        <select id="<?= e($key) ?>" name="<?= e($key) ?>" aria-describedby="<?= e($key) ?>-help" <?= isset($errors[$key]) ? 'aria-invalid="true"' : '' ?>>
          <option value="">Belum diisi</option>
          <?php $options = $key === 'jenis_kelamin' ? ['L' => 'Laki-laki', 'P' => 'Perempuan'] : array_column($subjects, 'nama_pelajaran', 'kd_pelajaran'); ?>
          <?php foreach ($options as $value => $name): ?><option value="<?= e($value) ?>" <?= (string) ($data[$key] ?? '') === (string) $value ? 'selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?>
        </select>
      <?php else: ?>
        <input id="<?= e($key) ?>" name="<?= e($key) ?>" type="<?= $key === 'email' ? 'email' : ($key === 'telepon' ? 'tel' : 'text') ?>" value="<?= e($data[$key] ?? '') ?>" maxlength="<?= $limit ?>" <?= in_array($key, ['id_guru', 'nama_guru'], true) ? 'required' : '' ?> <?= $key === 'id_guru' && $editing ? 'readonly' : '' ?> aria-describedby="<?= e($key) ?>-help" <?= isset($errors[$key]) ? 'aria-invalid="true"' : '' ?>>
      <?php endif; ?>
        <div id="<?= e($key) ?>-help" class="help <?= isset($errors[$key]) ? 'field-error' : '' ?>"><?= e($errors[$key] ?? match ($key) { 'id_guru' => 'Identitas tetap, maksimal 20 huruf, angka, tanda hubung atau garis bawah. Disimpan dalam huruf kapital.', 'nip' => 'Opsional, 18 angka.', 'nuptk' => 'Opsional, 16 angka.', 'jenis_kelamin' => 'Pilih jika tersedia.', 'kd_pelajaran' => 'Pilih dari Data Pelajaran jika sudah ditetapkan.', default => "Maksimal $limit karakter." }) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
    <div class="form-actions"><button type="submit" class="button primary">Simpan Data</button><a class="button" href="<?= $editing ? 'detail_guru.php?kode=' . e(rawurlencode($code)) : 'guru.php' ?>">Batal</a></div>
  </form>
</section>
<?php page_end(); ?>