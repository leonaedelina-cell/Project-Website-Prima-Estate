# Jawaban Soal Teori PHP dan Bootstrap

## Identitas

- **Nama:** [Isi nama]
- **NIM/Kelas:** [Isi NIM atau kelas]
- **Mata Kuliah:** [Isi nama mata kuliah]
- **Dosen:** [Isi nama dosen]
- **Tanggal:** [Isi tanggal]

## Jawaban

### 1. Perbedaan `==` dan `===`

`==` hanya membandingkan isi nilainya. PHP boleh mengubah tipe data agar nilainya bisa dibandingkan. Sementara itu, `===` membandingkan isi dan tipe datanya sekaligus.

```php
$angka = 5;
$teks = '5';

var_dump($angka == $teks);  // true
var_dump($angka === $teks); // false
```

Hasil `==` adalah `true` karena angka 5 dan teks '5' dianggap memiliki nilai sama. Hasil `===` adalah `false` karena tipe datanya berbeda.

### 2. Array indexed dan array asosiatif

Array indexed memakai nomor sebagai index dan biasanya dimulai dari 0. Array ini cocok untuk menyimpan daftar sederhana.

```php
$tipe_properti = ['rumah', 'apartemen', 'tanah'];
```

Array asosiatif memakai nama sebagai key. Array ini cocok untuk data yang memiliki keterangan, seperti nama dan role user.

```php
$user = ['nama' => 'Dewi', 'role' => 'user'];
```

### 3. `include`, `require`, dan versi `_once`

`include` dan `require` digunakan untuk memasukkan file PHP lain. Jika file `include` tidak ditemukan, program biasanya masih mencoba berjalan. Jika file `require` tidak ditemukan, program biasanya langsung berhenti.

`include_once` dan `require_once` memastikan file hanya dimasukkan satu kali. Ini penting untuk `config/database.php`, agar koneksi atau fungsi tidak dibuat berulang. Karena database wajib tersedia, `require_once` lebih cocok.

### 4. Perbedaan `$_GET` dan `$_POST`

`$_GET` mengambil data dari URL, misalnya `detail.php?id=5`. Data ini terlihat di address bar dan cocok untuk pencarian atau filter. `$_POST` mengirim data di dalam request, sehingga lebih cocok untuk form dan perubahan data.

Form hapus sebaiknya memakai POST karena penghapusan mengubah database. Link GET bisa menjalankan aksi hanya karena URL dibuka. Meski begitu, POST tetap harus dilengkapi pengecekan login dan CSRF.

### 5. XSS dan `htmlspecialchars()`

XSS adalah serangan ketika input user dianggap sebagai HTML atau JavaScript oleh browser.

```php
$nama = '<script>alert("XSS")</script>';
echo htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
```

Tanpa `htmlspecialchars()`, browser bisa menjalankan script tersebut. Fungsi ini mengubah karakter khusus seperti `<` dan `>` menjadi teks biasa.

### 6. `PRIMARY KEY` dan `AUTO_INCREMENT`

`PRIMARY KEY` adalah nomor identitas untuk setiap data. Nomor ini membedakan satu properti dari properti lainnya dan tidak boleh sama.

`AUTO_INCREMENT` membuat MySQL mengisi nomor ID secara otomatis. Admin tidak perlu menentukan ID saat menambah properti.

Contohnya:

```text
ID 1 = Rumah Green Valley
ID 2 = Apartemen City View
ID 3 = Villa Pinus
```

Jika ID 2 dihapus, data baru biasanya mendapat ID 4, bukan ID 2. Jadi nomor ID boleh memiliki jeda karena fungsinya sebagai identitas, bukan nomor urut.

### 7. `WHERE`, `ORDER BY`, dan `LIMIT`/`OFFSET`

`WHERE` memilih data, `ORDER BY` mengurutkan, dan `LIMIT`/`OFFSET` mengatur data yang tampil di setiap halaman.

- `WHERE` memilih data berdasarkan syarat.
- `ORDER BY` mengatur urutan data.
- `LIMIT` menentukan jumlah data yang ditampilkan.
- `OFFSET` menentukan berapa data yang dilewati.

Urutan penulisan dalam query adalah:

```sql
SELECT * FROM properti
WHERE status = 'tersedia'
ORDER BY created_at DESC
LIMIT 9 OFFSET 9;
```

Pada contoh tersebut, database memilih properti tersedia yang terbaru, melewati 9 data pertama, lalu menampilkan 9 data berikutnya. Ini adalah contoh untuk halaman kedua jika satu halaman berisi 9 properti.

### 8. Prepared statement dan SQL Injection

Prepared statement bisa dianggap seperti formulir SQL yang sudah memiliki tempat kosong. Tanda `?` adalah tempat untuk input user. Nilai input dimasukkan melalui `bind_param()`, bukan ditempel langsung ke perintah SQL.

```php
$stmt = mysqli_prepare($koneksi, 'SELECT * FROM users WHERE email = ?');
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
```

Contohnya, jika kolom email diisi teks berbahaya seperti `' OR '1'='1`, database tetap menganggapnya sebagai teks email biasa. Input tersebut tidak bisa mengubah perintah SQL. Inilah alasan prepared statement membantu mencegah SQL Injection.

### 9. Redirect after POST

Redirect after POST adalah cara mengarahkan user ke halaman lain setelah data POST selesai diproses.

