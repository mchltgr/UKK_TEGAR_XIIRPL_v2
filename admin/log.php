<?php
require '../config.php'; wajib_login('admin'); $judul = 'Log Aktivitas';
$data = rows("SELECT l.*, u.nama, u.role FROM log_aktivitas l JOIN users u ON u.id=l.user_id ORDER BY l.id DESC LIMIT 300");
require '../includes/header.php';
?>
<p class="muted">300 aktivitas terakhir.</p>
<div class="tablewrap"><table>
  <tr><th>Waktu</th><th>User</th><th>Level</th><th>Aktivitas</th></tr>
  <?php foreach ($data as $d): ?>
  <tr><td><?= e(date('d/m/Y H:i:s', strtotime($d['waktu']))) ?></td><td><?= e($d['nama']) ?></td><td><?= e(ucfirst($d['role'])) ?></td><td><?= e($d['aktivitas']) ?></td></tr>
  <?php endforeach; ?>
  <?php if (!$data): ?><tr><td colspan="4" class="empty">Belum ada aktivitas.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
