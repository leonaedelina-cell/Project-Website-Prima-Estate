# fix-stuff.md — Apa yang Mentor Tambahin/Perbaiki

Dokumen ini isinya daftar lengkap perubahan yang mentor bikin di branch **`mentor-fixes`** (dibikin dari `main`, jadi `main` kamu gak keubah sama sekali). Tiap poin dikasih file yang berubah + kenapa, biar kamu bisa belajar dari implementasinya, bukan cuma dikasih kode jadi.

Basis branch ini: `main` commit `3e7513b` ("semi finish").

---

## 1. Tipe Transaksi (Jual/Sewa) + Durasi Minimal

Ini fitur inti yang belum ada sebelumnya — sesuai spec soal ujian.

- `setup.sql`: tabel `properti` nambah kolom `tipe_transaksi ENUM('jual','sewa') DEFAULT 'jual'` dan `durasi_minimal ENUM('6 bulan','1 tahun') NULL`. Ada beberapa `UPDATE` di akhir file buat jadiin beberapa properti contoh jadi status sewa, biar ada data buat testing.
- `properti-tambah.php` / `properti-edit.php`: form nambah dropdown "Tipe Transaksi", dan field "Durasi Minimal Sewa" yang otomatis muncul/ilang pakai JS tergantung pilihan tipe transaksi (`display:none` toggle).
- `proses-properti.php`: validasi `tipe_transaksi` harus `jual`/`sewa`; kalau `jual`, `durasi_minimal` dipaksa `NULL` (biar data gak nyampah).
- `detail.php`: badge "Dijual"/"Disewakan" di halaman detail, tombol berubah jadi "Ajukan Sewa" kalau propertinya tipe sewa.
- `proses-transaksi.php`: pesan WhatsApp otomatis nyesuain kata "membeli"/"menyewa" sesuai tipe transaksi propertinya.
- `listing.php`: filter tambahan "Jual/Sewa" di form pencarian, badge di card properti juga nunjukin jual/sewa.

## 2. Kolom `fasilitas` dan `status_hunian`

- `setup.sql`: tabel `properti` nambah `fasilitas TEXT NULL` (isi dipisah koma) dan `status_hunian ENUM('kosong','terisi') DEFAULT 'kosong'`.
- `properti-tambah.php` / `properti-edit.php`: form nambah input teks fasilitas + dropdown status hunian.
- `detail.php`: fasilitas di-parse dari string koma (`explode(',', ...)`) terus ditampilin sebagai badge-badge kecil.

## 3. Upload Gambar via File (bukan link doang)

Kamu udah bikin pola upload yang bagus di `proses-agen.php` (foto agen) — mentor cuma copy pola yang sama ke 2 tempat lain:

- **Gambar properti** (`proses-properti.php`, form di `properti-tambah.php`/`properti-edit.php`): input `type="file"` + tetep ada opsi isi URL manual (kalau gak upload file, isi gambar_url biasa). Validasi: maks 2MB, format JPG/PNG/WEBP dicek pakai `getimagesize()` (bukan cuma cek ekstensi nama file — ekstensi bisa dipalsuin, isi filenya yang beneran dicek). Nama file di-random pakai `bin2hex(random_bytes(16))` biar gak collision/ketebak. Disimpan ke `assets/uploads/properti/`. Pas edit dan upload gambar baru, gambar lama otomatis kehapus (`unlink()`) — tapi cuma kalau gambar lama itu emang file upload kita sendiri (dicek lewat `strpos($path, '/assets/uploads/properti/')`), bukan link eksternal, biar gak salah hapus.
- **Bukti pembayaran** (`admin-transaksi-detail.php`): sama persis polanya, disimpan ke `assets/uploads/bukti-bayar/`.
- Folder `assets/uploads/` ditambahin ke `.gitignore` (isinya, bukan foldernya — ada `.gitkeep` biar folder tetep ke-track git tapi isi upload gak ikut ke-commit).

## 4. Kelola User Lebih Lengkap

Sebelumnya admin cuma bisa ubah role & hapus user. Sekarang:

