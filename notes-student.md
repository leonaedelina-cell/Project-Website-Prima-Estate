# Catatan untuk Student — Estate Prima

Ini catatan hasil review kode di branch `main` (progress terbaru kamu). Ditulis biar kamu tau bagian mana yang udah bagus dan bagian mana yang masih perlu dikerjain. Bukan buat bikin down — beberapa hal di sini di atas rata-rata anak seangkatan.

**Update:** mentor udah bikinin sebagian fix/fitur yang tercatat di sini secara langsung di kode, tapi ditaro di branch terpisah (`mentor-fixes`), **bukan** di `main` kamu. Jadi `main` kamu masih aman, gak keubah. Detail lengkap ada di `fix-stuff.md` — baca itu buat liat persis apa yang mentor tambahin dan gimana caranya, biar kamu bisa belajar dari situ juga.

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

## Yang tadinya "belum" — SEKARANG UDAH DIKERJAIN mentor (lihat `fix-stuff.md`)

Semua ini statusnya **selesai di branch `mentor-fixes`**, belum masuk `main` kamu. Baca `fix-stuff.md` buat detail per file:

- [x] `tipe_transaksi` (jual/sewa) + `durasi_minimal` — sekarang ada di schema, form tambah/edit properti, halaman detail (badge + tombol "Ajukan Sewa"/"Ajukan Beli"), pesan WhatsApp, dan filter di `listing.php`.
- [x] Kolom `fasilitas` + `status_hunian` — udah ditambah ke schema & form, ditampilkan di halaman detail.
- [x] `test-koneksi.php` — udah dihapus.
- [x] 3 sisa `confirm()` browser (`admin-agen.php`, `admin-pesan.php`, `properti-galeri.php`) — udah diganti modal Bootstrap.
- [x] `session_regenerate_id()` di `register.php` — udah ditambah, konsisten sama `login.php`.
- [x] Footer dashboard admin & user — udah dihapus (halaman dashboard sekarang pakai `includes/dashboard-footer.php`, tanpa `<footer>`, footer situs cuma dipakai halaman publik).
- [x] Hapus wishlist di dashboard user — sekarang AJAX (`fetch()`), gak reload halaman, card langsung hilang dari tampilan.
- [x] Kelola user admin — sekarang bisa tambah user baru (`admin-user-tambah.php`) dan edit data akun user lain (`admin-user-edit.php`), gak cuma ubah role/hapus doang.
- [x] Upload gambar via file — properti (`properti-tambah.php`/`properti-edit.php`) dan bukti pembayaran (`admin-transaksi-detail.php`) sekarang bisa upload file, bukan link doang. File lama otomatis kehapus pas diganti (contek pola yang kamu buat duluan di foto agen).
- [x] Nomor urut tabel — udah ada di 4 tabel admin (properti, transaksi, agen, users), gak reset pas ganti halaman.
- [x] Pagination — udah ada di `admin-transaksi.php`, `admin-agen.php`, `admin-users.php` (nyusul `admin-properti.php` yang emang udah punya duluan).
- [x] Search per-tabel — udah ada di 4 tabel admin, digabung sama pagination lewat query string.
- [x] Icon per status di statistik transaksi (`admin-dashboard.php`) — optional tapi udah ditambahin sekalian karena murah.
- [x] Tampilan tabel `admin-users.php` dirapihin total — sebelumnya satu baris HTML panjang, sekarang struktur `<table>` sama rapinya kayak tabel admin lain.
- [x] Sidebar admin & user sekarang bisa di-collapse/expand pakai tombol toggle (ikon `<<`), state disimpan di `localStorage`.
- [x] Cetak bukti transaksi (PDF/print) buat transaksi yang statusnya `selesai`, di `admin-transaksi-detail.php`.
- [x] Breadcrumb di halaman admin diganti jadi deskripsi singkat "halaman ini bisa ngapain" (halaman publik tetep pakai breadcrumb, karena emang butuh nav berlapis).
- [x] Fix bug: hover di header tabel `admin-properti.php` bikin teksnya invisible (CSS hover-nya kena baris header, bukan cuma baris data).
- [x] Fix bug: subjudul (`<p class="lead">`) di semua halaman admin gak kebaca di atas background navy gelap.
- [x] Semua CSS/JS yang tadinya nyampur di dalam file PHP (15 `<style>`, 4 `<script>`) dipisah ke `assets/css/style.css` dan `assets/js/*.js`, masing-masing dikasih komentar. Ketauan juga 4 halaman punya style `.table-estate` yang diulang-ulang, sekarang digabung jadi satu.
- [x] User juga bisa cetak bukti transaksi sendiri di `pesanan.php` (tombol muncul kalau status `selesai`), bukan cuma admin.

