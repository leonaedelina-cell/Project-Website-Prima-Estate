<?php
/**
 * test-koneksi.php - Estate Prima
 * Cuma buat ngecek koneksi database jalan atau enggak.
 * HAPUS FILE INI kalau project udah mau di-deploy/dikumpulkan.
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Test Koneksi Database</h2>";

echo "<p style='color:green;'>&#9989; Koneksi ke database <b>estate_prima</b> berhasil!</p>";

$tabel = ['users', 'agen', 'properti', 'galeri_properti', 'wishlist', 'transaksi'];

echo "<table border='1' cellpadding='8' cellspacing='0'>";
echo "<tr><th>Tabel</th><th>Jumlah Baris</th></tr>";

foreach ($tabel as $nama_tabel) {
    $hasil = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM {$nama_tabel}");
    if ($hasil) {
        $baris = mysqli_fetch_assoc($hasil);
        echo "<tr><td>{$nama_tabel}</td><td>{$baris['total']}</td></tr>";
    } else {
        echo "<tr><td>{$nama_tabel}</td><td style='color:red;'>Error: " . mysqli_error($koneksi) . "</td></tr>";
    }
}

echo "</table>";