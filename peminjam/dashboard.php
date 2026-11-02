<?php
require '../config.php'; wajib_login('peminjam'); $judul = 'Daftar Alat';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alatId = (int)$_POST['alat_id']; $jml = max(1, (int)$_POST['jumlah']); $tk = $_POST['tgl_kembali']; $hari = date('Y-m-d');
    $a = row("SELECT * FROM alat WHERE id=? AND kondisi='baik'", [$alatId]);
    if (!$a)                   flash('Alat tidak tersedia', 'err');
    elseif ($jml > $a['stok']) flash('Jumlah melebihi stok tersedia', 'err');
    elseif ($tk < $hari)       flash('Tanggal kembali tidak boleh sebelum hari ini', 'err');
    else {
        $id = ex("INSERT INTO peminjaman (user_id, alat_id, jumlah, tgl_pinjam, tgl_kembali, status) VALUES (?,?,?,?,?, 'menunggu')",
                 [$_SESSION['user']['id'], $alatId, $jml, $hari, $tk]);
        log_aktivitas("Mengajukan peminjaman {$a['nama_alat']} (#$id)");
        flash('Pengajuan terkirim. Tunggu persetujuan petugas.');
    }
    redirect('dashboard.php');
}

$cari = trim($_GET['cari'] ?? '');
$data = rows("SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON k.id=a.kategori_id
              WHERE a.nama_alat LIKE ? OR k.nama_kategori LIKE ? ORDER BY a.nama_alat", ["%$cari%", "%$cari%"]);
require '../includes/header.php';
?>
<form method="get" class="inline" style="margin-bottom:1rem">
  <input name="cari" placeholder="Cari alat atau kategori" value="<?= e($cari) ?>">
  <button class="btn">Cari</button>
</form>
<div class="tablewrap"><table>
  <tr><th>Alat</th><th>Kategori</th><th>Stok</th><th>Keterangan</th><th>Ajukan peminjaman</th></tr>
  <?php foreach ($data as $d): $bisa = $d['stok'] > 0 && $d['kondisi'] === 'baik'; ?>
  <tr>
    <td><?= e($d['nama_alat']) ?></td><td><?= e($d['nama_kategori']) ?></td>
    <td><?= $d['kondisi'] === 'rusak' ? '<span class="badge ditolak">Rusak</span>' : $d['stok'] ?></td>
    <td><?= e($d['keterangan']) ?></td>
    <td>
      <?php if ($bisa): ?>
      <form method="post" class="inline">
        <input type="hidden" name="alat_id" value="<?= $d['id'] ?>">
        <input type="number" name="jumlah" min="1" max="<?= $d['stok'] ?>" value="1" style="width:70px" aria-label="Jumlah" required>
        <input type="date" name="tgl_kembali" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" aria-label="Tanggal kembali" required>
        <button class="btn small">Ajukan</button>
      </form>
      <?php else: ?><span class="muted">Tidak tersedia</span><?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$data): ?><tr><td colspan="5" class="empty">Alat tidak ditemukan.</td></tr><?php endif; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