- `admin-user-tambah.php` (baru): form buat admin bikin akun baru + langsung pilih role (mirip `register.php` tapi role-nya bisa dipilih admin).
- `admin-user-edit.php` (baru): form edit nama/email/no HP **+ role** user lain, semua dari 1 form (revisi — awalnya role dipisah lewat tombol quick-toggle "Jadikan Admin/User" di `admin-users.php`, sekarang digabung ke form edit sesuai permintaan, tombol quick-togglenya dihapus).
- `proses-users.php`: aksi `edit` sekarang handle nama/email/no_hp/role sekaligus. Tetep ada proteksi: kalau admin edit akun sendiri, field role di-skip (dipaksa tetep nilai lama) biar gak ada yang gak sengaja demote diri sendiri sampai kekunci dari panel admin.
- `admin-users.php`: baris "Akun Anda" (buat baris akun yang lagi login) sekarang badge pill kecil (`<span class="badge rounded-pill ...">`), bukan teks polos lagi.

## 5. Nomor Urut, Pagination, Search — 4 Tabel Admin

`admin-properti.php` udah punya pagination duluan (nice), tapi belum ada nomor urut & search. 3 tabel lain (`admin-transaksi.php`, `admin-agen.php`, `admin-users.php`) belum ada pagination/search sama sekali. Sekarang keempatnya konsisten:

- **Nomor urut**: variabel `$nomor = $offset + 1;` sebelum loop, terus `$nomor++` tiap baris — jadi nomornya lanjut biar gak reset ke 1 tiap ganti halaman.
- **Pagination**: pola `?page=N` + `LIMIT ? OFFSET ?`, contek dari `listing.php`/`admin-properti.php` yang udah ada.
- **Search**: pola `?q=...` + `LIKE` + prepared statement, digabung sama pagination lewat query string biar `?q=budi&page=2` gak saling ilangin. Kolom yang dicari beda tiap tabel (properti: judul+kota, transaksi: nama customer+judul properti, agen: nama+email, users: nama+email).
- Khusus `admin-transaksi.php`: search digabung juga sama filter status yang udah ada duluan, ketiganya (`status`, `q`, `page`) jalan bareng tanpa saling ilangin.
- Khusus `admin-agen.php`: query-nya pakai `LEFT JOIN` + `GROUP BY` (buat hitung jumlah properti per agen), jadi search & pagination-nya sedikit lebih rumit — total count dihitung terpisah dari data utama biar akurat.

## 6. Footer Dihapus dari Halaman Dashboard

- File baru `includes/dashboard-footer.php` — isinya cuma closing `</body></html>` + script Bootstrap JS, TANPA elemen `<footer>`.
- Semua halaman yang pakai sidebar (admin & user, 16 file) di-switch dari `require includes/footer.php` ke `require includes/dashboard-footer.php`.
- Halaman publik (`index.php`, `listing.php`, `detail.php`, `kontak.php`, `login.php`, `register.php`) tetap pakai `includes/footer.php` yang lengkap — sesuai permintaan, footer situs cuma relevan di halaman publik.

## 7. Hapus Wishlist Pakai AJAX (Gak Reload Halaman)

- `wishlist.php`: form submit biasa diganti tombol + JS `fetch()`. Pas diklik, kirim request POST ke `proses-wishlist.php` pakai header `X-Requested-With: XMLHttpRequest`, terus card wishlist-nya langsung dihapus dari DOM begitu server balas sukses — user gak pindah halaman/reload.
- `proses-wishlist.php`: dicek, kalau request-nya ada header `X-Requested-With: XMLHttpRequest`, balikin JSON (`{sukses:true, ada_di_wishlist:false}`) alih-alih `header('Location: ...')` redirect. Kalau request biasa (dari `detail.php`, bukan AJAX), tetep redirect kayak sebelumnya — jadi satu file ini sekarang support 2 mode.

## 8. `confirm()` Browser → Modal Bootstrap

3 tempat yang masih pakai `onsubmit="return confirm(...)"` diganti pola modal yang sama kayak yang udah ada di `admin-properti.php`/`admin-users.php`:

- `admin-agen.php` — hapus agen
- `admin-pesan.php` — hapus pesan kontak
- `properti-galeri.php` — hapus foto galeri

## 9. Keamanan Kecil