```php
header('Location: admin-agen.php?pesan=tambah-berhasil');
exit;
```

Pola ini mencegah form terkirim ulang saat halaman di-refresh. Setelah redirect, browser membuka halaman dengan request GET baru.

### 10. `password_hash()` dan `password_verify()`

Password tidak boleh disimpan sebagai teks biasa karena bisa langsung terbaca jika database bocor. Enkripsi biasa juga kurang tepat karena bisa dibuka kembali dengan kunci.

`password_hash()` mengubah password menjadi hash satu arah. Saat login, `password_verify()` mencocokkan password input dengan hash di database tanpa mengetahui password aslinya.

### 11. Cara kerja `$_SESSION`

`$_SESSION` bisa dibayangkan seperti kartu tanda pengenal sementara untuk user. Setelah login, PHP menyimpan `user_id`, `nama`, dan `role` di dalamnya. Saat user membuka halaman lain, PHP membaca kartu tersebut sehingga tahu siapa yang sedang login.


### 12. Auth check dan admin check

Auth check memeriksa apakah user sudah login. Di project ini fungsinya `cek_login()`. Admin check memeriksa user sudah login dan role-nya `admin`, melalui `cek_admin()`.

Admin check membutuhkan auth check terlebih dahulu agar role diambil dari session, bukan dari input user. User yang belum login harus diarahkan ke halaman login.

### 13. Mengapa menyembunyikan link admin bukan security

CSS hanya menyembunyikan tampilan. User masih bisa mengetik URL admin secara langsung, jadi `display:none` bukan perlindungan keamanan.

Server harus memeriksa session dan role pada setiap halaman serta endpoint. Di project ini pemeriksaan dilakukan dengan `cek_admin()`.

### 14. Relasi many-to-many dan pivot table

Pada relasi many-to-many, satu user bisa menyukai banyak properti dan satu properti bisa disukai banyak user. Menyimpan semua ID dalam satu kolom akan sulit dicari dan dikelola.

Karena itu dibuat tabel penghubung `wishlist` yang berisi `user_id` dan `properti_id`. Satu baris berarti satu properti disukai satu user. Unique key mencegah data yang sama masuk dua kali.

### 15. `ON DELETE CASCADE`

`ON DELETE CASCADE` otomatis menghapus data terkait ketika data induknya dihapus. Contohnya, saat properti dihapus, data galeri dan wishlist yang terkait dapat ikut terhapus.

Fitur ini mencegah adanya data relasi yang menunjuk ke data yang sudah tidak ada. Namun penggunaannya harus hati-hati karena banyak data bisa ikut terhapus.

### 16. Search dengan `LIKE`

Fitur search bekerja seperti mencari kata di dalam judul properti. Jika keyword-nya `jakarta`, PHP membuat pola `%jakarta%`. Tanda `%` berarti boleh ada kata lain sebelum atau sesudah `jakarta`.

```php
$keyword = "%{$q}%";
$stmt = mysqli_prepare($koneksi, 'SELECT * FROM properti WHERE judul LIKE ?');
mysqli_stmt_bind_param($stmt, 's', $keyword);
```

Pola tersebut tetap harus dikirim sebagai parameter dengan `bind_param()`. Jika keyword ditempel langsung ke SQL, input user bisa dianggap sebagai perintah dan menyebabkan SQL Injection.

### 17. Pagination dengan `LIMIT` dan `OFFSET`

Pagination membagi data menjadi beberapa halaman. Jika satu halaman berisi 9 data, halaman kedua memakai `OFFSET 9` dan halaman ketiga memakai `OFFSET 18`.

Query `COUNT(*)` diperlukan untuk mengetahui jumlah semua data yang sesuai filter. Jumlah itu dibagi dengan data per halaman dan dibulatkan ke atas untuk mengetahui jumlah halaman.

### 18. Bootstrap dan CDN

Bootstrap adalah framework CSS dan JavaScript yang menyediakan grid responsif, tombol, form, modal, navbar, dan banyak class siap pakai.

Bootstrap CDN mengambil file dari internet saat halaman dibuka. Cara ini praktis, tetapi membutuhkan koneksi internet. Jika file disimpan di project, aplikasi bisa berjalan tanpa CDN, tetapi file dan versinya harus dikelola sendiri.

### 19. Modal Bootstrap

`data-bs-toggle="modal"` memberi tahu Bootstrap untuk membuka modal. `data-bs-target="#modalHapus"` menunjuk modal berdasarkan ID-nya. JavaScript Bootstrap kemudian mengatur tampilan dan tombol modal.

Modal lebih cocok untuk tampilan aplikasi karena isi, tombol, warna, dan desainnya bisa disesuaikan. Modal juga memberi penjelasan yang lebih jelas daripada `confirm()`. Namun server tetap harus memvalidasi proses hapus.

### 20. Kapan memakai partial

Header, footer, navbar, dan sidebar cocok dijadikan partial jika dipakai di banyak halaman. Dalam project ini `includes/header.php` dan `includes/footer.php` mengurangi pengulangan kode dan memudahkan perubahan bersama.

Bagian yang hanya dipakai sekali dan khusus untuk satu halaman boleh tetap berada di file tersebut. Partial sebaiknya dipakai jika benar-benar mengurangi pengulangan atau menjaga konsistensi.


