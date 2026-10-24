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
page_start('Detail Guru', 'Detail', true);
?>
<section class="panel form-panel">
  <div class="panel-heading"><h2>Informasi Guru</h2><p>Data master guru yang tersimpan.</p></div>
  <dl class="detail-list">
    <?php foreach (guru_fields() as $key => [$label]): ?>
    <div><dt><?= e($label) ?></dt><dd><?= e(guru_value($data, $key)) ?></dd></div>
    <?php endforeach; ?>
  </dl>
  <div class="form-actions"><a class="button" href="guru.php">Kembali</a><a class="button primary" href="form_guru.php?kode=<?= e(rawurlencode($data['id_guru'])) ?>">Edit</a></div>
</section>
<?php page_end(); ?>