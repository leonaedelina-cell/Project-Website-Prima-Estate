# Mockup — Estate Prima

Mockup tampilan statis (HTML + CSS + dikit JS dari Bootstrap), **gak ada PHP/database beneran** — cuma buat liat gambaran akhir UI/UX-nya sebelum mulai coding project.

## Cara Buka

Paling gampang pake ekstensi **Live Server** di VS Code:
1. Buka folder `mockup/` di VS Code.
2. Klik kanan `index.html` → **Open with Live Server**.
3. Browser kebuka otomatis, tinggal klik-klik navigasi antar halaman.

Kalau gak ada Live Server, buka `index.html` langsung 2x klik juga bisa (semua file di sini statis, gak butuh server PHP) — cuma gak auto-reload kalau diedit.

## Daftar Halaman

| File | Buat apa |
|---|---|
| `index.html` | Homepage — hero + search + statistik + properti terbaru |
| `listing.html` | Semua properti — search + filter + pagination |
| `detail.html` | Detail 1 properti — galeri, fasilitas, tombol Wishlist & Ajukan Beli |
| `login.html` / `register.html` | Form auth, split-screen |
| `kontak.html` | Halaman contact sales |
| `dashboard-user.html` | Dashboard user — stat wishlist/pesanan, sidebar |
| `wishlist.html` | Daftar lengkap wishlist user |
| `pesanan.html` | Status transaksi milik user |
| `admin-dashboard.html` | Dashboard admin — statistik keseluruhan |
| `admin-properti.html` | Kelola properti — tabel + modal konfirmasi hapus |
| `admin-transaksi.html` | List semua transaksi, filter status |
| `admin-transaksi-detail.html` | Form admin update metode + status + bukti bayar |

## Catatan

- Semua data (nama properti, harga, user) di mockup ini **dummy**, ngambang gitu aja, gak nyambung ke database manapun.
- Tombol-tombol (Login, Simpan, dll) kebanyakan `href="#"` atau nyambung ke halaman mockup lain doang — bukan fungsional beneran.
- Warna & komponen di sini (navy `#16324f` + gold `#c9a24b`) cuma referensi visual, **bebas diubah** pas ngerjain project aslinya — yang penting struktur halaman & fitur-fiturnya ke-cover.
- Gambar properti pake link Unsplash (foto rumah publik) — pas ngerjain project asli, boleh pake link gambar sendiri via kolom `gambar_url` (pola sama kayak Sesi 7).
- Sengaja dibikin **beda pola layout** dari mockup toko buku (`8-Bootstrap-Integration`) biar terasa kayak website properti beneran: hero pake foto rumah full-bleed (bukan gradient polos), `listing.html` pake filter sidebar kiri (bukan search bar di atas), ada kartu agen sales di halaman detail, dan Google Maps embed di halaman Kontak + detail properti.
- **Google Maps embed** (`detail.html` & `kontak.html`) pake format link gratis, **gak butuh API key**:
  ```html
  <iframe src="https://www.google.com/maps?q=ALAMAT_DI_SINI&output=embed" style="border:0;" allowfullscreen loading="lazy"></iframe>
  ```
  Tinggal ganti `q=` dengan alamat (spasi jadi `+`) atau koordinat lat,long. Ini bukan Maps JavaScript API (yang butuh API key & billing) — cuma embed link biasa, cukup buat nunjukin lokasi statis.