**Kalau mau lihat kode CSS/JS-nya:** `assets/css/style.css` (tiap bagian dikasih komentar `/* ---------- Halaman xxx.php ---------- */`) dan folder `assets/js/` (tiap file `.js` ada komentar header jelasin dipakai di halaman mana & buat apa).

## Yang masih perlu kamu kerjain sendiri

1. Cek ulang semua halaman admin/user di browser buat mastiin tampilan gak ada yang kepotong/rusak — terutama form yang barusan ditambah field baru (properti, transaksi-detail, users).
2. Kalau mau, rapikan CSS icon di stat card statistik transaksi (`admin-dashboard.php`) — sekarang fungsional tapi belum ada warna/size khusus per status, masih polos.
3. **Halaman "Cara Kerja" (baru, belum ada sama sekali)** — buat halaman publik yang jelasin proses beli/sewa properti step-by-step buat guest/customer. Beberapa keputusan yang udah didiskusiin, tinggal eksekusi:
   - **Halaman terpisah**, jangan digabung ke `listing.php` atau `kontak.php` — konteksnya beda (edukasi proses, bukan belanja atau kontak).
   - **Nama di navbar**: `Cara Kerja` (pendek, jelas — gaya sama kayak "Cara Beli" di situs properti lain). Alternatif: `Panduan`.
   - **FAQ** ditaro sebagai section DI DALAM halaman ini (accordion Bootstrap misalnya), bukan menu navbar terpisah — biar navbar gak kepanjangan.
   - **Tombol "Hubungi Tim Sales"** — arahin ke `wa.me` pakai constant `WHATSAPP_ADMIN` yang udah ada di `config/database.php`, sama pola kayak yang dipakai di `proses-transaksi.php` (pesan default di-`urlencode()`).
   - **Carousel foto agen** (auto-slide) — pakai Bootstrap Carousel (`data-bs-ride="carousel"`), library-nya udah ke-load di project, gak perlu tambahan. Bisa ditaro di halaman ini atau di `index.php` (homepage) biar agen keliatan dari awal — pilih salah satu, jangan dobel.
4. Lanjut checklist test manual di bagian bawah.

## Catatan struktur

Kode kamu di `main` pakai struktur flat di root (`admin-agen.php`, `proses-*.php`, dst) — ini yang aktif dan yang bakal dinilai. Sempat ada percobaan refactor ke folder `admin/`, `user/`, `actions/` di branch lain (`vanya`), tapi itu belum masuk ke `main` kamu, jadi abaikan aja kalau lihat referensi itu di tempat lain.

## Checklist cepat sebelum submit final

- [x] Tambah `tipe_transaksi` + alur sewa — *selesai di `mentor-fixes`, lihat `fix-stuff.md`*
- [x] Hapus `test-koneksi.php` — *selesai di `mentor-fixes`*
- [x] Ganti sisa `confirm()` jadi modal Bootstrap — *selesai di `mentor-fixes`*
- [x] Tambah kolom `fasilitas` / `status_hunian` — *selesai di `mentor-fixes`*
- [x] Buang footer dari layout dashboard admin & user — *selesai di `mentor-fixes`*
- [x] Hapus wishlist pakai AJAX, gak reload halaman — *selesai di `mentor-fixes`*
- [x] Tambah form tambah user + edit profil user di panel admin — *selesai di `mentor-fixes`*
- [x] Upload gambar properti & bukti bayar pakai file, bukan cuma link — *selesai di `mentor-fixes`*
- [x] Tambah kolom nomor urut di semua tabel (properti, transaksi, agen, users) — *selesai di `mentor-fixes`*
- [x] Tambah pagination di `admin-transaksi.php`, `admin-agen.php`, `admin-users.php` — *selesai di `mentor-fixes`*
- [x] Tambah search per-tabel di 4 tabel admin — *selesai di `mentor-fixes`*
- [x] Icon di statistik transaksi — *selesai di `mentor-fixes` (optional, tapi udah dikerjain)*
- [x] Sidebar bisa di-collapse/expand pakai toggle `<<` — *selesai di `mentor-fixes`*
- [x] Cetak bukti transaksi buat status `selesai` — *selesai di `mentor-fixes`*
- [ ] Test manual: guest gak bisa wishlist/ajukan (harus keredirect login), user cuma bisa liat pesanan sendiri
