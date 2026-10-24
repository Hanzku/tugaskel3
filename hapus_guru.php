<?php
declare(strict_types=1);
require __DIR__ . '/includes/guru.php';
require __DIR__ . '/includes/layout.php';

$data = find_guru(text_input($_GET, 'kode'));
if (!$data) {
    http_response_code(404);
    page_start('Guru tidak ditemukan', '', true);
    echo '<section class="panel empty"><p>Data tidak tersedia atau sudah dihapus.</p><a class="button" href="guru.php">Kembali ke Daftar</a></section>';
    page_end();
    exit;
}
page_start('Hapus Guru', 'Konfirmasi Hapus', true);
?>
<section class="panel form-panel">
  <div class="panel-heading"><h2>Konfirmasi Penghapusan</h2><p>Tindakan ini tidak dapat dibatalkan.</p></div>
  <div class="confirm-body"><p>Hapus guru <strong><?= e($data['id_guru']) ?></strong> — <?= e($data['nama_guru']) ?>?</p><p class="muted">Guru yang masih dirujuk oleh relasi database tidak dapat dihapus.</p></div>
  <form method="post" action="proses_guru.php">
    <input type="hidden" name="csrf" value="<?= e(token()) ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="original_code" value="<?= e($data['id_guru']) ?>">
    <div class="form-actions"><button class="button danger" type="submit">Ya, Hapus Data</button><a class="button" href="guru.php">Batal</a></div>
  </form>
</section>
<?php page_end(); ?>