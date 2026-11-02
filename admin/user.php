<?php
require '../config.php'; wajib_login('admin'); $judul = 'Kelola User';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama']); $username = trim($_POST['username']); $pw = $_POST['password'];
    $role = in_array($_POST['role'], ['admin','petugas','peminjam']) ? $_POST['role'] : 'peminjam';

    if (row("SELECT id FROM users WHERE username=? AND id<>?", [$username, $id])) {
        flash('Username sudah dipakai', 'err');
    } elseif ($id) {
        if ($pw !== '') ex("UPDATE users SET nama=?, username=?, role=?, password=? WHERE id=?", [$nama,$username,$role,password_hash($pw, PASSWORD_DEFAULT),$id]);
        else            ex("UPDATE users SET nama=?, username=?, role=? WHERE id=?", [$nama,$username,$role,$id]);
        log_aktivitas("Mengubah user $username"); flash('User diperbarui');
    } elseif ($pw === '') {
        flash('Password wajib diisi untuk user baru', 'err');
    } else {
        ex("INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)", [$nama,$username,password_hash($pw, PASSWORD_DEFAULT),$role]);
        log_aktivitas("Menambah user $username"); flash('User ditambahkan');
    }
    redirect('user.php');
}
if (isset($_GET['hapus'])) {
    if ((int)$_GET['hapus'] === $_SESSION['user']['id']) flash('Anda tidak bisa menghapus akun sendiri', 'err');
    else hapus('users', $_GET['hapus'], 'User');
    redirect('user.php');
}

$edit = isset($_GET['edit']) ? row("SELECT * FROM users WHERE id=?", [(int)$_GET['edit']]) : null;
$data = rows("SELECT * FROM users ORDER BY role, nama");
require '../includes/header.php';
?>
<div class="card">
  <form method="post" class="grid">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <label>Nama lengkap <input name="nama" required value="<?= e($edit['nama'] ?? '') ?>"></label>
    <label>Username <input name="username" required value="<?= e($edit['username'] ?? '') ?>"></label>
    <label>Password <input type="password" name="password" <?= $edit ? '' : 'required' ?> placeholder="<?= $edit ? 'Kosongkan jika tidak diganti' : '' ?>"></label>
    <label>Level
      <select name="role">
        <?php foreach (['admin','petugas','peminjam'] as $r): ?>
          <option value="<?= $r ?>" <?= ($edit['role'] ?? 'peminjam') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <div class="actions">
      <button class="btn"><?= $edit ? 'Simpan perubahan' : 'Tambah user' ?></button>
      <?php if ($edit): ?><a class="btn ghost" href="user.php">Batal</a><?php endif; ?>
    </div>
  </form>
</div>
<div class="tablewrap"><table>
  <tr><th>Nama</th><th>Username</th><th>Level</th><th>Aksi</th></tr>
  <?php foreach ($data as $d): ?>
  <tr>
    <td><?= e($d['nama']) ?></td><td><?= e($d['username']) ?></td><td><?= e(ucfirst($d['role'])) ?></td>
    <td class="actions">
      <a class="btn small" href="?edit=<?= $d['id'] ?>">Edit</a>
      <a class="btn small danger" href="?hapus=<?= $d['id'] ?>" data-confirm="Hapus user ini?">Hapus</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
