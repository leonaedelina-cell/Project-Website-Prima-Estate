# Ujian — PHP Fullstack

Ujian penutup materi PHP Fullstack (Sesi 1-8). Dua bagian: **Soal Teori** (20 soal, ngetes paham konsep dari Sesi 1-8) dan **Soal Project** (bikin website baru dari nol, domain beda dari latihan tapi pola sama persis).

**Tech stack:** PHP native + MySQL + Bootstrap CSS (boleh eksplor Tailwind CSS kalau mau)

**Closed-book buat soal teori.** Soal project boleh buka-buka README sesi 1-8 buat inget pola/struktur, tapi logic-nya tetep ditulis sendiri, bukan copy-paste.

> 💡 Ada **mockup tampilan statis** (HTML/CSS/Bootstrap, gak perlu database) di folder [`mockup/`](./mockup/) — buka `mockup/index.html` pakai Live Server buat liat gambaran akhir tampilan website-nya sebelum mulai coding. Cara jalaninnya ada di `mockup/README.md`.

## 🚀 Mulai Dari Sini

Dokumen ini panjang — jangan dibaca sekaligus dari atas ke bawah. Ikutin urutan ini:

1. **Kerjain dulu Soal Teori** (20 soal di bawah) — closed-book, tulis jawabannya di file terpisah (misal `jawaban-teori.md`).
2. **Buka `mockup/index.html`** pakai Live Server — klik-klik semua halamannya, biar kebayang target akhirnya kayak apa sebelum mulai ngoding.
3. **Baca bagian "0. Alur Bisnis"** di Soal Project — ini kunci ngerti kenapa ada fitur transaksi/WA/status pembayaran.
4. **Ikutin "4. Step-by-Step Pengerjaan" satu-satu, jangan loncat.** Tiap step nyebutin sesi mana yang polanya dicontek (misal "pola persis `admin/buku.php`") — buka file itu di folder sesi yang dimaksud, bandingin, baru tulis versi kamu sendiri buat domain properti.
5. Kalau ada bagian yang bikin bingung, cek dulu section terkaitnya di README ini (Struktur Database / Struktur Folder / Fitur per Role) — biasanya udah dijawab di situ.
6. Cek **Checklist Sebelum Submit** di paling bawah sebelum ngerasa "selesai".

Kalau masih bingung juga setelah ngikutin urutan di atas, baru tanya — sebutin **udah nyoba step berapa** dan **bingungnya di bagian mana**, biar gampang dibantu.

**Catatan buat yang masih pemula:** ini emang project paling gede di seluruh materi, wajar kalau kerasa berat pas pertama liat. Tapi coba dicek lagi — **hampir semua bagiannya BUKAN hal baru**. Login/register, CRUD, wishlist, search+pagination, styling Bootstrap — itu semua udah pernah dibikin sendiri di Sesi 5-8, cuma sekarang dipraktekin ke domain beda (properti, bukan buku). Yang beneran baru cuma 2: sistem transaksi (tabel `transaksi` + redirect WhatsApp) dan Google Maps embed — dan dua-duanya udah ada contoh kodenya lengkap di README ini.

Jangan coba selesein semua dalam 1 hari. Kerjain 1-2 step dari "Step-by-Step Pengerjaan" per sesi belajar, tes jalan dulu baru lanjut step berikutnya. Kalau stuck di 1 step lebih dari biasanya, itu sinyal buat break dulu, buka lagi sesi yang jadi rujukannya, baca ulang pola aslinya, baru balik lagi — bukan sinyal buat nyerah.

## Bootstrap vs Tailwind

Dokumentasi resmi, dua-duanya gratis dibaca tanpa akun:

- **Bootstrap**: https://getbootstrap.com/docs/5.3/getting-started/introduction/
- **Tailwind CSS**: https://tailwindcss.com/docs

Beda pendekatannya:

