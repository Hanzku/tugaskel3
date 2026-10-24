<?php
declare(strict_types=1);
require __DIR__ . '/includes/guru.php';
require __DIR__ . '/includes/layout.php';
$q = substr(text_input($_GET, 'q'), 0, 400);
$columns = ['id_guru' => 'ID Guru', 'nama_guru' => 'Nama Lengkap', 'jenis_kelamin' => 'Jenis Kelamin', 'nama_pelajaran' => 'Pelajaran Utama', 'email' => 'Email', 'telepon' => 'Telepon'];
$sort = text_input($_GET, 'sort');
$sort = array_key_exists($sort, $columns) ? $sort : 'id_guru';
$direction = text_input($_GET, 'dir') === 'desc' ? 'desc' : 'asc';
$page = max(1, min(1000000, (int) text_input($_GET, 'page')));
$params = [];
$from = ' FROM guru g LEFT JOIN pelajaran p ON p.kd_pelajaran = g.kd_pelajaran';
if ($q !== '') {
    $from .= " WHERE g.id_guru LIKE ? ESCAPE '=' OR g.nama_guru LIKE ? ESCAPE '=' OR g.nip LIKE ? ESCAPE '=' OR p.nama_pelajaran LIKE ? ESCAPE '='";
    $pattern = '%' . strtr($q, ['=' => '==', '%' => '=%', '_' => '=_']) . '%';
    $params = array_fill(0, 4, $pattern);
}
$count = db()->prepare('SELECT COUNT(*)' . $from);
$count->execute($params);
$total = (int) $count->fetchColumn();
$pages = max(1, (int) ceil($total / 10));
$page = min($page, $pages);
$offset = ($page - 1) * 10;
// Identifiers come only from the fixed column map; pagination is calculated locally.
$order = ($sort === 'nama_pelajaran' ? 'p.' : 'g.') . $sort;
$query = db()->prepare('SELECT g.*, p.nama_pelajaran' . $from . " ORDER BY $order $direction, g.id_guru ASC LIMIT 10 OFFSET $offset");
$query->execute($params);
$rows = $query->fetchAll();
function guru_list_url(array $changes = []): string
{
    global $q, $sort, $direction, $page;
    return 'guru.php?' . http_build_query(array_merge(['q' => $q, 'sort' => $sort, 'dir' => $direction, 'page' => $page], $changes));
}
page_start('Data Guru', '', true);
?>
<div class="toolbar"><a class="button primary" href="form_guru.php"><span aria-hidden="true">+</span> Tambah Data</a><span class="muted">ID unik - Nama lengkap - Pelajaran utama</span></div>
<section class="panel" aria-label="Daftar guru">
  <form method="get" action="guru.php" class="filters">
    <div class="search-field"><label class="sr-only" for="q">Cari ID, nama, NIP atau pelajaran</label><input id="q" name="q" value="<?= e($q) ?>" maxlength="100" placeholder="Cari ID, nama, NIP atau pelajaran..."></div>
    <label class="sr-only" for="sort">Urutkan berdasarkan</label><select id="sort" name="sort"><?php foreach ($columns as $key => $label): ?><option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
    <label class="sr-only" for="dir">Arah urutan</label><select id="dir" name="dir"><option value="asc" <?= $direction === 'asc' ? 'selected' : '' ?>>Menaik</option><option value="desc" <?= $direction === 'desc' ? 'selected' : '' ?>>Menurun</option></select>
    <button class="button" type="submit">Terapkan</button><?php if ($q !== ''): ?><a class="text-link" href="guru.php">Reset</a><?php endif; ?>
  </form>
  <div class="table-scroll" tabindex="0" role="region" aria-label="Tabel guru; geser untuk melihat seluruh kolom">
    <table><thead><tr>
      <?php foreach ($columns as $key => $label): ?><th scope="col" <?= $sort === $key ? 'aria-sort="' . ($direction === 'asc' ? 'ascending' : 'descending') . '"' : '' ?>><a href="<?= e(guru_list_url(['sort' => $key, 'dir' => $sort === $key && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) ?>"><?= e($label) ?><?= $sort === $key ? ($direction === 'asc' ? ' &uarr;' : ' &darr;') : '' ?></a></th><?php endforeach; ?>
      <th scope="col">Operasi</th>
    </tr></thead><tbody>
      <?php foreach ($rows as $row): $code = rawurlencode($row['id_guru']); ?><tr>
        <?php foreach ($columns as $key => $label): ?><td><?= e(guru_value($row, $key)) ?></td><?php endforeach; ?>
        <td><div class="row-actions"><a href="detail_guru.php?kode=<?= e($code) ?>" aria-label="Lihat <?= e($row['id_guru']) ?>">Lihat</a><a href="form_guru.php?kode=<?= e($code) ?>" aria-label="Edit <?= e($row['id_guru']) ?>">Edit</a><a class="danger-link" href="hapus_guru.php?kode=<?= e($code) ?>" aria-label="Hapus <?= e($row['id_guru']) ?>">Hapus</a></div></td>
      </tr><?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="7"><div class="empty"><h2><?= $q !== '' ? 'Tidak ada hasil pencarian' : 'Belum ada data guru' ?></h2><p><?= $q !== '' ? 'Coba kata pencarian yang berbeda.' : 'Tambahkan guru pertama untuk memulai.' ?></p><a class="button primary" href="<?= $q !== '' ? 'guru.php' : 'form_guru.php' ?>"><?= $q !== '' ? 'Tampilkan Semua' : 'Tambah Data' ?></a></div></td></tr><?php endif; ?>
    </tbody></table>
  </div>
  <div class="table-footer"><span>Menampilkan <?= $total ? $offset + 1 : 0 ?>-<?= min($offset + 10, $total) ?> dari <?= $total ?> guru</span><nav class="pagination" aria-label="Halaman daftar guru">
    <?php if ($page > 1): ?><a class="button" aria-label="Halaman sebelumnya" href="<?= e(guru_list_url(['page' => $page - 1])) ?>">&larr;</a><?php endif; ?>
    <span>Halaman <?= $page ?> / <?= $pages ?></span>
    <?php if ($page < $pages): ?><a class="button" aria-label="Halaman berikutnya" href="<?= e(guru_list_url(['page' => $page + 1])) ?>">&rarr;</a><?php endif; ?>
  </nav></div>
</section>
<?php page_end(); ?>