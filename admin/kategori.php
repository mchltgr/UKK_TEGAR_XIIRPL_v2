<?php
require '../config.php'; wajib_login('admin'); $judul = 'Kelola Kategori';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0); $nama = trim($_POST['nama_kategori']);
    if ($id) { ex("UPDATE kategori SET nama_kategori=? WHERE id=?", [$nama, $id]); log_aktivitas("Mengubah kategori $nama"); flash('Kategori diperbarui'); }
    else     { ex("INSERT INTO kategori (nama_kategori) VALUES (?)", [$nama]);     log_aktivitas("Menambah kategori $nama"); flash('Kategori ditambahkan'); }
    redirect('kategori.php');
}
if (isset($_GET['hapus'])) { hapus('kategori', $_GET['hapus'], 'Kategori'); redirect('kategori.php'); }

$edit = isset($_GET['edit']) ? row("SELECT * FROM kategori WHERE id=?", [(int)$_GET['edit']]) : null;
$data = rows("SELECT k.*, (SELECT COUNT(*) FROM alat WHERE kategori_id=k.id) AS jml FROM kategori k ORDER BY nama_kategori");
require '../includes/header.php';
?>
<div class="card">
  <form method="post" class="grid">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <label>Nama kategori <input name="nama_kategori" required value="<?= e($edit['nama_kategori'] ?? '') ?>"></label>
    <div class="actions">
      <button class="btn"><?= $edit ? 'Simpan perubahan' : 'Tambah kategori' ?></button>
      <?php if ($edit): ?><a class="btn ghost" href="kategori.php">Batal</a><?php endif; ?>
    </div>
  </form>
</div>
<div class="tablewrap"><table>
  <tr><th>Kategori</th><th>Jumlah alat</th><th>Aksi</th></tr>
  <?php foreach ($data as $d): ?>
  <tr>
    <td><?= e($d['nama_kategori']) ?></td><td><?= $d['jml'] ?></td>
    <td class="actions">
      <a class="btn small" href="?edit=<?= $d['id'] ?>">Edit</a>
      <a class="btn small danger" href="?hapus=<?= $d['id'] ?>" data-confirm="Hapus kategori ini?">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