|                 | Bootstrap (Sesi 8, yang udah dipelajari)                                                                   | Tailwind (opsional, belum diajarin)                                                                                                               |
| --------------- | ---------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| Cara kerja      | **Component-based** — udah ada nama class buat komponen jadi (`.btn`, `.card`, `.navbar`), tinggal pasang  | **Utility-first** — class-nya kecil-kecil per 1 properti CSS doang (`flex`, `p-4`, `rounded-lg`, `bg-blue-500`), gabungin sendiri jadi komponen   |
| Contoh tombol   | `<button class="btn btn-dark">Simpan</button>` — style tombol udah lengkap dari 1 class `btn` + `btn-dark` | `<button class="bg-gray-900 text-white px-4 py-2 rounded">Simpan</button>` — tiap bagian style (warna, padding, radius) class-nya sendiri-sendiri |
| Kecepatan mulai | Lebih cepet buat pemula — komponen siap pakai, gak perlu mikir kombinasi                                   | Lebih lama di awal — harus apal/cari nama utility-nya dulu, tapi lebih bebas custom                                                               |
| Hasil akhir     | Website gampang keliatan "mirip template" kalau gak di-custom (banyak orang pake Bootstrap default)        | Lebih gampang bikin desain unik, soalnya emang nyusun dari nol tiap kali                                                                          |
| Setup           | CDN doang, tinggal `<link>` (kayak yang dipake di Sesi 8)                                                  | Bisa CDN Play (`<script src="https://cdn.tailwindcss.com">`, buat belajar/coba-coba) atau build process (CLI/PostCSS, buat production)            |

Kalau mau nyoba Tailwind di soal project: paling gampang pake **CDN Play** dulu (`<script src="https://cdn.tailwindcss.com"></script>` di `<head>`), sama kayak cara Bootstrap CDN dipake di Sesi 8 — gak perlu install apa-apa, tinggal baca dokumentasinya buat nyari nama utility class yang dibutuhin.

---

## Daftar Isi

