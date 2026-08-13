<?php
/**
 * listing.php - Estate Prima
 * Semua properti - search + filter (tipe, kota, rentang harga) + pagination
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$q         = trim($_GET['q'] ?? '');
$tipe      = trim($_GET['tipe'] ?? '');
$kota      = trim($_GET['kota'] ?? '');
$harga_min = $_GET['harga_min'] ?? '';
$harga_max = $_GET['harga_max'] ?? '';

$halaman = max((int)($_GET['page'] ?? 1), 1);
$data_per_halaman = 9;
$offset = ($halaman - 1) * $data_per_halaman;

$kondisi = ["status = 'tersedia'"];
$tipe_data = '';
$parameter = [];

if ($q !== '') {
    $kondisi[] = "(judul LIKE ? OR alamat LIKE ? OR kota LIKE ?)";
    $keyword = "%{$q}%";
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $parameter[] = $keyword;
    $tipe_data .= 'sss';
}

if ($tipe !== '' && in_array($tipe, ['rumah', 'apartemen', 'tanah', 'ruko'])) {
    $kondisi[] = "tipe = ?";
    $parameter[] = $tipe;
    $tipe_data .= 's';
}

if ($kota !== '') {
    $kondisi[] = "kota LIKE ?";
    $parameter[] = "%{$kota}%";
    $tipe_data .= 's';
}

if ($harga_min !== '' && is_numeric($harga_min)) {
    $kondisi[] = "harga >= ?";
    $parameter[] = (int)$harga_min;
    $tipe_data .= 'i';
}

if ($harga_max !== '' && is_numeric($harga_max)) {
    $kondisi[] = "harga <= ?";
    $parameter[] = (int)$harga_max;
    $tipe_data .= 'i';
}

$where_sql = implode(' AND ', $kondisi);

$query_total = "SELECT COUNT(*) AS total FROM properti WHERE {$where_sql}";
$stmt_total = mysqli_prepare($koneksi, $query_total);
if (!empty($parameter)) {
    mysqli_stmt_bind_param($stmt_total, $tipe_data, ...$parameter);
}
mysqli_stmt_execute($stmt_total);
$total_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total))['total'];
$total_halaman = max((int)ceil($total_data / $data_per_halaman), 1);
mysqli_stmt_close($stmt_total);

$query_data = "SELECT id, judul, harga, tipe, kota, kamar_tidur, kamar_mandi, luas_bangunan, gambar_url
               FROM properti
               WHERE {$where_sql}
               ORDER BY created_at DESC
               LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($koneksi, $query_data);
$parameter_lengkap = array_merge($parameter, [$data_per_halaman, $offset]);
$tipe_data_lengkap = $tipe_data . 'ii';
mysqli_stmt_bind_param($stmt, $tipe_data_lengkap, ...$parameter_lengkap);
mysqli_stmt_execute($stmt);
$daftar_properti = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Jelajahi properti pilihan Estate Prima.">
<title>Properti - Estate Prima</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
:root{
    --navy:#16324f;
    --navy-dark:#0d1f33;
    --gold:#c9a24b;
    --gold-light:#f5eedf;
    --cream:#f7f5f0;
    --text:#20242b;
    --muted:#6b7280;
    --line:#e3e1da;
}
*{box-sizing:border-box}
body{margin:0;background:var(--cream);color:var(--text);font-family:"Manrope",sans-serif;-webkit-font-smoothing:antialiased}
.navbar-estate{background:var(--navy);padding:1rem 0}
.brand{color:#fff!important;font-family:"Fraunces",serif;font-size:1.45rem;font-weight:600;letter-spacing:.02em;text-decoration:none}
.brand span{color:var(--gold)}
.navbar-estate .nav-link{color:rgba(255,255,255,.78);font-size:.88rem;font-weight:700;margin:0 .5rem}
.navbar-estate .nav-link:hover,.navbar-estate .nav-link.active{color:#e0c179}
.navbar-toggler{border-color:rgba(255,255,255,.25)}
.navbar-toggler:focus{box-shadow:0 0 0 .2rem rgba(201,162,75,.18)}
.btn-gold{background:var(--gold);border-color:var(--gold);color:var(--navy-dark);font-weight:800}
.btn-gold:hover{background:#e0c179;border-color:#e0c179;color:var(--navy-dark)}
.btn-outline-gold{border:1px solid var(--gold);color:#e0c179;font-weight:700}
.btn-outline-gold:hover{background:var(--gold);border-color:var(--gold);color:var(--navy-dark)}

.page-header{
    position:relative;overflow:hidden;background:var(--navy-dark);
    padding:4.7rem 0 6rem
}
.page-header:before{
    content:"";position:absolute;width:430px;height:430px;border:1px solid rgba(201,162,75,.22);
    border-radius:50%;right:-170px;top:-275px
}
.page-header:after{
    content:"";position:absolute;width:270px;height:270px;border-radius:50%;
    background:rgba(201,162,75,.07);left:-150px;bottom:-190px
}
.header-content{position:relative;z-index:1}
.eyebrow{color:var(--gold);font-size:.72rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
.page-title{max-width:760px;color:#fff;font-family:"Fraunces",serif;font-size:clamp(2.35rem,5vw,3.7rem);font-weight:600;line-height:1.08;letter-spacing:-.03em;margin:.7rem 0 1rem}
.page-title em{color:#e0c179;font-style:italic}
.page-description{max-width:650px;color:rgba(255,255,255,.7);line-height:1.8;font-size:.92rem;margin:0}

.filter-wrap{position:relative;z-index:5;margin-top:-55px}
.filter-card{
    background:#fff;border:1px solid var(--line);border-radius:5px;padding:1.45rem;
    box-shadow:0 20px 55px rgba(13,31,51,.10)
}
.filter-title{color:var(--navy);font-size:.85rem;font-weight:800}
.filter-label{
    display:block;color:var(--navy);font-size:.65rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.08em;margin-bottom:.45rem
}
.filter-control{
    min-height:45px;border:1px solid var(--line);border-radius:3px!important;
    color:var(--text);font-size:.78rem;font-weight:600;background:#fff
}
.filter-control:focus{border-color:var(--gold);box-shadow:0 0 0 .2rem rgba(201,162,75,.15)}
.filter-actions{display:flex;gap:7px;height:100%;align-items:end}
.filter-actions input{min-width:0;flex:1}
.filter-actions .btn{min-height:45px}
.active-filter{
    display:inline-flex;align-items:center;gap:6px;background:var(--gold-light);color:#856321;
    border:1px solid rgba(201,162,75,.22);border-radius:999px;padding:6px 10px;font-size:.68rem;font-weight:800
}
.sort-note{color:var(--muted);font-size:.74rem;font-weight:600;text-decoration:none}
.sort-note:hover{color:var(--navy)}

.listing-section{padding:4.5rem 0 6rem}
.result-count{color:var(--navy);font-size:.86rem;font-weight:800}
.result-count span{color:var(--gold)}

.property-card{
    height:100%;overflow:hidden;background:#fff;border:1px solid var(--line);
    border-radius:5px;box-shadow:0 8px 25px rgba(13,31,51,.05);
    transition:transform .25s ease,box-shadow .25s ease
}
.property-card:hover{transform:translateY(-5px);box-shadow:0 20px 40px rgba(13,31,51,.12)}
.property-card a{text-decoration:none}
.property-image{height:245px;position:relative;overflow:hidden;background:#e9e6de}
.property-image img{width:100%;height:100%;display:block;object-fit:cover;transition:transform .45s ease}
.property-card:hover .property-image img{transform:scale(1.045)}
.property-badge{
    position:absolute;top:14px;left:14px;background:rgba(255,255,255,.94);color:var(--navy);
    border-radius:3px;padding:6px 10px;font-size:.66rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em
}
.property-price{
    position:absolute;left:14px;bottom:14px;background:var(--navy-dark);color:#e0c179;
    padding:7px 11px;border-radius:3px;font-family:"Fraunces",serif;font-size:1rem;font-weight:600
}
.property-arrow{
    position:absolute;right:14px;bottom:14px;width:38px;height:38px;display:grid;place-items:center;
    background:var(--gold);color:var(--navy-dark);border-radius:50%;opacity:0;transform:translateY(5px);
    transition:.25s ease
}
.property-card:hover .property-arrow{opacity:1;transform:none}
.property-body{padding:1.2rem 1.2rem 1.25rem}
.property-title{
    color:var(--navy);font-family:"Fraunces",serif;font-size:1.18rem;font-weight:600;
    line-height:1.25;margin:0 0 .45rem
}
.property-location{color:var(--muted);font-size:.75rem;margin-bottom:1rem}
.property-location i,.property-specs i{color:var(--gold)}
.property-specs{
    display:flex;flex-wrap:wrap;gap:1rem;padding-top:.9rem;border-top:1px solid var(--line);
    color:#687384;font-size:.7rem;font-weight:700
}
.property-specs span{display:inline-flex;align-items:center;gap:5px}

.empty-state{
    text-align:center;background:#fff;border:1px solid var(--line);border-radius:5px;padding:4rem 1.5rem
}
.empty-icon{
    width:60px;height:60px;margin:0 auto 1rem;display:grid;place-items:center;
    background:var(--gold-light);color:var(--gold);border-radius:50%;font-size:1.4rem
}
.empty-state h2{font-family:"Fraunces",serif;color:var(--navy);font-size:1.6rem}
.empty-state p{max-width:500px;margin:0 auto 1.4rem;color:var(--muted);font-size:.8rem;line-height:1.75}

.pagination-wrap{margin-top:3rem}
.pagination .page-link{
    min-width:39px;height:39px;display:grid;place-items:center;margin:0 3px;
    border:1px solid var(--line);border-radius:3px!important;color:#687384;background:#fff;
    font-size:.75rem;font-weight:800
}
.pagination .page-link:hover{color:var(--navy);background:var(--gold-light);border-color:#dcc99e}
.pagination .page-item.active .page-link{color:#fff;background:var(--navy);border-color:var(--navy)}
.pagination .page-item.disabled .page-link{background:#f7f5f0;color:#b2b6bd}

.site-footer{background:var(--navy-dark);color:rgba(255,255,255,.62);padding:3.2rem 0 1rem}
.footer-brand{color:#fff;font-family:"Fraunces",serif;font-size:1.3rem;font-weight:600;text-decoration:none}
.footer-brand span{color:var(--gold)}
.site-footer h5{color:#fff;font-family:"Fraunces",serif;font-size:1rem;font-weight:600}
.site-footer p,.site-footer li,.site-footer a{color:rgba(255,255,255,.62);font-size:.75rem;line-height:1.8}
.site-footer a{text-decoration:none}.site-footer a:hover{color:#e0c179}
.footer-line{border-color:rgba(255,255,255,.1)}

@media(max-width:991.98px){
    .page-header{padding:4rem 0 5rem}
    .filter-wrap{margin-top:-42px}
    .filter-actions{height:auto;margin-top:0}
}
@media(max-width:767.98px){
    .navbar-estate{padding:.85rem 0}
    .page-header{padding:3.5rem 0 4.3rem}
    .page-title{font-size:2.35rem}
    .filter-wrap{margin-top:-30px}
    .filter-card{padding:1.15rem}
    .listing-section{padding:3rem 0 4.5rem}
    .property-image{height:220px}
    .property-arrow{opacity:1;transform:none;width:34px;height:34px}
}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-estate sticky-top">
    <div class="container">
        <a class="brand" href="index.php">ESTATE <span>PRIMA</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Buka menu">
            <i class="bi bi-list text-white fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto my-3 my-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link active" href="listing.php">Properti</a></li>
                <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="login.php" class="btn btn-outline-gold btn-sm px-3">Masuk</a>
                <a href="register.php" class="btn btn-gold btn-sm px-3">Daftar</a>
            </div>
        </div>
    </div>
</nav>

<header class="page-header">
    <div class="container header-content">
        <div class="eyebrow">Estate Prima / Property Collection</div>
        <h1 class="page-title">Temukan properti yang <em>sesuai dengan hidupmu.</em></h1>
        <p class="page-description">
            Jelajahi rumah, apartemen, tanah, dan ruko pilihan. Gunakan filter untuk
            mempersempit pencarian berdasarkan lokasi, tipe, dan budget.
        </p>
    </div>
</header>

<section class="filter-wrap">
    <div class="container">
        <div class="filter-card">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="filter-title">
                    <i class="bi bi-sliders2 me-2" style="color:var(--gold)"></i>
                    Cari & Filter Properti
                </div>
                <?php if ($q !== '' || $tipe !== '' || $kota !== '' || $harga_min !== '' || $harga_max !== ''): ?>
                    <a href="listing.php" class="sort-note">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset semua filter
                    </a>
                <?php endif; ?>
            </div>

            <form method="GET" action="listing.php">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <label class="filter-label" for="q">Kata kunci</label>
                        <div class="input-group">
                            <span class="input-group-text filter-control border-end-0 bg-white">
                                <i class="bi bi-search text-secondary"></i>
                            </span>
                            <input id="q" type="text" name="q" class="form-control filter-control border-start-0"
                                   placeholder="Judul, alamat, atau kota..." value="<?= htmlspecialchars($q) ?>">
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label" for="tipe">Tipe</label>
                        <select id="tipe" name="tipe" class="form-select filter-control">
                            <option value="">Semua Tipe</option>
                            <?php foreach (['rumah','apartemen','tanah','ruko'] as $t): ?>
                                <option value="<?= htmlspecialchars($t) ?>" <?= $tipe === $t ? 'selected' : '' ?>>
                                    <?= ucfirst($t) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label" for="kota">Kota</label>
                        <input id="kota" type="text" name="kota" class="form-control filter-control"
                               placeholder="Contoh: Bekasi" value="<?= htmlspecialchars($kota) ?>">
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="filter-label" for="harga_min">Harga minimum</label>
                        <input id="harga_min" type="number" name="harga_min" class="form-control filter-control"
                               placeholder="Rp minimum" min="0" value="<?= htmlspecialchars($harga_min) ?>">
                    </div>

                    <div class="col-lg-2">
                        <label class="filter-label" for="harga_max">Harga maksimum</label>
                        <div class="filter-actions">
                            <input id="harga_max" type="number" name="harga_max" class="form-control filter-control"
                                   placeholder="Rp maksimum" min="0" value="<?= htmlspecialchars($harga_max) ?>">
                            <button type="submit" class="btn btn-gold px-3" title="Terapkan filter">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <?php if ($q !== '' || $tipe !== '' || $kota !== '' || $harga_min !== '' || $harga_max !== ''): ?>
                <div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                    <span class="sort-note me-1 align-self-center">Filter aktif:</span>
                    <?php if ($q !== ''): ?><span class="active-filter"><i class="bi bi-search"></i><?= htmlspecialchars($q) ?></span><?php endif; ?>
                    <?php if ($tipe !== ''): ?><span class="active-filter"><i class="bi bi-house"></i><?= htmlspecialchars(ucfirst($tipe)) ?></span><?php endif; ?>
                    <?php if ($kota !== ''): ?><span class="active-filter"><i class="bi bi-geo-alt"></i><?= htmlspecialchars($kota) ?></span><?php endif; ?>
                    <?php if ($harga_min !== ''): ?><span class="active-filter">Min: Rp <?= number_format((float)$harga_min,0,',','.') ?></span><?php endif; ?>
                    <?php if ($harga_max !== ''): ?><span class="active-filter">Max: Rp <?= number_format((float)$harga_max,0,',','.') ?></span><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<main class="listing-section">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <div class="result-count"><span><?= number_format($total_data) ?></span> properti ditemukan</div>
                <div class="sort-note mt-1">Menampilkan halaman <?= $halaman ?> dari <?= $total_halaman ?></div>
            </div>
            <div class="sort-note"><i class="bi bi-stars me-1" style="color:var(--gold)"></i>Terbaru ditampilkan lebih dahulu</div>
        </div>

        <?php if (empty($daftar_properti)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-house-x"></i></div>
                <h2>Tidak ada properti yang cocok</h2>
                <p>Kami belum menemukan properti yang sesuai dengan filter kamu. Coba ubah kata kunci, lokasi, tipe, atau rentang harga.</p>
                <a href="listing.php" class="btn btn-gold"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Pencarian</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($daftar_properti as $p): ?>
                    <?php $gambar = !empty($p['gambar_url']) ? $p['gambar_url'] : 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=1000&q=85'; ?>
                    <div class="col-md-6 col-lg-4">
                        <article class="property-card">
                            <a href="detail.php?id=<?= (int)$p['id'] ?>" class="d-block text-reset">
                                <div class="property-image">
                                    <img src="<?= htmlspecialchars($gambar) ?>"
                                         alt="<?= htmlspecialchars($p['judul']) ?>"
                                         loading="lazy"
                                         onerror="this.src='https://images.unsplash.com/photo-1568605114967-8130f3a36994?auto=format&fit=crop&w=1000&q=85';">
                                    <span class="property-badge"><i class="bi bi-house-door-fill me-1"></i><?= htmlspecialchars(ucfirst($p['tipe'])) ?></span>
                                    <span class="property-price">Rp <?= number_format($p['harga'],0,',','.') ?></span>
                                    <span class="property-arrow"><i class="bi bi-arrow-up-right"></i></span>
                                </div>
                                <div class="property-body">
                                    <h2 class="property-title"><?= htmlspecialchars($p['judul']) ?></h2>
                                    <div class="property-location"><i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($p['kota']) ?></div>
                                    <div class="property-specs">
                                        <span><i class="bi bi-door-closed-fill"></i><?= (int)$p['kamar_tidur'] ?> KT</span>
                                        <span><i class="bi bi-droplet-fill"></i><?= (int)$p['kamar_mandi'] ?> KM</span>
                                        <span><i class="bi bi-rulers"></i><?= (int)$p['luas_bangunan'] ?> m²</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_halaman > 1): ?>
                <nav class="pagination-wrap" aria-label="Navigasi halaman properti">
                    <ul class="pagination justify-content-center mb-0">
                        <?php
                        $query_string = $_GET;
                        $prev = $halaman - 1;
                        $next = $halaman + 1;
                        ?>
                        <?php if ($halaman > 1): $query_string['page']=$prev; ?>
                            <li class="page-item"><a class="page-link" href="listing.php?<?= http_build_query($query_string) ?>"><i class="bi bi-chevron-left"></i></a></li>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $halaman - 2);
                        $end_page = min($total_halaman, $halaman + 2);
                        ?>
                        <?php if ($start_page > 1): $query_string['page']=1; ?>
                            <li class="page-item"><a class="page-link" href="listing.php?<?= http_build_query($query_string) ?>">1</a></li>
                            <?php if ($start_page > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i=$start_page; $i<=$end_page; $i++): $query_string['page']=$i; ?>
                            <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
                                <a class="page-link" href="listing.php?<?= http_build_query($query_string) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_halaman): ?>
                            <?php if ($end_page < $total_halaman - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                            <?php $query_string['page']=$total_halaman; ?>
                            <li class="page-item"><a class="page-link" href="listing.php?<?= http_build_query($query_string) ?>"><?= $total_halaman ?></a></li>
                        <?php endif; ?>

                        <?php if ($halaman < $total_halaman): $query_string['page']=$next; ?>
                            <li class="page-item"><a class="page-link" href="listing.php?<?= http_build_query($query_string) ?>"><i class="bi bi-chevron-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row g-4 pb-4">
            <div class="col-lg-4">
                <a href="index.php" class="footer-brand">ESTATE <span>PRIMA</span></a>
                <p class="mt-2 mb-0">Membantu kamu menemukan properti yang tepat dengan pencarian yang sederhana, informasi yang jelas, dan pengalaman yang nyaman.</p>
            </div>
            <div class="col-lg-2 col-6">
                <h5>Jelajahi</h5>
                <ul class="list-unstyled mt-2 mb-0">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="listing.php">Semua Properti</a></li>
                    <li><a href="kontak.php">Kontak</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-6">
                <h5>Akun</h5>
                <ul class="list-unstyled mt-2 mb-0">
                    <li><a href="login.php">Masuk</a></li>
                    <li><a href="register.php">Daftar</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5>Kontak</h5>
                <ul class="list-unstyled mt-2 mb-0">
                    <li><i class="bi bi-geo-alt me-2"></i>Jakarta Selatan, Indonesia</li>
                    <li><i class="bi bi-envelope me-2"></i>halo@estateprima.test</li>
                </ul>
            </div>
        </div>
        <hr class="footer-line">
        <p class="text-center py-3 mb-0">&copy; <?= date('Y') ?> Estate Prima. Semua hak dilindungi.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>