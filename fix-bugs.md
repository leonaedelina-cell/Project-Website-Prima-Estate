# Catatan Perbaikan Bug — Estate Prima

Dokumen ini menjelaskan masalah yang ditemukan pada submission dan statusnya.

## Perbaikan URL dan path yang sudah dilakukan

- `BASE_URL` tidak lagi menunjuk ke folder proyek lama. Nilainya sekarang dibuat otomatis dari URL yang sedang dibuka, jadi aplikasi dapat dipakai dari `http://localhost/Project-Website-Prima-Estate/` tanpa mengubah konfigurasi setiap kali nama folder berubah.
- Tautan navbar untuk dashboard user dan admin sekarang menunjuk ke file yang benar di root proyek: `dashboard-user.php` dan `admin-dashboard.php`.
- Redirect admin setelah login juga menunjuk ke `admin-dashboard.php` di root proyek.
- Semua halaman user/admin dan handler yang berada di root sekarang memuat `config/database.php` serta `includes/auth.php` dari root. Sebelumnya file-file itu masih memakai `../../`, seolah-olah berada di folder `pages/user` atau `pages/admin` yang tidak ada.
- `logout.php` sekarang memuat konfigurasi database agar `BASE_URL` tersedia saat redirect.

## Cara membuka aplikasi

1. Nyalakan Apache dan MySQL melalui Laragon.
2. Import `setup.sql` melalui phpMyAdmin. File ini membuat database `estate_prima` dan data contoh.
3. Buka `http://localhost/Project-Website-Prima-Estate/index.php`.

Jika folder proyek diganti namanya, URL otomatis mengikuti nama folder baru.

## Bug yang masih perlu diperbaiki

### Prioritas tinggi

- `login.php` dan `register.php` hanya memproses POST; pada akses GET kedua halaman tidak merender form sehingga tampil kosong.
- `admin-transaksi-detail.php` kosong, padahal tabel transaksi menaut ke halaman tersebut untuk mengubah status pembayaran.
- `admin-properti.php` berisi duplikat dashboard admin, bukan halaman daftar properti.
- `proses-agen.php` berisi markup halaman properti, bukan handler tambah/edit/hapus agen; form agen belum bisa bekerja.
- Link menuju `properti-edit.php` rusak karena file yang tersedia bernama `pproperti-edit.php` (ada huruf `p` tambahan).

### Ketidaksesuaian spesifikasi

- Skema di README berbeda dari kolom yang digunakan PHP. `setup.sql` dibuat sebagai **skema kompatibilitas kode**, bukan skema empat tabel README.
- README meminta redirect WhatsApp setelah pengajuan pembelian. `proses-transaksi.php` saat ini hanya menyimpan transaksi lalu kembali ke detail properti.
- README meminta halaman kelola user/role dan profil, tetapi file tersebut belum ada.
- README dan mockup meminta modal Bootstrap untuk hapus; beberapa halaman masih menggunakan `confirm()` browser.

### Keamanan dan validasi

- Form yang mengubah data belum memakai token CSRF.
- Pengajuan transaksi belum memverifikasi ulang apakah properti ada dan masih berstatus `tersedia` sebelum insert.
- Hapus foto galeri belum memastikan foto yang dihapus benar-benar milik properti pada form.

## Akun data contoh

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@estateprima.test` | `password` |
| User | `pelanggan@estateprima.test` | `password` |

> Akun ini hanya untuk data pengembangan/lokal. Ganti password sebelum proyek dipakai di lingkungan nyata.