- `register.php`: tambah `session_regenerate_id(true)` pas auto-login setelah daftar (biar konsisten sama `login.php` yang udah bener duluan).
- `test-koneksi.php`: dihapus. File ini publik dan bocorin nama tabel + jumlah baris database — komentar di file itu sendiri udah bilang "hapus sebelum submit".

## 10. Statistik Transaksi Pakai Icon (Optional)

- `admin-dashboard.php`: tiap stat card di "Statistik Transaksi" (Menunggu, Diproses, Disetujui, Ditolak, Selesai) sekarang ada icon Bootstrap Icons yang beda-beda sesuai aktivitasnya.
- `admin-transaksi.php`: icon yang sama juga dipasang di filter tab status dan badge status di tabel, biar konsisten.

## 11. Tabel `admin-users.php` Dirapihin

Sebelumnya halaman ini isinya HTML satu baris panjang (susah dibaca/di-maintain). Ditulis ulang jadi struktur multi-baris standar (`<thead>`/`<tbody>` rapi) sama kayak tabel admin lainnya, sekalian nambahin nomor urut, pagination, search, dan tombol "Tambah User" + "Edit" di poin 4-5 di atas.

**Update (revisi kecil):** rewrite awal ternyata masih tampil polos tanpa border/warna header, walau struktur `<table>`-nya udah bener. Penyebabnya: style `.table-estate` (warna header navy, padding, hover row) **gak ada di `assets/css/style.css` global** — tiap halaman admin (`admin-agen.php`, `admin-properti.php`, `admin-transaksi.php`) nulis `<style>.table-estate{...}</style>` sendiri-sendiri di dalam filenya. `admin-users.php` versi asli (sebelum ditulis ulang) gak pernah punya blok `<style>` itu sama sekali karena originalnya satu baris tanpa styling section, jadi pas ditulis ulang, stylingnya ketinggalan. Fix: tambahin blok `<style>` yang sama ke `admin-users.php`.

**Catatan buat kamu:** ini pola duplikasi CSS yang riskan — 3 file beda punya salinan `.table-estate` yang sama persis. Kalau nanti bikin halaman tabel baru dan lupa copy blok `<style>`-nya, bakal kejadian bug yang sama lagi. Lebih aman kalau `.table-estate` dipindah ke `assets/css/style.css` sekali aja, terus semua halaman admin otomatis kepakai tanpa perlu copy-paste `<style>` di tiap file.

---

## 12. Cetak Bukti Transaksi (`admin-transaksi-detail.php`)

- Tombol "Cetak Bukti" cuma muncul kalau status transaksi `selesai` (lunas). Belum lunas, tombolnya gak ada — gak masuk akal ngasih bukti transaksi yang belum kelar.
- Tombol pakai `onclick="window.print()"` — panggil dialog print bawaan browser (dari situ user bisa print ke printer beneran atau "Save as PDF").
- Ada blok `<div id="print-area">` terpisah yang isinya struk/invoice rapi (kop surat navy+gold, nomor transaksi format `#TRX0001`, strip status "Lunas/Selesai", section Data Pemohon/Properti/Waktu, kotak total nilai transaksi, catatan admin kalau ada, area tanda tangan admin). Blok ini `display:none` di layar biasa, cuma muncul pas mode print lewat CSS `@media print`.
- Sidebar, header foto, breadcrumb, form edit pembayaran, dan tombol-tombol disembunyikan otomatis pas print (`@media print { ... display:none !important; }`) — biar hasil print bersih, cuma struknya doang yang kecetak, bukan seluruh halaman admin.

## 13. Teks Subjudul di `.page-header` Gak Kebaca

- `assets/css/style.css` gak pernah punya rule warna buat `<p class="lead">` / `<p>` polos di dalam `.page-header` — jadi teksnya kepake warna default Bootstrap (abu-abu gelap) di atas background navy gelap, kontrasnya jelek/gak kebaca. Ini bug global, kena semua halaman yang pakai `.page-header` (admin-properti, admin-users, admin-transaksi, dst), bukan cuma satu tempat.
- Fix: nambah `.page-header .lead, .page-header p { color: rgba(255,255,255,0.75); }` di `style.css`. Teks eyebrow (`<p class="eyebrow">`) tetep warna gold soalnya rule-nya lebih spesifik (`.page-header .eyebrow`), gak ketimpa.

## 14. Breadcrumb Diganti Deskripsi Singkat (Halaman Admin)

