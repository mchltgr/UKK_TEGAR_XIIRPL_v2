<?php
require '../config.php'; wajib_login('petugas'); $judul = 'Pemantauan Pengembalian';
$aktif = rows("SELECT p.*, u.nama, a.nama_alat FROM peminjaman p JOIN users u ON u.id=p.user_id JOIN alat a ON a.id=p.alat_id WHERE p.status='disetujui' ORDER BY p.tgl_kembali");
$riwayat = rows("SELECT r.*, p.tgl_kembali, p.jumlah, u.nama, a.nama_alat FROM pengembalian r JOIN peminjaman p ON p.id=r.peminjaman_id JOIN users u ON u.id=p.user_id JOIN alat a ON a.id=p.alat_id ORDER BY r.tgl_dikembalikan DESC, r.id DESC LIMIT 50");
require '../includes/header.php';
?>
<h2>Sedang dipinjam</h2>
<div class="tablewrap"><table>
  <tr><th>#</th><th>Peminjam</th><th>Alat</th><th>Jumlah</th><th>Pinjam</th><th>Batas kembali</th><th>Status</th><th>Estimasi denda</th></tr>
  <?php foreach ($aktif as $d): ?>
  <tr>
    <td><?= $d['id'] ?></td><td><?= e($d['nama']) ?></td><td><?= e($d['nama_alat']) ?></td><td><?= $d['jumlah'] ?></td>
    <td><?= tgl($d['tgl_pinjam']) ?></td><td><?= tgl($d['tgl_kembali']) ?></td><td><?= badge($d['status'], $d['tgl_kembali']) ?></td>
    <td><?= rupiah(hitung_denda($d['tgl_kembali'], date('Y-m-d'))) ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$aktif): ?><tr><td colspan="8" class="empty">Tidak ada alat yang sedang dipinjam.</td></tr><?php endif; ?>
</table></div>

<h2>Riwayat pengembalian</h2>
<div class="tablewrap"><table>
  <tr><th>Peminjam</th><th>Alat</th><th>Batas kembali</th><th>Dikembalikan</th><th>Kondisi</th><th>Denda</th></tr>
  <?php foreach ($riwayat as $d): ?>
  <tr>
    <td><?= e($d['nama']) ?></td><td><?= e($d['nama_alat']) ?> (<?= $d['jumlah'] ?>)</td><td><?= tgl($d['tgl_kembali']) ?></td>
    <td><?= tgl($d['tgl_dikembalikan']) ?></td><td><?= e(ucfirst($d['kondisi'])) ?></td><td><?= rupiah($d['denda']) ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$riwayat): ?><tr><td colspan="6" class="empty">Belum ada pengembalian.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
