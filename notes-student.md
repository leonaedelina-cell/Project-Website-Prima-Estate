# Catatan untuk Student — Estate Prima

Ini catatan hasil review kode di branch `main` (progress terbaru kamu). Ditulis biar kamu tau bagian mana yang udah bagus dan bagian mana yang masih perlu dikerjain. Bukan buat bikin down — beberapa hal di sini di atas rata-rata anak seangkatan.

## Cara login buat testing

Akun demo dari `setup.sql`:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@estateprima.test` | `password` |
| User | `pelanggan@estateprima.test` | `password` |

## Yang udah bagus (progress dari commit terakhir)

- **CSRF token udah ada di semua form POST** (`csrf_token()` + `cek_csrf()` di `includes/auth.php`). Ini konsep yang gak diajarin eksplisit tapi kamu implementasi dengan benar — token disimpan di session, dicek pakai `hash_equals()` (bukan `==`, ini penting biar gak kena timing attack).
- **`session_regenerate_id(true)` setelah login** — mencegah session fixation. Bagus, sering kelewat.
- **Cookie session di-harden**: `httponly`, `samesite`, `secure` kalau HTTPS.
- **`admin-users.php` + `proses-users.php`** — kelola role user udah jalan, dan ada proteksi gak bisa hapus/demote akun sendiri.
- **`profil.php`** udah dibikin.
- **Redirect WhatsApp** di `proses-transaksi.php` udah jalan pakai `urlencode()`, sesuai spec.
- **`admin-transaksi-detail.php`** udah jadi form beneran (sebelumnya cuma placeholder kosong). Pakai `mysqli_begin_transaction` + `FOR UPDATE` buat lock baris pas approve/reject transaksi — ini mencegah race condition kalau ada 2 admin approve bareng. Solid.
- Prepared statement dipakai konsisten, `htmlspecialchars()` juga konsisten di semua output.

## Yang masih perlu dikerjain (urutan prioritas)

1. **`tipe_transaksi` (jual/sewa) belum ada.** Ini fitur inti di spec soal — kolom `tipe_transaksi` dan `durasi_minimal` gak ada di `properti`, jadi alur "Ajukan Sewa" belum bisa jalan sama sekali. Ini yang paling penting buat dikejar.
2. **Hapus `test-koneksi.php`.** File ini publik, siapa aja bisa akses dan lihat nama tabel + jumlah baris di database. Komentar di file itu sendiri udah bilang "hapus sebelum submit" — sekarang saatnya beneran dihapus.
3. **Kolom `fasilitas` dan `status_hunian` belum ada** di tabel `properti` sesuai spec.
4. **3 tempat masih pakai `confirm()` browser** (`admin-agen.php`, `admin-pesan.php`, `properti-galeri.php`) padahal tempat lain (`admin-users.php`) udah pakai modal Bootstrap yang lebih rapi. Tinggal disamain aja pattern-nya.
5. Kecil: `register.php` gak panggil `session_regenerate_id()` pas auto-login setelah daftar (di `login.php` udah bener). Bukan bug besar, tapi biar konsisten.

## Catatan struktur

Kode kamu di `main` pakai struktur flat di root (`admin-agen.php`, `proses-*.php`, dst) — ini yang aktif dan yang bakal dinilai. Sempat ada percobaan refactor ke folder `admin/`, `user/`, `actions/` di branch lain (`vanya`), tapi itu belum masuk ke `main` kamu, jadi abaikan aja kalau lihat referensi itu di tempat lain.

## Request tambahan dari mentor

Di luar hasil review kode, ini permintaan perbaikan/fitur tambahan:

1. **Sidebar/halaman dashboard admin & user gak perlu footer lagi.** Footer cukup buat halaman publik aja.
2. **Hapus wishlist di halaman user dashboard jangan pindah halaman.** Sekarang kemungkinan submit form biasa yang reload/redirect. Ganti ke AJAX (`fetch()`) — pas tombol hapus diklik, request ke `proses-wishlist.php` di background, kalau sukses baru elemen card-nya dihapus dari DOM pakai JS. User tetep di halaman yang sama, cuma card wishlist-nya yang hilang.
3. **(Optional, gak wajib)** Statistik transaksi bisa ditambah icon/logo per jenis aktivitas (misal icon beda buat "menunggu", "diproses", "selesai", dll) biar lebih enak dibaca. Bootstrap Icons yang udah dipakai di tempat lain bisa dipake juga di sini.
4. **Kelola user admin belum lengkap**: sekarang baru bisa ubah role & hapus user, belum bisa:
   - Tambah user baru dari panel admin.
   - Edit profil/data akun user lain (nama, email, no HP) dari panel admin.
   Perlu ditambah form tambah user (mirip `register.php` tapi admin yang isi + bisa pilih role langsung) dan form edit data user per akun.
5. **Upload gambar masih link doang di beberapa tempat, belum bisa upload file.** Update: foto agen (`proses-agen.php`) ternyata **udah bisa** upload file — pola-nya udah bagus (validasi ukuran max 400KB, cek tipe file pakai `getimagesize()`, nama file di-random, foto lama otomatis kehapus pas ganti). Tinggal **copy pola yang sama** ke dua tempat ini yang masih link doang:
   - Gambar properti (form tambah/edit properti).
   - Bukti pembayaran/transaksi (`admin-transaksi-detail.php`).
   Simpan ke folder kayak `assets/uploads/properti/` dan `assets/uploads/bukti-bayar/`, path-nya yang disimpan ke database (bukan link eksternal).
6. **Semua tabel (admin & user) belum ada nomor urut.** Tabel di `admin-properti.php`, `admin-transaksi.php`, `admin-agen.php`, `admin-users.php` semua belum punya kolom "No" di paling kiri. Tambahin kolom nomor urut biar gampang direferensiin pas ngobrol/laporan (misal "data nomor 5"). Kalau tabelnya pakai pagination, nomornya jangan reset ke 1 tiap ganti halaman — lanjutin dari halaman sebelumnya (contoh: halaman 2 mulai dari nomor 11, bukan 1 lagi).
7. **Pagination belum ada di semua tabel admin & user.** `admin-properti.php` udah punya pagination (`page`, `LIMIT`/`OFFSET`), pola ini yang harus dicontek. Yang masih belum ada pagination:
   - `admin-transaksi.php`
   - `admin-agen.php`
   - `admin-users.php`
   Kalau daftar user/agen/transaksi makin banyak, halaman bakal berat kalau semua data ditampilin sekaligus tanpa pagination.
8. **Search belum ada di tabel admin.** Tambahin fitur cari di 4 tabel: `admin-properti.php`, `admin-transaksi.php`, `admin-agen.php`, `admin-users.php`. **Pakai pola per-tabel** (bukan search universal/gabungan) — contek persis pola yang udah ada di `listing.php` (`?q=` + `LIKE` + prepared statement), digabung sama pagination biar `?q=...&page=2` gak saling ilangin (sama kayak yang udah jalan di `listing.php`). Kolom yang di-search beda tiap tabel, contoh:
   - Properti: judul, kota
   - Transaksi: nama customer, judul properti
   - Agen: nama, email
   - Users: nama, email

   Kenapa bukan universal search (satu search box nyari lintas semua tabel)? Karena tiap tabel struktur & kolomnya beda-beda, hasilnya bakal susah ditampilin rapi dalam satu daftar, query-nya jadi lebih ribet (union/banyak query sekaligus), dan gak match sama cara admin biasa pakai — admin biasanya emang lagi buka halaman tabel tertentu pas nyari data, bukan nyari acak dari mana-mana. Universal search itu pola buat command-palette di SaaS gede, bukan buat admin panel skala kecil kayak ini.

## Checklist cepat sebelum submit final

- [ ] Tambah `tipe_transaksi` + alur sewa
- [ ] Hapus `test-koneksi.php`
- [ ] Ganti sisa `confirm()` jadi modal Bootstrap
- [ ] Tambah kolom `fasilitas` / `status_hunian` kalau mau ngikutin spec persis
- [ ] Buang footer dari layout dashboard admin & user
- [ ] Hapus wishlist pakai AJAX, gak reload halaman
- [ ] Tambah form tambah user + edit profil user di panel admin
- [ ] Upload gambar properti & bukti bayar pakai file, bukan cuma link (contek pola foto agen)
- [ ] Tambah kolom nomor urut di semua tabel (properti, transaksi, agen, users)
- [ ] Tambah pagination di `admin-transaksi.php`, `admin-agen.php`, `admin-users.php` (contek pola `admin-properti.php`)
- [ ] Tambah search per-tabel di 4 tabel admin (contek pola `?q=` di `listing.php`)
- [ ] (Optional) icon/logo di statistik transaksi
- [ ] Test manual: guest gak bisa wishlist/ajukan (harus keredirect login), user cuma bisa liat pesanan sendiri