12 halaman admin yang tadinya cuma nampilin breadcrumb ("Beranda / Kelola Properti / ...") di bawah judul sekarang diganti `<p class="lead">` deskripsi singkat "halaman ini bisa ngapain" — nyamain pola yang udah lebih dulu ada di `admin-properti.php`/`admin-users.php`. File yang kena: `admin-dashboard.php`, `admin-agen.php`, `admin-pesan.php`, `admin-transaksi.php`, `admin-transaksi-detail.php`, `admin-user-edit.php`, `admin-user-tambah.php`, `agen-edit.php`, `agen-tambah.php`, `properti-edit.php`, `properti-galeri.php`, `properti-tambah.php`.

Halaman publik (`detail.php`, `listing.php`, `kontak.php`, `login.php`, `register.php`) **sengaja gak diubah** — di situ breadcrumb emang berguna karena nav-nya berlapis (contoh: Beranda / Properti / Detail Properti), beda sama halaman admin yang cuma 1-2 level dan user udah tau lagi di panel admin dari sidebar.

## 15. Sidebar Collapse/Expand

- Tombol toggle (icon `bi-chevron-double-left`, bentuk lingkaran kecil nempel di tepi kanan sidebar) ditambah di `includes/header.php`, muncul di kedua varian sidebar (admin & user).
- Semua teks label di sidebar (brand, nama user, teks menu, teks tombol bawah) dibungkus `<span class="sidebar-label">`, biar bisa disembunyikan lewat CSS (`display:none`) pas collapsed — ikon-nya tetep keliatan.
- CSS: `.dashboard-sidebar.collapsed` bikin sidebar jadi 84px (cuma ikon), konten utama (`body.has-dashboard-sidebar.sidebar-collapsed > ...`) otomatis nyesuain margin-left/width-nya. Ada `transition` biar animasinya halus, bukan langsung loncat.
- JS: script kecil di `includes/header.php`, jalan abis markup sidebar. Baca/simpan state ke `localStorage` (key `estateprima-sidebar-collapsed`) — jadi kalau kamu collapse terus pindah halaman, sidebar tetep collapsed (localStorage per browser, gak ke-share ke user lain).
- Di layar sempit (<1200px, sama kayak breakpoint mobile yang udah ada), tombol toggle disembunyikan dan sidebar balik ke layout stack horizontal yang emang udah dirancang buat mobile — collapse cuma relevan buat desktop.
- **Keterbatasan kecil**: pas collapsed, area brand "ESTATE PRIMA" jadi strip kosong (teksnya disembunyikan, belum ada logo mini pengganti). Kalau mau lebih rapi, bisa ditambah logo/inisial kecil yang cuma muncul pas collapsed.

## 16. Bug Fix: Hover Header Tabel Bikin Teks Hilang (`admin-properti.php`)

- CSS `.table-estate tr:hover { background:#fbf8f1; }` di `admin-properti.php` gak di-scope ke `tbody`, jadi kena baris `<thead>` juga. Pas kursor lewat header tabel, background header (harusnya navy gelap) ketiban jadi warna terang, sementara teks header tetep putih — jadinya teks header invisible pas di-hover.
- File lain (`admin-agen.php`, `admin-transaksi.php`, `admin-users.php`) udah bener dari awal (`.table-estate tbody tr:hover`), cuma `admin-properti.php` yang kelewatan. Fix: tambahin `tbody` di selector-nya.

## Yang BELUM dikerjain (giliran kamu)

- **Sidebar collapse/expand** pakai toggle ikon `<<`/`>>` — belum ada sama sekali, ini murni kerjaan kamu. Lihat juga `notes-student.md` bagian "Yang masih perlu kamu kerjain sendiri".
- Testing manual end-to-end di browser — semua perubahan di atas udah lolos `php -l` (cek syntax), tapi belum dites jalan beneran di browser/database. **Test dulu sebelum anggap selesai.**

## Cara Lihat Detail Perubahan

Semua perubahan ada di branch `mentor-fixes`. Buat lihat diff lengkap tiap file:

```
git checkout mentor-fixes
git diff main mentor-fixes --stat      # ringkasan file yang berubah
git diff main mentor-fixes -- namafile.php   # diff detail 1 file
```
