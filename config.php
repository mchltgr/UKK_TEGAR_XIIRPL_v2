<?php
// ====== KONFIGURASI ======
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set('Asia/Jakarta');
session_start();

$db = new mysqli('localhost', 'root', '', 'peminjaman_alat');   // sesuaikan jika perlu
$db->set_charset('utf8mb4');

define('DENDA_PER_HARI', 5000);

// BASE otomatis (mis. /peminjaman_alat/)
$root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$dir  = str_replace('\\', '/', __DIR__);
define('BASE', stripos($dir, $root) === 0 ? rtrim(substr($dir, strlen($root)), '/') . '/' : '/peminjaman_alat/');

// ====== HELPER DATABASE ======
function q($sql, $p = [])
{
    global $db;
    $st = $db->prepare($sql);
    if ($p) {
        $t = '';
        foreach ($p as $x) $t .= is_int($x) ? 'i' : 's';
        $st->bind_param($t, ...$p);
    }
    $st->execute();
    $r = $st->get_result();
    return $r ?: null;
}
function rows($sql, $p = [])
{
    $r = q($sql, $p);
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
function row($sql, $p = [])
{
    $r = rows($sql, $p);
    return $r[0] ?? null;
}
function hitung($sql, $p = [])
{
    $r = row($sql, $p);
    return $r ? (int)array_values($r)[0] : 0;
}
function ex($sql, $p = [])
{
    global $db;
    q($sql, $p);
    return $db->insert_id;
}

// ====== HELPER UMUM ======
function e($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function redirect($url)
{
    header("Location: $url");
    exit;
}
function rupiah($n)
{
    return 'Rp ' . number_format((int)$n, 0, ',', '.');
}
function tgl($d)
{
    return $d ? date('d/m/Y', strtotime($d)) : '-';
}

function flash($pesan = null, $tipe = 'ok')
{
    if ($pesan !== null) {
        $_SESSION['flash'] = [$pesan, $tipe];
        return '';
    }
    if (isset($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return "<div class='alert $t'>" . e($m) . "</div>";
    }
    return '';
}

function wajib_login($role = null)
{
    if (empty($_SESSION['user'])) redirect(BASE . 'login.php');
    if ($role && $_SESSION['user']['role'] !== $role) redirect(BASE . 'index.php');
}

function log_aktivitas($teks)
{
    if (!empty($_SESSION['user']))
        ex("INSERT INTO log_aktivitas (user_id, aktivitas) VALUES (?, ?)", [(int)$_SESSION['user']['id'], $teks]);
}

function hapus($tabel, $id, $label)
{
    try {
        ex("DELETE FROM $tabel WHERE id=?", [(int)$id]);
        log_aktivitas("Menghapus $label #$id");
        flash("$label berhasil dihapus");
    } catch (mysqli_sql_exception $e) {
        flash("$label tidak bisa dihapus karena masih dipakai data lain", 'err');
    }
}

function badge($status, $tglKembali = null)
{
    if ($status === 'disetujui' && $tglKembali && $tglKembali < date('Y-m-d')) $status = 'terlambat';
    return "<span class='badge $status'>" . e(ucfirst($status)) . "</span>";
}

// ====== LOGIKA PEMINJAMAN ======
function setujui($id, $petugasId)
{
    $p = row("SELECT * FROM peminjaman WHERE id=? AND status='menunggu'", [(int)$id]);
    if (!$p) return false;
    $a = row("SELECT stok FROM alat WHERE id=?", [(int)$p['alat_id']]);
    if (!$a || $a['stok'] < $p['jumlah']) return false;
    ex("UPDATE alat SET stok = stok - ? WHERE id=?", [(int)$p['jumlah'], (int)$p['alat_id']]);
    ex("UPDATE peminjaman SET status='disetujui', petugas_id=? WHERE id=?", [(int)$petugasId, (int)$id]);
    return true;
}

function hitung_denda($rencana, $aktual)
{
    $hari = (strtotime($aktual) - strtotime($rencana)) / 86400;
    return $hari > 0 ? (int)floor($hari) * DENDA_PER_HARI : 0;
}

function kembalikan($id, $kondisi, $tglAktual, $denda = null)
{
    $p = row("SELECT * FROM peminjaman WHERE id=? AND status='disetujui'", [(int)$id]);
    if (!$p) return false;
    if (!in_array($kondisi, ['baik', 'rusak', 'hilang'])) $kondisi = 'baik';
    if ($denda === null) $denda = hitung_denda($p['tgl_kembali'], $tglAktual);
    ex(
        "INSERT INTO pengembalian (peminjaman_id, tgl_dikembalikan, kondisi, denda) VALUES (?,?,?,?)",
        [(int)$id, $tglAktual, $kondisi, (int)$denda]
    );
    ex("UPDATE peminjaman SET status='dikembalikan' WHERE id=?", [(int)$id]);
    if ($kondisi !== 'hilang')
        ex("UPDATE alat SET stok = stok + ? WHERE id=?", [(int)$p['jumlah'], (int)$p['alat_id']]);
    return true;
}
