<?php
require '../config.php'; wajib_login('peminjam'); $judul = 'Pinjaman Saya';
$uid = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $milik = row("SELECT id FROM peminjaman WHERE id=? AND user_id=?", [$id, $uid]);
    if ($milik && kembalikan($id, $_POST['kondisi'], date('Y-m-d'))) {
        $denda = hitung("SELECT denda FROM pengembalian WHERE peminjaman_id=?", [$id]);
        log_aktivitas("Mengembalikan alat (peminjaman #$id)");
        flash('Alat berhasil dikembalikan.' . ($denda ? ' Denda keterlambatan: ' . rupiah($denda) : ''));
    } else flash('Pengembalian gagal diproses', 'err');
    redirect('pinjaman.php');
}
$data = rows("SELECT p.*, a.nama_alat, r.tgl_dikembalikan, r.denda, r.kondisi
              FROM peminjaman p JOIN alat a ON a.id=p.alat_id LEFT JOIN pengembalian r ON r.peminjaman_id=p.id
              WHERE p.user_id=? ORDER BY p.id DESC", [$uid]);
require '../includes/header.php';
?>
<div class="tablewrap"><table>
  <tr><th>Alat</th><th>Jml</th><th>Pinjam</th><th>Batas kembali</th><th>Status</th><th>Denda</th><th>Aksi</th></tr>
  <?php foreach ($data as $d): ?>
  <tr>
    <td><?= e($d['nama_alat']) ?></td><td><?= $d['jumlah'] ?></td><td><?= tgl($d['tgl_pinjam']) ?></td><td><?= tgl($d['tgl_kembali']) ?></td>
    <td><?= badge($d['status'], $d['tgl_kembali']) ?></td>
    <td><?= $d['denda'] !== null ? rupiah($d['denda']) : '-' ?></td>
    <td>
      <?php if ($d['status'] === 'disetujui'): ?>
      <form method="post" class="inline" onsubmit="return confirm('Kembalikan alat ini sekarang?')">
        <input type="hidden" name="id" value="<?= $d['id'] ?>">
        <select name="kondisi" aria-label="Kondisi alat"><option value="baik">Baik</option><option value="rusak">Rusak</option><option value="hilang">Hilang</option></select>
        <button class="btn small ok">Kembalikan</button>
      </form>
      <?php elseif ($d['status'] === 'dikembalikan'): ?><span class="muted">Dikembalikan <?= tgl($d['tgl_dikembalikan']) ?></span>
      <?php else: ?>-<?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$data): ?><tr><td colspan="7" class="empty">Belum ada peminjaman. Pilih alat di menu Daftar Alat.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
