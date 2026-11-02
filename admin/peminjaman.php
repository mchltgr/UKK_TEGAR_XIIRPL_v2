<?php
require '../config.php'; wajib_login('admin'); $judul = 'Kelola Data Peminjaman';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $tp = $_POST['tgl_pinjam']; $tk = $_POST['tgl_kembali'];
    if ($tk < $tp) { flash('Tanggal kembali tidak boleh sebelum tanggal pinjam', 'err'); redirect('peminjaman.php'); }
    if ($id) {
        ex("UPDATE peminjaman SET tgl_pinjam=?, tgl_kembali=? WHERE id=?", [$tp, $tk, $id]);
        log_aktivitas("Mengubah peminjaman #$id"); flash('Peminjaman diperbarui');
    } else {
        $new = ex("INSERT INTO peminjaman (user_id, alat_id, jumlah, tgl_pinjam, tgl_kembali, status) VALUES (?,?,?,?,?, 'menunggu')",
                  [(int)$_POST['user_id'], (int)$_POST['alat_id'], max(1,(int)$_POST['jumlah']), $tp, $tk]);
        if (setujui($new, $_SESSION['user']['id'])) { log_aktivitas("Menambah peminjaman #$new"); flash('Peminjaman ditambahkan dan langsung disetujui'); }
        else { ex("DELETE FROM peminjaman WHERE id=?", [$new]); flash('Stok alat tidak mencukupi', 'err'); }
    }
    redirect('peminjaman.php');
}
if (isset($_GET['hapus'])) {
    $p = row("SELECT * FROM peminjaman WHERE id=?", [(int)$_GET['hapus']]);
    if ($p) {
        // Stok hanya perlu dipulihkan jika alat masih sedang dipinjam (status disetujui)
        if ($p['status'] === 'disetujui')
            ex("UPDATE alat SET stok = stok + ? WHERE id=?", [(int)$p['jumlah'], (int)$p['alat_id']]);
        hapus('peminjaman', $p['id'], 'Peminjaman');
    }
    redirect('peminjaman.php');
}

$edit = isset($_GET['edit']) ? row("SELECT * FROM peminjaman WHERE id=?", [(int)$_GET['edit']]) : null;
$users = rows("SELECT id, nama FROM users WHERE role='peminjam' ORDER BY nama");
$alat  = rows("SELECT id, nama_alat, stok FROM alat WHERE kondisi='baik' ORDER BY nama_alat");
$data  = rows("SELECT p.*, u.nama, a.nama_alat FROM peminjaman p JOIN users u ON u.id=p.user_id JOIN alat a ON a.id=p.alat_id ORDER BY p.id DESC");
require '../includes/header.php';
?>
<div class="card">
  <form method="post" class="grid">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <?php if (!$edit): ?>
      <label>Peminjam <select name="user_id" required><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['nama']) ?></option><?php endforeach; ?></select></label>
      <label>Alat <select name="alat_id" required><?php foreach ($alat as $a): ?><option value="<?= $a['id'] ?>"><?= e($a['nama_alat']) ?> (stok <?= $a['stok'] ?>)</option><?php endforeach; ?></select></label>
      <label>Jumlah <input type="number" name="jumlah" min="1" value="1" required></label>
    <?php endif; ?>
    <label>Tanggal pinjam <input type="date" name="tgl_pinjam" required value="<?= e($edit['tgl_pinjam'] ?? date('Y-m-d')) ?>"></label>
    <label>Rencana kembali <input type="date" name="tgl_kembali" required value="<?= e($edit['tgl_kembali'] ?? date('Y-m-d', strtotime('+3 days'))) ?>"></label>
    <div class="actions">
      <button class="btn"><?= $edit ? 'Simpan perubahan' : 'Tambah peminjaman' ?></button>
      <?php if ($edit): ?><a class="btn ghost" href="peminjaman.php">Batal</a><?php endif; ?>
    </div>
  </form>
</div>
<div class="tablewrap"><table>
  <tr><th>#</th><th>Peminjam</th><th>Alat</th><th>Jml</th><th>Pinjam</th><th>Rencana kembali</th><th>Status</th><th>Aksi</th></tr>
  <?php foreach ($data as $d): ?>
  <tr>
    <td><?= $d['id'] ?></td><td><?= e($d['nama']) ?></td><td><?= e($d['nama_alat']) ?></td><td><?= $d['jumlah'] ?></td>
    <td><?= tgl($d['tgl_pinjam']) ?></td><td><?= tgl($d['tgl_kembali']) ?></td><td><?= badge($d['status'], $d['tgl_kembali']) ?></td>
    <td class="actions">
      <a class="btn small" href="?edit=<?= $d['id'] ?>">Edit</a>
      <a class="btn small danger" href="?hapus=<?= $d['id'] ?>" data-confirm="Hapus data peminjaman ini?">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$data): ?><tr><td colspan="8" class="empty">Belum ada data peminjaman.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
