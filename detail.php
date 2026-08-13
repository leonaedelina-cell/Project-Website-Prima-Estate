<?php
/**
 * detail.php - Estate Prima
 * Detail 1 properti: galeri, fasilitas, kartu agen, tombol Wishlist & Ajukan Beli
 * Akses: detail.php?id=1
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Properti tidak ditemukan.');
}

// ------------------------------------------------------------------
// 1. Ambil data properti + join data agen
// ------------------------------------------------------------------
$query = "SELECT p.*, a.nama AS nama_agen, a.no_hp AS no_hp_agen, a.email AS email_agen, a.foto_url AS foto_agen
          FROM properti p
          LEFT JOIN agen a ON p.agen_id = a.id
          WHERE p.id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$properti = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$properti) {
    die('Properti tidak ditemukan.');
}

// ------------------------------------------------------------------
// 2. Ambil galeri foto tambahan
// ------------------------------------------------------------------
$stmt = mysqli_prepare($koneksi, "SELECT gambar_url FROM galeri_properti WHERE properti_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$galeri = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// ------------------------------------------------------------------
// 3. Cek status: user ini sudah login? sudah wishlist properti ini?
// ------------------------------------------------------------------
$user = user_login();
$sudah_wishlist = false;

if ($user) {
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM wishlist WHERE user_id = ? AND properti_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $user['id'], $id);
    mysqli_stmt_execute($stmt);
    $sudah_wishlist = (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

// Pesan sukses/gagal dari redirect proses-wishlist.php / proses-transaksi.php (lihat query string ?pesan=...)
$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($properti['judul']) ?> - Estate Prima</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{--navy:#16324f;--navy-dark:#0d1f33;--gold:#c9a24b;--cream:#f7f5f0;--text:#20242b;--muted:#6b7280;--line:#e3e1da}
*{box-sizing:border-box}body{margin:0;background:var(--cream);color:var(--text);font-family:Manrope,sans-serif}.navbar-estate{background:var(--navy);padding:1rem 0}.brand{color:#fff!important;font-family:Fraunces,serif;font-size:1.45rem;font-weight:600;letter-spacing:.02em;text-decoration:none}.brand span{color:var(--gold)}.navbar-estate .nav-link{color:rgba(255,255,255,.78);font-size:.88rem;font-weight:700;margin:0 .5rem}.navbar-estate .nav-link:hover,.navbar-estate .nav-link.active{color:#e0c179}.navbar-toggler{border-color:rgba(255,255,255,.25)}.navbar-toggler:focus{box-shadow:0 0 0 .2rem rgba(201,162,75,.18)}.btn-gold{background:var(--gold);border-color:var(--gold);color:var(--navy-dark);font-weight:800}.btn-gold:hover{background:#e0c179;border-color:#e0c179;color:var(--navy-dark)}.btn-outline-gold{border:1px solid var(--gold);color:#e0c179;font-weight:700}.btn-outline-gold:hover{background:var(--gold);border-color:var(--gold);color:var(--navy-dark)}
.page{padding:2.2rem 0 5rem}.back{color:var(--muted);font-size:.75rem;font-weight:700;text-decoration:none}.back:hover{color:var(--navy)}.eyebrow{color:var(--gold);font-size:.68rem;font-weight:800;letter-spacing:.15em;text-transform:uppercase}.title{font-family:Fraunces,serif;color:var(--navy);font-size:clamp(2rem,4vw,3rem);line-height:1.1;font-weight:600;margin:.45rem 0}.muted{color:var(--muted);font-size:.78rem}.gallery{display:grid;grid-template-columns:2fr 1fr;grid-template-rows:205px 205px;gap:8px;margin-top:1.5rem}.gallery img{width:100%;height:100%;object-fit:cover;border-radius:3px}.gallery .main{grid-row:1/3}.gallery-empty{background:#e9e6df;display:flex;align-items:center;justify-content:center;color:#888;border-radius:3px;font-size:.75rem}.card-estate{background:#fff;border:1px solid var(--line);border-radius:4px;box-shadow:0 12px 35px rgba(13,31,51,.06)}.summary{padding:1.7rem}.price{font-family:Fraunces,serif;font-size:1.8rem;font-weight:600;color:var(--navy)}.status{display:inline-block;padding:.35rem .65rem;background:#edf5ef;color:#39704a;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;border-radius:2px}.specs{display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--line);border-bottom:1px solid var(--line);margin:1.5rem 0}.spec{padding:1rem .5rem;border-right:1px solid var(--line);text-align:center}.spec:last-child{border-right:0}.spec i{display:block;color:var(--gold);font-size:1rem;margin-bottom:.35rem}.spec strong{display:block;color:var(--navy);font-size:.78rem}.spec small{color:var(--muted);font-size:.6rem}.section{padding:1.7rem}.section h2{font-family:Fraunces,serif;color:var(--navy);font-size:1.55rem;margin:0 0 1rem}.desc{font-size:.78rem;color:#555;line-height:1.9}.feature{display:flex;align-items:center;gap:.7rem;padding:.75rem 0;border-bottom:1px solid #eee;font-size:.73rem}.feature i{color:var(--gold);width:18px}.agent{display:flex;align-items:center;gap:1rem}.agent-photo{width:58px;height:58px;border-radius:50%;object-fit:cover;background:#eee}.agent-name{font-family:Fraunces,serif;color:var(--navy);font-size:1.1rem}.agent-meta{font-size:.68rem;color:var(--muted)}.sticky-card{position:sticky;top:95px}.map{height:320px}.map iframe{width:100%;height:100%;border:0}.action-area{padding:1.5rem;border-top:1px solid var(--line)}.form-select{font-size:.76rem;min-height:45px;border-color:var(--line);border-radius:3px}.form-select:focus{border-color:var(--gold);box-shadow:0 0 0 .2rem rgba(201,162,75,.14)}.notice{font-size:.72rem;border-radius:3px;padding:.8rem 1rem}.notice.success{background:#f1f8f3;border:1px solid #cfe5d4;color:#326b43}.notice.warn{background:#fff7e8;border:1px solid #ead7ad;color:#88651f}.site-footer{background:var(--navy-dark);color:rgba(255,255,255,.62);padding:3rem 0 1rem}.footer-brand{color:#fff;font-family:Fraunces,serif;font-size:1.3rem;text-decoration:none}.footer-brand span{color:var(--gold)}.site-footer h5{color:#fff;font-family:Fraunces,serif;font-size:1rem}.site-footer p,.site-footer li,.site-footer a{color:rgba(255,255,255,.62);font-size:.74rem;line-height:1.8}.site-footer a{text-decoration:none}.site-footer a:hover{color:#e0c179}.footer-line{border-color:rgba(255,255,255,.1)}
@media(max-width:767px){.page{padding-top:1.5rem}.gallery{grid-template-columns:1fr;grid-template-rows:260px 130px 130px}.gallery .main{grid-row:auto}.sticky-card{position:static}.specs{grid-template-columns:repeat(2,1fr)}.spec:nth-child(2){border-right:0}.spec:nth-child(3){grid-column:1/3;border-top:1px solid var(--line);border-right:0}.map{height:270px}}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-estate sticky-top"><div class="container">
<a class="brand" href="index.php">ESTATE <span>PRIMA</span></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><i class="bi bi-list text-white fs-4"></i></button>
<div class="collapse navbar-collapse" id="mainNav"><ul class="navbar-nav mx-auto my-3 my-lg-0">
<li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li><li class="nav-item"><a class="nav-link active" href="listing.php">Properti</a></li><li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li></ul>
<div class="d-flex align-items-center gap-2"><a href="login.php" class="btn btn-outline-gold btn-sm px-3">Masuk</a><a href="register.php" class="btn btn-gold btn-sm px-3">Daftar</a></div></div></div></nav>

<main class="page"><div class="container">
<a href="listing.php" class="back"><i class="bi bi-arrow-left me-1"></i>Kembali ke Listing</a>
<div class="row g-4 mt-1">
<div class="col-lg-8">
<div class="eyebrow">Estate Prima / Detail Properti</div><h1 class="title"><?= htmlspecialchars($properti['judul']) ?></h1>
<div class="muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($properti['alamat']) ?>, <?= htmlspecialchars($properti['kota']) ?></div>
<div class="gallery">
<?php if (!empty($properti['gambar_url'])): ?><img class="main" src="<?= htmlspecialchars($properti['gambar_url']) ?>" alt="<?= htmlspecialchars($properti['judul']) ?>"><?php else: ?><div class="gallery-empty main"><i class="bi bi-image fs-1"></i></div><?php endif; ?>
<?php if (!empty($galeri)): ?><?php foreach(array_slice($galeri,0,2) as $g): ?><img src="<?= htmlspecialchars($g['gambar_url']) ?>" alt="Galeri properti"><?php endforeach; ?><?php else: ?><div class="gallery-empty"><i class="bi bi-image"></i></div><div class="gallery-empty"><i class="bi bi-image"></i></div><?php endif; ?>
</div>
<div class="card-estate summary mt-4"><div class="d-flex justify-content-between align-items-start gap-3"><div><div class="eyebrow">Harga Properti</div><div class="price">Rp <?= number_format($properti['harga'],0,',','.') ?></div></div><span class="status"><?= htmlspecialchars(ucfirst($properti['status'])) ?></span></div>
<div class="specs"><div class="spec"><i class="bi bi-house-door"></i><strong><?= htmlspecialchars(ucfirst($properti['tipe'])) ?></strong><small>Tipe</small></div><div class="spec"><i class="bi bi-bounding-box"></i><strong><?= $properti['luas_tanah'] ?? '-' ?> m²</strong><small>Luas Tanah</small></div><div class="spec"><i class="bi bi-building"></i><strong><?= $properti['luas_bangunan'] ?? '-' ?> m²</strong><small>Luas Bangunan</small></div><div class="spec"><i class="bi bi-door-open"></i><strong><?= $properti['kamar_tidur'] ?></strong><small>Kamar</small></div><div class="spec"><i class="bi bi-droplet"></i><strong><?= $properti['kamar_mandi'] ?></strong><small>Bath</small></div><div class="spec"><i class="bi bi-car-front"></i><strong><?= $properti['carport'] ?></strong><small>Carport</small></div></div>
<h2 class="font-serif" style="color:var(--navy);font-size:1.55rem">Deskripsi</h2><p class="desc mb-0"><?= nl2br(htmlspecialchars($properti['deskripsi'])) ?></p></div>

<div class="card-estate section mt-4"><div class="eyebrow">Property Features</div><h2>Fasilitas</h2><div class="row g-0"><div class="col-md-6"><div class="feature"><i class="bi bi-house"></i><?= htmlspecialchars(ucfirst($properti['tipe'])) ?></div><div class="feature"><i class="bi bi-bounding-box"></i><?= $properti['luas_tanah'] ?? '-' ?> m² luas tanah</div><div class="feature"><i class="bi bi-building"></i><?= $properti['luas_bangunan'] ?? '-' ?> m² luas bangunan</div></div><div class="col-md-6 ps-md-4"><div class="feature"><i class="bi bi-door-open"></i><?= $properti['kamar_tidur'] ?> kamar tidur</div><div class="feature"><i class="bi bi-droplet"></i><?= $properti['kamar_mandi'] ?> kamar mandi</div><div class="feature"><i class="bi bi-car-front"></i><?= $properti['carport'] ?> carport</div></div></div></div>

<div class="card-estate mt-4"><div class="section"><div class="eyebrow">Location</div><h2>Lokasi</h2></div><div class="map"><iframe src="https://www.google.com/maps?q=<?= urlencode($properti['alamat'].', '.$properti['kota']) ?>&output=embed" allowfullscreen loading="lazy"></iframe></div></div>
</div>

<div class="col-lg-4"><div class="sticky-card">
<div class="card-estate">
<div class="section"><div class="eyebrow">Estate Prima</div><h2>Agen Sales</h2>
<?php if ($properti['nama_agen']): ?><div class="agent"><?php if (!empty($properti['foto_agen'])): ?><img class="agent-photo" src="<?= htmlspecialchars($properti['foto_agen']) ?>" alt="Foto agen"><?php else: ?><div class="agent-photo d-grid place-items-center text-secondary text-center pt-3"><i class="bi bi-person fs-3"></i></div><?php endif; ?><div><div class="agent-name"><?= htmlspecialchars($properti['nama_agen']) ?></div><div class="agent-meta"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($properti['no_hp_agen']) ?></div><div class="agent-meta"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($properti['email_agen']) ?></div></div></div><?php else: ?><p class="muted mb-0">Informasi agen belum tersedia.</p><?php endif; ?></div>
<div class="action-area">
<?php if ($pesan === 'wishlist-ditambah'): ?><div class="notice success mb-3"><i class="bi bi-check-circle me-1"></i>Berhasil ditambahkan ke wishlist.</div><?php elseif ($pesan === 'wishlist-dihapus'): ?><div class="notice success mb-3"><i class="bi bi-check-circle me-1"></i>Berhasil dihapus dari wishlist.</div><?php elseif ($pesan === 'pengajuan-berhasil'): ?><div class="notice success mb-3"><i class="bi bi-check-circle me-1"></i>Pengajuan beli berhasil dikirim!</div><?php elseif ($pesan === 'sudah-diajukan'): ?><div class="notice warn mb-3"><i class="bi bi-exclamation-circle me-1"></i>Kamu sudah pernah mengajukan pembelian untuk properti ini.</div><?php endif; ?>
<?php if (!$user): ?><p class="muted mb-3">Silakan login untuk menambah wishlist atau mengajukan pembelian.</p><a href="login.php" class="btn btn-gold w-100">Login untuk Melanjutkan</a>
<?php else: ?><form method="POST" action="proses-wishlist.php" class="mb-2"><input type="hidden" name="properti_id" value="<?= $properti['id'] ?>"><button class="btn btn-outline-gold w-100" type="submit"><i class="bi bi-heart me-2"></i><?= $sudah_wishlist ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' ?></button></form>
<?php if ($properti['status'] === 'tersedia'): ?><form method="POST" action="proses-transaksi.php"><input type="hidden" name="properti_id" value="<?= $properti['id'] ?>"><select name="metode_bayar" class="form-select mb-2" required><option value="">Pilih Metode Bayar</option><option value="transfer_bank">Transfer Bank</option><option value="cicilan_kpr">Cicilan KPR</option><option value="tunai">Tunai</option></select><button class="btn btn-gold w-100" type="submit"><i class="bi bi-send me-2"></i>Ajukan Beli</button></form><?php else: ?><div class="notice warn mt-2"><i class="bi bi-info-circle me-1"></i>Properti ini sudah terjual.</div><?php endif; ?><?php endif; ?>
</div></div></div></div>
</div></div></main>

<footer class="site-footer"><div class="container"><div class="row g-4 pb-4"><div class="col-lg-4"><a href="index.php" class="footer-brand">ESTATE <span>PRIMA</span></a><p class="mt-2 mb-0">Platform pencarian dan pengajuan pembelian properti — rumah, apartemen, tanah, dan ruko di lokasi pilihan.</p></div><div class="col-lg-2 col-6"><h5>Jelajahi</h5><ul class="list-unstyled mt-2"><li><a href="index.php">Beranda</a></li><li><a href="listing.php">Semua Properti</a></li><li><a href="kontak.php">Kontak</a></li></ul></div><div class="col-lg-2 col-6"><h5>Akun</h5><ul class="list-unstyled mt-2"><li><a href="login.php">Masuk</a></li><li><a href="register.php">Daftar</a></li></ul></div><div class="col-lg-4"><h5>Kontak</h5><ul class="list-unstyled mt-2"><li><i class="bi bi-geo-alt me-2"></i>Jakarta Selatan, Indonesia</li><li><i class="bi bi-envelope me-2"></i>halo@estateprima.test</li></ul></div></div><hr class="footer-line"><p class="text-center py-3 mb-0">&copy; <?= date('Y') ?> Estate Prima. Semua hak dilindungi.</p></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>