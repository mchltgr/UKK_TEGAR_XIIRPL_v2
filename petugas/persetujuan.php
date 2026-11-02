<?php
require '../config.php'; wajib_login('petugas'); $judul = 'Persetujuan Peminjaman';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    if ($_POST['aksi'] === 'setujui') {
        if (setujui($id, $_SESSION['user']['id'])) { log_aktivitas("Menyetujui peminjaman #$id"); flash('Peminjaman disetujui'); }
        else flash('Gagal: stok tidak cukup atau pengajuan sudah diproses', 'err');
    } elseif ($_POST['aksi'] === 'tolak') {
        ex("UPDATE peminjaman SET status='ditolak', petugas_id=? WHERE id=? AND status='menunggu'", [$_SESSION['user']['id'], $id]);
        log_aktivitas("Menolak peminjaman #$id"); flash('Peminjaman ditolak');
    }
    redirect('persetujuan.php');
}
$data = rows("SELECT p.*, u.nama, a.nama_alat, a.stok FROM peminjaman p JOIN users u ON u.id=p.user_id JOIN alat a ON a.id=p.alat_id WHERE p.status='menunggu' ORDER BY p.id");
require '../includes/header.php';
?>
<div class="tablewrap"><table>
  <tr><th>#</th><th>Peminjam</th><th>Alat</th><th>Jumlah</th><th>Stok tersedia</th><th>Pinjam</th><th>Rencana kembali</th><th>Aksi</th></tr>
  <?php foreach ($data as $d): ?>
  <tr>
    <td><?= $d['id'] ?></td><td><?= e($d['nama']) ?></td><td><?= e($d['nama_alat']) ?></td><td><?= $d['jumlah'] ?></td><td><?= $d['stok'] ?></td>
    <td><?= tgl($d['tgl_pinjam']) ?></td><td><?= tgl($d['tgl_kembali']) ?></td>
    <td>
      <form method="post" class="inline">
        <input type="hidden" name="id" value="<?= $d['id'] ?>">
        <button class="btn small ok" name="aksi" value="setujui" <?= $d['stok'] < $d['jumlah'] ? 'disabled' : '' ?>>Setujui</button>
        <button class="btn small danger" name="aksi" value="tolak" onclick="return confirm('Tolak pengajuan ini?')">Tolak</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$data): ?><tr><td colspan="8" class="empty">Tidak ada pengajuan yang menunggu.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
