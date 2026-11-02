<?php
require '../config.php'; wajib_login('petugas'); $judul = 'Dashboard Petugas';
$hariIni = date('Y-m-d');
require '../includes/header.php';
?>
<div class="stats">
  <div class="stat warn"><b><?= hitung("SELECT COUNT(*) FROM peminjaman WHERE status='menunggu'") ?></b>Menunggu persetujuan</div>
  <div class="stat"><b><?= hitung("SELECT COUNT(*) FROM peminjaman WHERE status='disetujui'") ?></b>Sedang dipinjam</div>
  <div class="stat bad"><b><?= hitung("SELECT COUNT(*) FROM peminjaman WHERE status='disetujui' AND tgl_kembali < ?", [$hariIni]) ?></b>Terlambat</div>
  <div class="stat"><b><?= hitung("SELECT COUNT(*) FROM pengembalian WHERE tgl_dikembalikan = ?", [$hariIni]) ?></b>Dikembalikan hari ini</div>
</div>
<a class="btn" href="persetujuan.php">Proses pengajuan</a>
<?php require '../includes/footer.php'; ?>
