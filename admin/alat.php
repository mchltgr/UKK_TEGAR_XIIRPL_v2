<?php
require '../config.php'; wajib_login('admin'); $judul = 'Kelola Alat';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $f = [(int)$_POST['kategori_id'], trim($_POST['nama_alat']), max(0,(int)$_POST['stok']),
          $_POST['kondisi'] === 'rusak' ? 'rusak' : 'baik', trim($_POST['keterangan'])];
    if ($id) { ex("UPDATE alat SET kategori_id=?, nama_alat=?, stok=?, kondisi=?, keterangan=? WHERE id=?", [...$f, $id]); log_aktivitas("Mengubah alat {$f[1]}"); flash('Alat diperbarui'); }
    else     { ex("INSERT INTO alat (kategori_id, nama_alat, stok, kondisi, keterangan) VALUES (?,?,?,?,?)", $f);      log_aktivitas("Menambah alat {$f[1]}"); flash('Alat ditambahkan'); }
    redirect('alat.php');
}
if (isset($_GET['hapus'])) { hapus('alat', $_GET['hapus'], 'Alat'); redirect('alat.php'); }

$edit = isset($_GET['edit']) ? row("SELECT * FROM alat WHERE id=?", [(int)$_GET['edit']]) : null;
$kat  = rows("SELECT * FROM kategori ORDER BY nama_kategori");
$data = rows("SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON k.id=a.kategori_id ORDER BY a.nama_alat");
require '../includes/header.php';
?>
<div class="card">
<?php if (!$kat): ?>
  <p>Tambahkan kategori terlebih dahulu sebelum menambah alat.</p>
<?php else: ?>
  <form method="post" class="grid">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <label>Nama alat <input name="nama_alat" required value="<?= e($edit['nama_alat'] ?? '') ?>"></label>
    <label>Kategori
      <select name="kategori_id">
        <?php foreach ($kat as $k): ?><option value="<?= $k['id'] ?>" <?= ($edit['kategori_id'] ?? 0) == $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Stok <input type="number" min="0" name="stok" required value="<?= e($edit['stok'] ?? 1) ?>"></label>
    <label>Kondisi
      <select name="kondisi">
        <option value="baik" <?= ($edit['kondisi'] ?? '') === 'baik' ? 'selected' : '' ?>>Baik</option>
        <option value="rusak" <?= ($edit['kondisi'] ?? '') === 'rusak' ? 'selected' : '' ?>>Rusak</option>
      </select>
    </label>
    <label>Keterangan <input name="keterangan" value="<?= e($edit['keterangan'] ?? '') ?>"></label>
    <div class="actions">
      <button class="btn"><?= $edit ? 'Simpan perubahan' : 'Tambah alat' ?></button>
      <?php if ($edit): ?><a class="btn ghost" href="alat.php">Batal</a><?php endif; ?>
    </div>
  </form>
<?php endif; ?>
</div>
<div class="tablewrap"><table>
  <tr><th>Alat</th><th>Kategori</th><th>Stok</th><th>Kondisi</th><th>Keterangan</th><th>Aksi</th></tr>
  <?php foreach ($data as $d): ?>
  <tr>
    <td><?= e($d['nama_alat']) ?></td><td><?= e($d['nama_kategori']) ?></td><td><?= $d['stok'] ?></td>
    <td><?= e(ucfirst($d['kondisi'])) ?></td><td><?= e($d['keterangan']) ?></td>
    <td class="actions">
      <a class="btn small" href="?edit=<?= $d['id'] ?>">Edit</a>
      <a class="btn small danger" href="?hapus=<?= $d['id'] ?>" data-confirm="Hapus alat ini?">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
