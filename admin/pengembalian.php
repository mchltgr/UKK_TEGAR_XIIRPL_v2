<?php
require '../config.php'; wajib_login('admin'); $judul = 'Kelola Data Pengembalian';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $tgl = $_POST['tgl_dikembalikan'];
    $kondisi = in_array($_POST['kondisi'], ['baik','rusak','hilang']) ? $_POST['kondisi'] : 'baik';
    if ($id) {
        $lama = row("SELECT r.kondisi, p.jumlah, p.alat_id FROM pengembalian r JOIN peminjaman p ON p.id=r.peminjaman_id WHERE r.id=?", [$id]);
        if ($lama) {
            // sesuaikan stok bila status "hilang" berubah
            if ($lama['kondisi'] === 'hilang' && $kondisi !== 'hilang') ex("UPDATE alat SET stok = stok + ? WHERE id=?", [(int)$lama['jumlah'], (int)$lama['alat_id']]);
            if ($lama['kondisi'] !== 'hilang' && $kondisi === 'hilang') ex("UPDATE alat SET stok = GREATEST(stok - ?, 0) WHERE id=?", [(int)$lama['jumlah'], (int)$lama['alat_id']]);
            ex("UPDATE pengembalian SET tgl_dikembalikan=?, kondisi=?, denda=? WHERE id=?", [$tgl, $kondisi, max(0,(int)$_POST['denda']), $id]);
            log_aktivitas("Mengubah pengembalian #$id"); flash('Pengembalian diperbarui');
        }
    } else {
        if (kembalikan((int)$_POST['peminjaman_id'], $kondisi, $tgl)) { log_aktivitas("Menambah pengembalian untuk peminjaman #" . (int)$_POST['peminjaman_id']); flash('Pengembalian dicatat (denda dihitung otomatis)'); }
        else flash('Peminjaman tidak valid atau sudah dikembalikan', 'err');
    }
    redirect('pengembalian.php');
}
if (isset($_GET['hapus'])) {
    $r = row("SELECT r.*, p.jumlah, p.alat_id FROM pengembalian r JOIN peminjaman p ON p.id=r.peminjaman_id WHERE r.id=?", [(int)$_GET['hapus']]);
    if ($r) {
        if ($r['kondisi'] !== 'hilang') ex("UPDATE alat SET stok = GREATEST(stok - ?, 0) WHERE id=?", [(int)$r['jumlah'], (int)$r['alat_id']]);
        ex("UPDATE peminjaman SET status='disetujui' WHERE id=?", [(int)$r['peminjaman_id']]);
        hapus('pengembalian', $r['id'], 'Pengembalian');
    }
    redirect('pengembalian.php');
}

$edit = isset($_GET['edit']) ? row("SELECT * FROM pengembalian WHERE id=?", [(int)$_GET['edit']]) : null;
$aktif = rows("SELECT p.id, u.nama, a.nama_alat, p.jumlah FROM peminjaman p JOIN users u ON u.id=p.user_id JOIN alat a ON a.id=p.alat_id WHERE p.status='disetujui' ORDER BY p.id");
$data = rows("SELECT r.*, p.tgl_kembali, p.jumlah, u.nama, a.nama_alat FROM pengembalian r JOIN peminjaman p ON p.id=r.peminjaman_id JOIN users u ON u.id=p.user_id JOIN alat a ON a.id=p.alat_id ORDER BY r.id DESC");
require '../includes/header.php';
?>
<div class="card">
  <form method="post" class="grid">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <?php if (!$edit): ?>
      <label>Peminjaman yang dikembalikan
        <select name="peminjaman_id" required>
          <?php foreach ($aktif as $a): ?><option value="<?= $a['id'] ?>">#<?= $a['id'] ?> - <?= e($a['nama']) ?> - <?= e($a['nama_alat']) ?> (<?= $a['jumlah'] ?>)</option><?php endforeach; ?>
        </select>
      </label>
    <?php endif; ?>
    <label>Tanggal dikembalikan <input type="date" name="tgl_dikembalikan" required value="<?= e($edit['tgl_dikembalikan'] ?? date('Y-m-d')) ?>"></label>
    <label>Kondisi alat
      <select name="kondisi">
        <?php foreach (['baik','rusak','hilang'] as $k): ?><option value="<?= $k ?>" <?= ($edit['kondisi'] ?? 'baik') === $k ? 'selected' : '' ?>><?= ucfirst($k) ?></option><?php endforeach; ?>
      </select>
    </label>
    <?php if ($edit): ?><label>Denda (Rp) <input type="number" min="0" name="denda" value="<?= (int)$edit['denda'] ?>"></label><?php endif; ?>
    <div class="actions">
      <button class="btn" <?= (!$edit && !$aktif) ? 'disabled' : '' ?>><?= $edit ? 'Simpan perubahan' : 'Catat pengembalian' ?></button>
      <?php if ($edit): ?><a class="btn ghost" href="pengembalian.php">Batal</a><?php endif; ?>
    </div>
  </form>
  <p class="muted">Denda otomatis <?= rupiah(DENDA_PER_HARI) ?> per hari keterlambatan.</p>
</div>
<div class="tablewrap"><table>
  <tr><th>#</th><th>Peminjam</th><th>Alat</th><th>Rencana</th><th>Dikembalikan</th><th>Kondisi</th><th>Denda</th><th>Aksi</th></tr>
  <?php foreach ($data as $d): ?>
  <tr>
    <td><?= $d['id'] ?></td><td><?= e($d['nama']) ?></td><td><?= e($d['nama_alat']) ?> (<?= $d['jumlah'] ?>)</td>
    <td><?= tgl($d['tgl_kembali']) ?></td><td><?= tgl($d['tgl_dikembalikan']) ?></td><td><?= e(ucfirst($d['kondisi'])) ?></td><td><?= rupiah($d['denda']) ?></td>
    <td class="actions">
      <a class="btn small" href="?edit=<?= $d['id'] ?>">Edit</a>
      <a class="btn small danger" href="?hapus=<?= $d['id'] ?>" data-confirm="Hapus data pengembalian ini? Status peminjaman kembali menjadi disetujui.">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$data): ?><tr><td colspan="8" class="empty">Belum ada data pengembalian.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
