<?php
require '../config.php'; wajib_login('petugas'); $judul = 'Laporan Peminjaman';
$dari = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$data = rows("SELECT p.*, u.nama, a.nama_alat, r.tgl_dikembalikan, r.kondisi, r.denda
              FROM peminjaman p JOIN users u ON u.id=p.user_id JOIN alat a ON a.id=p.alat_id
              LEFT JOIN pengembalian r ON r.peminjaman_id=p.id
              WHERE p.tgl_pinjam BETWEEN ? AND ? ORDER BY p.tgl_pinjam DESC, p.id DESC", [$dari, $sampai]);
$totalDenda = array_sum(array_column($data, 'denda'));
if (isset($_GET['cetak'])) log_aktivitas("Mencetak laporan $dari s/d $sampai");
require '../includes/header.php';
?>
<form method="get" class="grid card no-print">
  <label>Dari tanggal <input type="date" name="dari" value="<?= e($dari) ?>"></label>
  <label>Sampai tanggal <input type="date" name="sampai" value="<?= e($sampai) ?>"></label>
  <div class="actions">
    <button class="btn">Tampilkan</button>
    <button class="btn ghost" type="button" onclick="fetch('?cetak=1&dari=<?= e($dari) ?>&sampai=<?= e($sampai) ?>').finally(function(){window.print()})">Cetak laporan</button>
  </div>
</form>
<p>Periode: <strong><?= tgl($dari) ?></strong> s/d <strong><?= tgl($sampai) ?></strong> &mdash; <?= count($data) ?> transaksi</p>
<div class="tablewrap"><table>
  <tr><th>#</th><th>Peminjam</th><th>Alat</th><th>Jml</th><th>Pinjam</th><th>Batas kembali</th><th>Dikembalikan</th><th>Status</th><th>Denda</th></tr>
  <?php foreach ($data as $d): ?>
  <tr>
    <td><?= $d['id'] ?></td><td><?= e($d['nama']) ?></td><td><?= e($d['nama_alat']) ?></td><td><?= $d['jumlah'] ?></td>
    <td><?= tgl($d['tgl_pinjam']) ?></td><td><?= tgl($d['tgl_kembali']) ?></td><td><?= tgl($d['tgl_dikembalikan']) ?></td>
    <td><?= badge($d['status'], $d['tgl_kembali']) ?></td><td><?= $d['denda'] !== null ? rupiah($d['denda']) : '-' ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$data): ?><tr><td colspan="9" class="empty">Tidak ada data pada periode ini.</td></tr><?php endif; ?>
  <?php if ($data): ?><tr><th colspan="8">Total denda</th><th><?= rupiah($totalDenda) ?></th></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
