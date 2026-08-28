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
5. **Upload gambar masih link doang, belum bisa upload file.** Berlaku buat dua tempat:
   - Gambar properti (sekarang cuma isi URL di form tambah/edit properti).
   - Bukti pembayaran/transaksi (sekarang juga cuma isi URL di `admin-transaksi-detail.php`).
   Perlu ditambah input `type="file"`, handle pakai `$_FILES` + `move_uploaded_file()`, simpan ke folder kayak `uploads/properti/` dan `uploads/bukti-bayar/`, terus path-nya yang disimpan ke database (bukan link eksternal). Validasi tipe file (jpg/png) dan ukuran max juga perlu ditambahin biar aman.

## Checklist cepat sebelum submit final

- [ ] Tambah `tipe_transaksi` + alur sewa
- [ ] Hapus `test-koneksi.php`
- [ ] Ganti sisa `confirm()` jadi modal Bootstrap
- [ ] Tambah kolom `fasilitas` / `status_hunian` kalau mau ngikutin spec persis
- [ ] Buang footer dari layout dashboard admin & user
- [ ] Hapus wishlist pakai AJAX, gak reload halaman
- [ ] Tambah form tambah user + edit profil user di panel admin
- [ ] Upload gambar properti & bukti bayar pakai file, bukan cuma link
- [ ] (Optional) icon/logo di statistik transaksi
- [ ] Test manual: guest gak bisa wishlist/ajukan (harus keredirect login), user cuma bisa liat pesanan sendiri
