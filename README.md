# Estate Prima — Progress Backend

Dokumentasi progress pengerjaan backend project **Estate Prima** (website jual-beli properti).
Simpan file ini di root project biar gampang dilanjutin kapan aja, termasuk kalau sesi chat sebelumnya hilang.

---

## Upload to github


## 1. Stack & Konvensi

| Hal | Keputusan |
|---|---|
| Backend | **PHP native** (bukan framework) |
| Database | **MySQL**, akses pakai **mysqli (procedural)** — bukan PDO |
| Query dengan input user | **Selalu prepared statement**: `mysqli_prepare()` + `mysqli_stmt_bind_param()` + `mysqli_stmt_execute()` |
| Password | `password_hash()` / `password_verify()`, jangan pernah simpan plain text |
| Session | `session_start()` di tiap file yang butuh, helper di `includes/auth.php` |
| Routing | Tidak pakai router — tiap halaman = 1 file `.php` fisik, sesuai daftar halaman mockup di `README.md` |
| Path/URL | Pakai konstanta `BASE_URL` (didefinisikan di `config/database.php`) supaya link & redirect selalu benar dari kedalaman folder manapun (root, `pages/user/`, `pages/admin/`) |
| Gambar properti & bukti bayar | Disimpan sebagai **URL string** (bukan upload file fisik), konsisten sama pola `gambar_url` di README mockup |
| Environment testing | Laragon, path project: `C:\laragon\www\belajar-php\9-Ujian-Project\Project-Website\` |

**Kenapa mysqli, bukan PDO?** Karena course/kebiasaan sebelumnya sudah pakai mysqli untuk mayoritas query (termasuk CRUD), jadi kita ikuti itu biar konsisten dengan gaya penulisan kode yang sudah terbiasa dipakai.

---

## 2. Struktur Folder

```
estate-prima/  (root project, sesuaikan nama folder dengan punya kamu)
├── config/
│   └── database.php          # koneksi mysqli + konstanta BASE_URL
├── includes/
│   └── auth.php               # cek_login(), cek_admin(), user_login()
├── database/
│   ├── schema.sql              # skema lengkap + seed data (import sekali di awal)
│   └── tambahan-tahap6.sql     # ALTER/CREATE tambahan kalau schema.sql sudah pernah diimport duluan
├── pages/
│   ├── user/
│   │   ├── dashboard-user.php
│   │   ├── wishlist.php
│   │   └── pesanan.php
│   └── admin/
│       ├── admin-dashboard.php
│       ├── admin-properti.php        + properti-tambah.php, properti-edit.php, proses-properti.php
│       ├── properti-galeri.php       + proses-galeri.php
│       ├── admin-transaksi.php       + admin-transaksi-detail.php, proses-transaksi-admin.php
│       ├── admin-agen.php            + agen-tambah.php, agen-edit.php, proses-agen.php
│       └── admin-pesan.php           + proses-pesan.php
├── index.php                  # homepage
├── listing.php                # semua properti + search/filter/pagination
├── detail.php                 # detail 1 properti + wishlist & ajukan beli
├── kontak.php                 # form kontak
├── login.php / register.php / logout.php
├── proses-wishlist.php        # toggle wishlist (dipanggil dari detail.php)
├── proses-transaksi.php       # ajukan beli (dipanggil dari detail.php)
└── test-koneksi.php           # cek koneksi DB, HAPUS sebelum deploy/kumpul
```

---

## 3. Skema Database (7 tabel)

1. **users** — `role` ENUM('user','admin') membedakan user biasa & admin dalam satu tabel.
2. **agen** — data agen sales, ditampilkan sebagai kartu di halaman detail properti.
3. **properti** — data utama properti (harga, lokasi, `lat`/`lng` buat Maps, spesifikasi, `status` tersedia/terjual).
4. **galeri_properti** — foto tambahan per properti (1 properti banyak foto).
5. **wishlist** — relasi user ↔ properti, `UNIQUE KEY` biar gak bisa wishlist dobel.
6. **transaksi** — pengajuan beli, `status` ENUM('menunggu','diproses','disetujui','ditolak','selesai').
7. **pesan_kontak** — pesan dari form kontak (Tahap 6).

**Akun testing** (password sama-sama `password123`):
- Admin: `admin@estateprima.test`
- User: `budi@mail.test`

---

## 4. Progress per Tahap

### ✅ Tahap 1 — Setup & Struktur
- Struktur folder awal
- `database/schema.sql` (7 tabel + seed data)
- `config/database.php` (koneksi mysqli)

### ✅ Tahap 2 — Auth
- `register.php` — validasi lengkap + cek email duplikat + hash password
- `login.php` — `password_verify()`, redirect beda tujuan tergantung `role`
- `logout.php` — destroy session
- `includes/auth.php` — `cek_login()`, `cek_admin()`, `user_login()`

### ✅ Tahap 3 — Fitur Publik
- `index.php` — statistik + 6 properti terbaru
- `listing.php` — search (judul/alamat/kota) + filter (tipe/kota/rentang harga) + pagination, WHERE dibangun dinamis tapi tetap prepared statement
- `detail.php` — detail properti + galeri + kartu agen + Google Maps embed
- `proses-wishlist.php` — toggle wishlist (ada → hapus, belum ada → tambah)
- `proses-transaksi.php` — ajukan beli, ada validasi cegah pengajuan dobel untuk properti yang sama

### ✅ Tahap 4 — Dashboard User
- `pages/user/dashboard-user.php` — ringkasan wishlist + transaksi per status
- `pages/user/wishlist.php` — daftar lengkap wishlist, bisa hapus langsung
- `pages/user/pesanan.php` — daftar transaksi dengan label & warna status

### ✅ Tahap 5 — CRUD Admin (Properti & Transaksi)
- `pages/admin/admin-dashboard.php` — statistik keseluruhan sistem
- `pages/admin/admin-properti.php` + `properti-tambah.php` + `properti-edit.php` + `proses-properti.php` — CRUD properti lengkap
- `pages/admin/admin-transaksi.php` + `admin-transaksi-detail.php` + `proses-transaksi-admin.php` — kelola transaksi, filter status
  - **Business logic penting**: status transaksi diubah ke `disetujui`/`selesai` → properti otomatis `terjual`; ke `ditolak` → properti balik `tersedia`

### ✅ Tahap 6 — Kontak, Galeri, Kelola Agen
- `kontak.php` + `pages/admin/admin-pesan.php` + `proses-pesan.php` — form kontak tersimpan ke DB, admin bisa lihat/tandai dibaca/hapus
- `pages/admin/properti-galeri.php` + `proses-galeri.php` — tambah/hapus foto galeri per properti
- `pages/admin/admin-agen.php` + `agen-tambah.php` + `agen-edit.php` + `proses-agen.php` — CRUD agen lengkap, hapus agen otomatis set properti terkait jadi "Tanpa Agen" (`ON DELETE SET NULL`)
- `database/tambahan-tahap6.sql` — `CREATE TABLE pesan_kontak`

**Status: Backend 100% selesai.** Semua 7 tabel punya CRUD/fungsi yang jalan, semua 12 halaman di README mockup punya logic PHP di baliknya.

### ⏳ Tahap 7 — Integrasi Tampilan (BELUM DIMULAI)
Rencana: ambil markup HTML/Bootstrap dari file mockup asli (`index.html`, `listing.html`, dst), lalu suntik logic PHP yang sudah dibuat ke dalamnya satu halaman per satu halaman. Logic backend tidak perlu diubah, cuma bagian tampilan (`<body>` polos → markup Bootstrap asli).

**Yang dibutuhkan buat mulai**: file mockup asli (HTML+CSS+JS) di-upload ke chat, karena sejauh ini yang di-upload baru `README.md`-nya saja.

---

## 5. Catatan Bug yang Pernah Ditemukan (biar gak keulang)

1. **Hash password seed data awal salah** — jangan pernah karang string hash bcrypt manual, selalu generate pakai `password_hash()` beneran (atau `php -r "echo password_hash('xxx', PASSWORD_DEFAULT);"` di terminal).
2. **`mysqli_stmt_bind_param()` jumlah tipe data harus PAS** sama jumlah variabel yang di-bind — pernah kurang 1 huruf `s` di `proses-properti.php` bagian UPDATE. Kalau nambah/ubah field di query, selalu hitung ulang.
3. **Path absolut (`/login.php`) vs relatif** — karena project ada di subfolder (bukan root domain), semua redirect pakai `BASE_URL` (buat lintas kedalaman folder) atau path relatif biasa (buat file yang sejajar).

---

## 6. Cara Lanjutin dari Sini

1. Kalau mulai chat baru, **upload file ini (`PROGRESS.md`) di awal** biar konteks langsung nyambung.
2. Kalau mau lanjut ke Tahap 7 (tampilan), siapkan & upload semua file mockup asli (HTML/CSS/JS) — bisa di-zip jadi satu.
3. Semua source code PHP yang sudah jadi ada di riwayat chat sebelumnya (sudah didownload satu-satu) — pastikan sudah tersalin semua ke project lokal kamu di Laragon.