- [Soal Ujian Teori (20 Soal)](#soal-ujian-teori-20-soal)
- [Soal Project — Website Jual/Sewa Properti](#soal-project--website-jualsewa-properti)
  - [0. Alur Bisnis (Kenapa Gak Ada Payment Gateway)](#0-alur-bisnis)
  - [1. Struktur Database](#1-struktur-database)
  - [2. Struktur Folder](#2-struktur-folder)
  - [3. Fitur per Role](#3-fitur-per-role)
  - [4. Step-by-Step Pengerjaan](#4-step-by-step-pengerjaan)
  - [5. Integrasi WhatsApp (Bukan Payment Gateway)](#5-integrasi-whatsapp)
  - [Checklist Sebelum Submit](#checklist-sebelum-submit)

---

## Soal Ujian Teori (20 Soal)

20 soal teori nyakup semua sesi (`1-Fundamental-PHP` sampe `8-Bootstrap-Integration`). Jawab pakai kalimat sendiri, boleh sertain kode kalau perlu buat jelasin. Total nilai 100 (5 poin/soal).

**1.** (Sesi 1) Jelasin beda `==` sama `===` di PHP. Kasih 1 contoh kasus yang hasilnya beda kalau pakai `==` vs `===`.

**2.** (Sesi 1) Apa bedanya array indexed sama array asosiatif? Kapan masing-masing lebih cocok dipakai?

**3.** (Sesi 1) Jelasin fungsi `include`/`require`. Apa bedanya sama `include_once`/`require_once`, dan kenapa bedanya itu penting buat file kayak `config/db.php`?

**4.** (Sesi 2) Jelasin beda `$_GET` dan `$_POST`. Kenapa form hapus data sebaiknya pakai POST, bukan link biasa yang jalanin GET?

**5.** (Sesi 2) Apa itu XSS? Jelasin gimana `htmlspecialchars()` nyegah XSS, kasih contoh input yang berbahaya kalau gak di-`htmlspecialchars()`.

**6.** (Sesi 3) Apa fungsi `PRIMARY KEY` dan `AUTO_INCREMENT` di MySQL? Apa yang kejadian ke nomor id kalau ada baris yang dihapus?

**7.** (Sesi 3) Jelasin fungsi `WHERE`, `ORDER BY`, dan `LIMIT`/`OFFSET` di query `SELECT`. Kalau digabung bertiga, urutan penulisannya gimana?

**8.** (Sesi 4) Jelasin kenapa prepared statement (`mysqli_prepare` + `bind_param`) nyegah SQL Injection — bukan cuma "karena disuruh pakai", jelasin mekanismenya.

**9.** (Sesi 4) Apa itu pola "redirect after POST" (`header("Location: ...")` + `exit`)? Masalah apa yang dicegah pola ini?

**10.** (Sesi 5) Kenapa password harus disimpen pakai `password_hash()`, bukan plaintext atau di-enkripsi biasa? Jelasin juga fungsi `password_verify()`.

**11.** (Sesi 5) Jelasin cara kerja `$_SESSION` buat "inget" siapa yang login. Kenapa `session_start()` harus dipanggil sebelum ada output HTML apapun?

**12.** (Sesi 6) Apa bedanya `auth-check.php` sama `admin-check.php` (kalau di project kamu namanya beda, jelasin pola yang sama)? Kenapa admin-check butuh auth-check duluan?

**13.** (Sesi 6) Kenapa nyembunyiin tombol/link admin pakai CSS (`display:none`) **bukan** security yang cukup? Apa yang harus dicek di server?

**14.** (Sesi 7) Kenapa relasi many-to-many (misal: user bisa favoritin banyak buku, 1 buku bisa difavoritin banyak user) butuh table pivot/junction sendiri, gak bisa numpang kolom di salah satu table yang ada?

**15.** (Sesi 7) Apa fungsi `ON DELETE CASCADE` di foreign key? Kasih contoh kasus di project kamu yang makein ini.

**16.** (Sesi 7) Jelasin cara kerja fitur Search pakai `LIKE` di query. Kenapa parameternya (`"%$keyword%"`) tetep harus di-bind lewat prepared statement, bukan digabung langsung ke string SQL?

**17.** (Sesi 7) Jelasin cara kerja Pagination (`LIMIT`/`OFFSET`). Kenapa butuh query `COUNT(*)` terpisah buat nentuin jumlah halaman?

**18.** (Sesi 8) Apa itu Bootstrap? Jelasin beda pakai Bootstrap CDN vs download filenya taro lokal di project.

**19.** (Sesi 8) Jelasin cara kerja Modal Bootstrap (`data-bs-toggle="modal"`, `data-bs-target`). Kenapa ini dianggap lebih baik daripada `confirm()` JavaScript bawaan buat konfirmasi hapus?

**20.** (Sesi 8) Kapan sebuah bagian HTML (misal navbar atau sidebar) layak dipisah jadi partial (`include`/`require`), dan kapan mendingan dibiarin nempel di file itu sendiri? Kasih alasannya, bukan cuma aturan hafalan.

---

## Soal Project — Website Jual/Sewa Properti

Bikin website **jual/sewa properti (rumah/estate)** dari nol. Beda tema dari latihan (`7-Buku-dan-Favorite`, `8-Bootstrap-Integration` — tema toko buku), tapi **pola arsitekturnya sama persis** — CRUD, auth, role, relasi many-to-many, Bootstrap. Tiap bagian di bawah nyambungin balik ke sesi yang relevan.

### 0. Alur Bisnis

Website ini **gak ada payment gateway** — pembayaran diarahin manual ke WhatsApp, dicatet manual sama admin. Alurnya:

1. User login, buka detail properti, klik **"Ajukan Beli"** atau **"Ajukan Sewa"** (teks tombol ngikutin `tipe_transaksi` properti itu).
2. Sistem langsung **INSERT 1 baris ke table `transaksi`** (status `menunggu`), abis itu redirect ke `https://wa.me/<nomor>?text=<pesan otomatis>`.
3. Nego harga/DP/jadwal survey kejadian di WhatsApp beneran, di luar sistem — website gak nyimpen chat-nya.
4. Setelah admin terima pembayaran (transfer/cash/e-wallet/QRIS), admin buka panel admin, cari transaksi itu, isi **metode pembayaran** + link **bukti pembayaran** + update **status** (`menunggu` → `diproses` → `lunas`).
5. User bisa mantengin status pesanannya sendiri di halaman "Pesanan Saya".

Kenapa gitu? Karena payment gateway beneran (Midtrans, Xendit, dll) butuh API key + akun bisnis terverifikasi — di luar scope belajar PHP native. WhatsApp link (`wa.me`) gak butuh API/auth sama sekali, jadi cocok buat latihan.

### 1. Struktur Database

Masih 1 database (bikin baru, misal `properti_db`), 4 table. Jalanin dulu di phpMyAdmin (tab SQL) buat bikin database-nya, sebelum lanjut ke query `CREATE TABLE` di bawah:

```sql
CREATE DATABASE properti_db;
USE properti_db;
```

`CREATE DATABASE` bikin database kosong baru. `USE` nge-set database mana yang lagi "aktif" dipakai — tanpa ini, query `CREATE TABLE` sesudahnya gak tau mau nyimpen table-nya ke database mana. Bisa juga langsung pilih database-nya lewat sidebar phpMyAdmin (klik nama database di kiri) — kalau gitu, `USE` gak wajib ditulis lagi soalnya udah otomatis "aktif" dari situ.

**`users`** (pola sama Sesi 5/6, tambah 1 kolom):

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20),
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**`properti`** (pola sama `buku` di Sesi 7, kolomnya lebih banyak — kelompokin form-nya pas bikin UI, jangan 1 kolom form panjang lurus):

```sql
CREATE TABLE properti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    tipe_transaksi ENUM('jual', 'sewa') NOT NULL DEFAULT 'jual',
    durasi_minimal ENUM('6 bulan', '1 tahun') NULL,  -- cuma keisi kalau tipe_transaksi = 'sewa'
    harga DECIMAL(15, 2) NOT NULL,
    lokasi VARCHAR(150) NOT NULL,
    kamar_tidur INT NOT NULL DEFAULT 0,
    kamar_mandi INT NOT NULL DEFAULT 0,
    luas_tanah INT NOT NULL,   -- meter persegi
    luas_bangunan INT NOT NULL,
    fasilitas TEXT,             -- dipisah koma, misal: "AC, Garasi, Kolam Renang"
    status_hunian ENUM('kosong', 'terisi') NOT NULL DEFAULT 'kosong',
    status_jual ENUM('tersedia', 'terjual') NOT NULL DEFAULT 'tersedia',
    gambar_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**`wishlist`** (pola PERSIS `favorit` di Sesi 7, many-to-many):

```sql
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    properti_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (properti_id) REFERENCES properti(id) ON DELETE CASCADE,
    UNIQUE KEY unik_wishlist (user_id, properti_id)
);
```

**`transaksi`** (baru — relasi 1 user bisa banyak transaksi, 1 properti bisa ada banyak yang minat):

```sql
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    properti_id INT NOT NULL,
    status ENUM('menunggu', 'diproses', 'lunas') NOT NULL DEFAULT 'menunggu',
    metode_pembayaran ENUM('transfer', 'cash', 'e-wallet', 'qris') NULL,
    bukti_pembayaran_url VARCHAR(500) NULL,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (properti_id) REFERENCES properti(id) ON DELETE CASCADE
);
```

### 2. Struktur Folder

Pola sama persis `8-Bootstrap-Integration/` — reuse aja `config/db.php`, `includes/auth-check.php`, `includes/admin-check.php` (tinggal ganti nama database & path redirect):

```
9-Ujian-Project/
  README.md               <- file ini
  setup.sql                 <- CREATE TABLE semua + data contoh
  config/
    db.php
  includes/
    auth-check.php
    admin-check.php
    navbar.php               <- publik: Home/Listing/Kontak + tombol Login/Register atau dropdown user
    user-sidebar.php          <- area login: Dashboard/Wishlist/Pesanan Saya/Edit Profile/Logout
    admin-sidebar.php          <- area admin: Dashboard/Kelola Properti/Kelola Transaksi/Kelola Users/Logout
  index.php                <- homepage: hero + listing terbaru
  listing.php                <- semua properti, search (judul/lokasi) + filter (tipe_transaksi/status) + pagination
  properti-detail.php         <- ?id=..., galeri gambar, detail lengkap, tombol Wishlist + tombol Ajukan Beli/Sewa
  kontak.php                   <- contact sales (dummy info + tombol WA umum + embed Google Maps lokasi kantor)
  wishlist-toggle.php           <- handler like/unlike, WAJIB login (pola sama favorit-toggle.php)
  ajukan-transaksi.php           <- handler bikin transaksi + redirect WA, WAJIB login
  register.php / login.php / logout.php
  dashboard.php                  <- user: overview wishlist + pesanan
  wishlist-saya.php                <- daftar lengkap wishlist
  pesanan-saya.php                  <- daftar transaksi milik user + status masing-masing
  profil.php                         <- ganti username & password
  admin/
    dashboard.php                    <- statistik: total properti, user, transaksi per status
    properti.php                       <- list + hapus (+ pagination, nomor urut tampilan)
    properti-tambah.php
    properti-edit.php
    transaksi.php                       <- list semua transaksi, filter status, link ke detail
    transaksi-detail.php                 <- ?id=..., form update metode+status+bukti pembayaran
    users.php                             <- kelola role user (pola sama Sesi 6/7/8)
```

#### 📁 Langkah 0 — Bikin Folder & File Kosong Dulu (Sebelum Nulis Kode Apapun)

Ikutin persis biar strukturnya bener dari awal, gak perlu mikir "taro di mana" pas lagi nulis kode nanti.

1. Buka File Explorer, masuk ke `C:\laragon\www\` (folder tempat `php-journey` juga ada).
2. Bikin folder baru di situ, kasih nama **`properti-prima`** (atau nama lain, terserah — tapi inget-inget namanya, dipakai pas buka di browser nanti).
3. Buka folder `properti-prima` itu pakai VS Code (klik kanan folder → "Open with Code", atau buka VS Code → File → Open Folder).
4. Di dalem VS Code, bikin folder & file kosong ini satu-satu (klik kanan di panel Explorer kiri → New Folder / New File):

```
properti-prima/
  config/
    db.php
  includes/
    auth-check.php
    admin-check.php
    navbar.php
    user-sidebar.php
    admin-sidebar.php
  admin/
    dashboard.php
    properti.php
    properti-tambah.php
    properti-edit.php
    transaksi.php
    transaksi-detail.php
    users.php
  index.php
  listing.php
  properti-detail.php
  kontak.php
  wishlist-toggle.php
  ajukan-transaksi.php
  register.php
  login.php
  logout.php
  dashboard.php
  wishlist-saya.php
  pesanan-saya.php
  profil.php
  setup.sql
```

5. Semua file di atas **boleh kosong dulu** — isinya baru ditulis pelan-pelan ngikutin "4. Step-by-Step Pengerjaan" di bawah, gak harus langsung lengkap semua.
6. Buka Laragon, klik **Start All** (Apache + MySQL nyala).
7. Tes dulu folder-nya kebaca: buka browser, akses `http://localhost/properti-prima/index.php` — kalau muncul halaman putih kosong (bukan error "Not Found"), berarti struktur foldernya udah bener, tinggal mulai isi kodenya dari `setup.sql` (Step 1).

### 3. Fitur per Role

**Guest (belum login):**

- Liat homepage, listing properti, detail properti, halaman kontak.
- **Gak bisa** wishlist atau ajukan beli/sewa — diarahin ke halaman login kalau nyoba.

**User (login, role `user`):**

- Semua yang guest bisa, plus:
- Wishlist/unwishlist properti.
- Ajukan Beli/Sewa (bikin transaksi + redirect WA).
- Liat "Pesanan Saya" — status transaksi dia sendiri (gak bisa liat punya orang lain, walau tau ID-nya).
- Edit profile (ganti username & password).

**Admin (login, role `admin`):**

- Semua yang user bisa (tapi gak perlu wishlist/ajukan, itu buat customer).
- CRUD properti penuh (tambah/edit/hapus).
- Liat semua transaksi dari semua user, update metode pembayaran + status + bukti pembayaran.
- Kelola role user (promote/demote, hapus user — gak bisa hapus/demote akun sendiri, pola sama Sesi 6).

### 4. Step-by-Step Pengerjaan

Ikutin urutan ini, tiap step nyambung ke sesi yang relevan buat dicontek polanya (bukan kodenya mentah-mentah):

1. **Setup database** — jalanin `setup.sql`, isi minimal 6-8 properti contoh (pola sama `7-Buku-dan-Favorite/setup.sql`).
2. **Auth (register/login/logout)** — copy pola persis dari `8-Bootstrap-Integration/` (`register.php`, `login.php`, `logout.php`, `includes/auth-check.php`). Cuma ganti nama database di `config/db.php`.
3. **CRUD Properti (admin)** — pola persis `admin/buku.php` + `buku-tambah.php` + `buku-edit.php`, tapi form-nya dikelompokin (fieldset/section: Info Dasar, Detail Fisik, Fasilitas & Status) soalnya kolomnya lebih banyak dari `buku`.
4. **Listing + Detail (publik)** — pola persis `katalog.php`, tambah filter `tipe_transaksi` & `status_jual` di query (`WHERE` tambahan, sama konsepnya kayak search).
5. **Wishlist** — copy PERSIS pola `favorit-toggle.php` + relasi `favorit` di Sesi 7, ganti nama table/kolom doang.
6. **Transaksi + WhatsApp** — bagian baru. `ajukan-transaksi.php`: cek login (`auth-check.php`), INSERT ke `transaksi`, susun pesan WA (`urlencode()` teksnya!), `header("Location: https://wa.me/62xxx?text=...")`.
7. **Role Management (Sesi 6 pattern)** — `admin-check.php`, `admin/users.php` sama persis pola Sesi 6/7/8, cuma reuse.
8. **Dashboard User & Admin** — pola persis `dashboard.php` + `admin/dashboard.php` di Sesi 8 (statistik pakai `COUNT()`, quick action cards).
9. **Kelola Transaksi (admin)** — bagian baru: `admin/transaksi.php` (list + filter status, badge warna per status) dan `admin/transaksi-detail.php` (form update, mirip `admin/buku-edit.php` tapi field-nya metode/status/bukti).
10. **Styling Bootstrap** — pola persis Sesi 8: navbar dropdown pas login, modal buat konfirmasi hapus properti, badge buat status, pagination di listing & tabel admin.

### 5. Integrasi WhatsApp

Bukan API, cuma link biasa (`wa.me`), jadi gak butuh setup/API key apapun:

```php
$nomorAdmin = "6281234567890"; // format internasional, tanpa "+" atau "0" di depan
$pesan = "Halo, saya tertarik dengan properti \"" . $properti['judul'] . "\" (ID: " . $properti['id'] . "). Bisa dibantu info lebih lanjut?";
$waLink = "https://wa.me/" . $nomorAdmin . "?text=" . urlencode($pesan);

header("Location: " . $waLink);
exit;
```

`urlencode()` WAJIB — pesan ada spasi & karakter spesial, kalau gak di-encode linknya rusak/salah interpretasi browser.

### 6. Lokasi Kantor via Google Maps

Di halaman `kontak.php` (dan boleh juga di `properti-detail.php`), tampilin lokasi pake **Google Maps embed** — bukan Maps JavaScript API (itu butuh API key + billing account), cukup `<iframe>` biasa yang gratis:

```html
<iframe
    src="https://www.google.com/maps?q=Jl.+Sudirman+No.+88,+Jakarta+Pusat&output=embed"
    style="border:0; width:100%; height:400px;"
    allowfullscreen loading="lazy">
</iframe>
```

Ganti nilai `q=` sama alamat kantor (spasi diganti `+`) atau koordinat (`q=-6.2088,106.8456`). Bootstrap punya class `ratio` (`ratio-16x9`, `ratio-21x9`) buat jaga proporsi iframe-nya tanpa nulis CSS manual:
```html
<div class="ratio ratio-21x9">
    <iframe src="..." style="border:0;" allowfullscreen loading="lazy"></iframe>
</div>
```

### Checklist Sebelum Submit

- [ ] `setup.sql` jalan tanpa error, ada minimal 6-8 data properti contoh
- [ ] Register, login, logout jalan; password ke-hash (cek di phpMyAdmin, jangan plaintext)
- [ ] Guest gak bisa wishlist/ajukan transaksi — ke-redirect ke login
- [ ] User cuma bisa liat "Pesanan Saya" miliknya sendiri (coba akses transaksi user lain lewat URL manual → harus ketolak/gak nongol)
- [ ] Admin bisa CRUD properti penuh, form-nya gak numpuk lurus (dikelompokin section)
- [ ] Klik "Ajukan Beli"/"Ajukan Sewa" → transaksi ke-insert ke database DAN redirect ke WhatsApp beneran
- [ ] Admin bisa update metode pembayaran + status + bukti pembayaran di 1 transaksi
- [ ] Search & filter di listing jalan bareng pagination (`?q=...&tipe=jual&page=2` gak saling ilangin)
- [ ] Semua query pakai prepared statement, semua output pakai `htmlspecialchars()`
- [ ] Delete (properti/user) pakai POST + modal konfirmasi Bootstrap, bukan link GET
- [ ] Navbar publik beda tampilan login/belum login (dropdown vs tombol Login/Register)
- [ ] Sidebar admin & sidebar user kepisah, gak ketuker menu
- [ ] Halaman Kontak nampilin lokasi kantor pake Google Maps embed (`<iframe>`, bukan cuma alamat teks doang)
