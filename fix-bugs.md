# Catatan Perbaikan — Estate Prima

## Yang sudah diperbaiki

Ini yang sudah dibetulkan supaya URL project tidak lagi `Not Found`:

- URL project tidak lagi memakai path proyek lama. `BASE_URL` sekarang otomatis mengikuti nama folder project.
- Link dashboard di navbar sudah diarahkan ke file yang benar.
- Redirect admin setelah login sudah diarahkan ke file dashboard yang benar.
- Path file admin dan user sudah dibetulkan. Sebelumnya path tersebut mengarah ke folder `pages/` yang sebenarnya tidak ada.
- `logout.php` sudah bisa memakai `BASE_URL` dengan benar.
- Ditambahkan `setup.sql` untuk membuat database `estate_prima` beserta data contoh.

Untuk membuka project di Laragon:

`http://localhost/Project-Website-Prima-Estate/index.php`

## Yang masih harus dikerjakan murid

- `login.php` dan `register.php` belum punya tampilan form. Saat dibuka halaman masih kosong.
- `admin-transaksi-detail.php` masih kosong. Admin belum bisa update detail transaksi.
- `admin-properti.php` isinya salah; seharusnya halaman kelola properti, bukan dashboard admin.
- `proses-agen.php` belum menjadi handler agen. Form tambah, edit, dan hapus agen belum bisa bekerja.
- File edit properti salah nama: ada `pproperti-edit.php`, tetapi link memakai `properti-edit.php`.
- Pengajuan transaksi belum redirect ke WhatsApp seperti yang diminta di README.
- Halaman profil user dan kelola user/role admin belum ada.
- Beberapa tombol hapus masih memakai pop-up browser `confirm()`, belum memakai modal Bootstrap seperti mockup.

## Cara test setelah import database

1. Import `setup.sql` di phpMyAdmin.
2. Buka URL project di atas.
3. Coba halaman Beranda, Properti, Detail Properti, dan Kontak.
4. Login memakai akun berikut:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@estateprima.test` | `password` |
| User | `pelanggan@estateprima.test` | `password` |

> `setup.sql` dibuat supaya cocok dengan kode PHP yang ada sekarang. Struktur databasenya berbeda dari contoh awal di README.
