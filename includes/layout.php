<?php
declare(strict_types=1);

function page_start(string $title, string $crumb = '', bool $guru = false): void
{
    $notice = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> · SIA SMK Malaka</title>
  <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <a class="brand" href="pelajaran.php">
      <img src="assets/img/favicon.svg" alt="Logo SMK Malaka" class="brand-logo" width="40" height="40">
      <span class="brand-text">
        <strong>SIA</strong>
        <span>SMK MALAKA</span>
      </span>
    </a>
    <div class="nav-label">DATA MASTER</div>
    <nav aria-label="Navigasi utama"><a class="nav-link<?= !$guru ? ' active' : '' ?>" <?= !$guru ? 'aria-current="page"' : '' ?> href="pelajaran.php">Pelajaran</a><a class="nav-link<?= $guru ? ' active' : '' ?>" <?= $guru ? 'aria-current="page"' : '' ?> href="guru.php">Data Guru</a></nav>
    <div class="sidebar-note">Sistem Informasi Akademik<br>Modul Data <?= $guru ? 'Guru' : 'Pelajaran' ?></div>
  </aside>
  <main class="content">
    <div class="breadcrumb"><a href="pelajaran.php">Data Master</a> / <a href="<?= $guru ? 'guru.php' : 'pelajaran.php' ?>"><?= $guru ? 'Data Guru' : 'Pelajaran' ?></a><?= $crumb !== '' ? ' / ' . e($crumb) : '' ?></div>
    <?php if ($notice): ?><div class="alert <?= $notice['type'] === 'success' ? 'success' : 'error' ?>" role="status"><?= e($notice['message']) ?></div><?php endif; ?>
    <header class="page-header">
      <div class="page-header-title">
        <h1><?= e($title) ?></h1>
        <p>Kelola data <?= $guru ? 'guru' : 'mata pelajaran' ?> SMK Malaka.</p>
      </div>
      <div class="page-header-badge">
        <span class="status-pill"><span class="pulse-dot"></span> Sistem Aktif</span>
      </div>
    </header>
<?php
}

function page_end(): void
{
    echo '</main></div><script src="assets/js/app.js" defer></script></body></html>';
}