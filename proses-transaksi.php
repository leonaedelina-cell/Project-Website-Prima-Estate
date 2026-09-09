<?php
/**
 * proses-transaksi.php - Estate Prima
 * Menangani submit "Ajukan Beli / Sewa" dari detail.php.
 * Insert baris baru ke tabel transaksi dengan status default 'menunggu'
 * lalu mengarahkan user ke WhatsApp admin.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

cek_login();
cek_csrf();

$user_id         = $_SESSION['user_id'];
$properti_id     = (int)($_POST['properti_id'] ?? 0);
$tipe_transaksi  = null;
$durasi_sewa     = isset($_POST['durasi_sewa']) ? (int)$_POST['durasi_sewa'] : null;
$metode_bayar    = $_POST['metode_bayar'] ?? '';

$metode_valid        = ['transfer_bank', 'cicilan_kpr', 'tunai', 'e-wallet', 'qris'];
$tipe_transaksi_valid = ['jual', 'sewa'];

if ($properti_id <= 0 || !in_array($metode_bayar, $metode_valid, true)) {
    die('Data pengajuan tidak valid.');
}

mysqli_begin_transaction($koneksi);
try {
    // Ambil data properti untuk validasi status & kalkulasi harga
    $stmt = mysqli_prepare($koneksi, "SELECT judul, harga, harga_sewa, periode_sewa, minimal_sewa, tipe_transaksi, status FROM properti WHERE id = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "i", $properti_id);
    mysqli_stmt_execute($stmt);
    $properti = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$properti || $properti['status'] !== 'tersedia') {
        throw new RuntimeException('Properti sudah tidak tersedia.');
    }
    if (!in_array($properti['tipe_transaksi'], $tipe_transaksi_valid, true)) {
        throw new RuntimeException('Tipe transaksi properti tidak valid.');
    }
    // Tipe transaksi selalu mengikuti data properti, bukan nilai dari browser.
    $tipe_transaksi = $properti['tipe_transaksi'];

    // Cek transaksi menggantung
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM transaksi
         WHERE user_id = ? AND properti_id = ? AND status IN ('menunggu', 'diproses')"
    );
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $properti_id);
    mysqli_stmt_execute($stmt);
    $sudah_ada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($sudah_ada) {
        mysqli_rollback($koneksi);
        header("Location: detail.php?id={$properti_id}&pesan=sudah-diajukan");
        exit;
    }

    // Hitung total harga & periode tanggal jika transaksi sewa
    $total_harga = $properti['harga'];
    $tanggal_mulai = null;
    $tanggal_selesai = null;

    if ($tipe_transaksi === 'sewa') {
        $min_sewa = (int)($properti['minimal_sewa'] ?? 1);
        if (!$durasi_sewa || $durasi_sewa < $min_sewa) {
            throw new RuntimeException("Minimal durasi sewa adalah {$min_sewa} " . ($properti['periode_sewa'] ?? 'bulan') . ".");
        }

        $total_harga = (float)$properti['harga_sewa'] * $durasi_sewa;
        $tanggal_mulai = date('Y-m-d');
        
        $satuan_waktu = ($properti['periode_sewa'] === 'tahun') ? 'year' : 'month';
        $tanggal_selesai = date('Y-m-d', strtotime("+{$durasi_sewa} {$satuan_waktu}"));
    }

    // Simpan data transaksi
    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO transaksi (user_id, properti_id, tipe_transaksi, durasi_sewa, tanggal_mulai, tanggal_selesai, total_harga, metode_bayar, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')"
    );
    mysqli_stmt_bind_param(
        $stmt, 
        "iisissss", 
        $user_id, 
        $properti_id, 
        $tipe_transaksi, 
        $durasi_sewa, 
        $tanggal_mulai, 
        $tanggal_selesai, 
        $total_harga, 
        $metode_bayar
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException('Pengajuan gagal disimpan.');
    }
    
    $transaksi_id = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);
    mysqli_commit($koneksi);

} catch (Throwable $error) {
    mysqli_rollback($koneksi);
    die($error->getMessage());
}

// Susun teks WhatsApp
$kata_transaksi = $tipe_transaksi === 'sewa' ? 'menyewa' : 'membeli';
$pesan_wa = "Halo Admin Estate Prima, saya ingin mengajukan untuk {$kata_transaksi} properti:\n\n";
$pesan_wa .= "• ID Transaksi: #{$transaksi_id}\n";
$pesan_wa .= "• Properti: " . ($properti['judul'] ?? 'Properti') . " (ID: {$properti_id})\n";

if ($tipe_transaksi === 'sewa') {
    $pesan_wa .= "• Durasi Sewa: {$durasi_sewa} " . ($properti['periode_sewa'] ?? 'bulan') . "\n";
    $pesan_wa .= "• Periode: {$tanggal_mulai} s/d {$tanggal_selesai}\n";
}

$pesan_wa .= "• Total Harga: Rp " . number_format($total_harga, 0, ',', '.') . "\n";
$pesan_wa .= "• Metode Bayar: " . ucwords(str_replace('_', ' ', $metode_bayar)) . "\n\n";
$pesan_wa .= "Mohon arahan untuk proses selanjutnya. Terima kasih!";

$wa_link = 'https://wa.me/' . WHATSAPP_ADMIN . '?text=' . urlencode($pesan_wa);
header('Location: ' . $wa_link);
exit;