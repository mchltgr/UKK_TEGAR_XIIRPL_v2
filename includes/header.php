<?php
$u = $_SESSION['user'];
$menu = [
  'admin' => ['dashboard.php'=>'Dashboard','user.php'=>'User','kategori.php'=>'Kategori','alat.php'=>'Alat',
              'peminjaman.php'=>'Peminjaman','pengembalian.php'=>'Pengembalian','log.php'=>'Log Aktivitas'],
  'petugas' => ['dashboard.php'=>'Dashboard','persetujuan.php'=>'Persetujuan','pemantauan.php'=>'Pemantauan Pengembalian','laporan.php'=>'Laporan'],
  'peminjam' => ['dashboard.php'=>'Daftar Alat','pinjaman.php'=>'Pinjaman Saya'],
];
$cur = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'Peminjaman Alat') ?> - Peminjaman Alat</title>
<link rel="stylesheet" href="<?= BASE ?>assets/style.css">
</head>
<body>
<aside class="side no-print">
  <div class="brand">Peminjaman Alat</div>
  <nav>
    <?php foreach ($menu[$u['role']] as $file => $label): ?>
      <a href="<?= BASE . $u['role'] . '/' . $file ?>" class="<?= $cur === $file ? 'on' : '' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="me">
    <strong><?= e($u['nama']) ?></strong>
    <span class="muted"><?= e(ucfirst($u['role'])) ?></span>
    <a href="<?= BASE ?>logout.php" class="btn ghost small">Keluar</a>
  </div>
</aside>
<main class="main">
  <h1><?= e($judul ?? '') ?></h1>
  <?= flash() ?>
