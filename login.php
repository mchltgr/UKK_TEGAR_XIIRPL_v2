<?php
require 'config.php';

if (!empty($_SESSION['user'])) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $u = row("SELECT id, nama, username, password, role FROM users WHERE username=? LIMIT 1", [$username]);

  if ($u && is_string($password) && password_verify($password, $u['password'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = [
      'id'       => (int)$u['id'],
      'nama'     => $u['nama'],
      'username' => $u['username'],
      'role'     => $u['role'],
    ];
    log_aktivitas('Login ke sistem');
    redirect('index.php');
  }
  $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk - Peminjaman Alat</title>
  <link rel="stylesheet" href="<?= BASE ?>assets/style.css">
</head>

<body class="login-page">
  <main class="login-box">
    <h1>Peminjaman Alat</h1>
    <p class="muted">Masuk untuk mengelola atau meminjam alat.</p>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <label>Username <input name="username" autocomplete="username" required autofocus></label>
      <label>Password <input type="password" name="password" autocomplete="current-password" required></label>
      <button class="btn" type="submit">Masuk</button>
    </form>
  </main>
</body>

</html>