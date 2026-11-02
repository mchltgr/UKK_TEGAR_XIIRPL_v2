<?php
require '../config.php'; wajib_login('admin'); $judul = 'Dashboard Admin';
$hariIni = date('Y-m-d');
require '../includes/header.php';
?>
<div class="stats">
  <div class="stat"><b><?= hitung("SELECT COUNT(*) FROM users") ?></b>User</div>
  <div class="stat"><b><?= hitung("SELECT COUNT(*) FROM alat") ?></b>Jenis alat</div>
  <div class="stat"><b><?= hitung("SELECT COUNT(*) FROM kategori") ?></b>Kategori</div>
  <div class="stat warn"><b><?= hitung("SELECT COUNT(*) FROM peminjaman WHERE status='menunggu'") ?></b>Menunggu persetujuan</div>
  <div class="stat"><b><?= hitung("SELECT COUNT(*) FROM peminjaman WHERE status='disetujui'") ?></b>Sedang dipinjam</div>
  <div class="stat bad"><b><?= hitung("SELECT COUNT(*) FROM peminjaman WHERE status='disetujui' AND tgl_kembali < ?", [$hariIni]) ?></b>Terlambat</div>
</div>
<p class="muted">Gunakan menu di kiri untuk mengelola user, alat, kategori, peminjaman, pengembalian, dan melihat log aktivitas.</p>
<?php require '../includes/footer.php'; ?>
