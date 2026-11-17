# Deploy Menu MBG ke Hosting (MySQL)

Panduan ini mengasumsikan hosting menyediakan **PHP 8.3+, MySQL 8 / MariaDB 10.6+, Composer**, dan akses SSH
(minimal Terminal di cPanel). Aplikasi tidak memakai Node/npm — cukup PHP.

## 1. Ekstensi PHP wajib

`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `zip`, `gd`, `bcmath`.

`zip` dan `gd` dibutuhkan untuk ekspor Excel/PDF dan unggah logo. Jika ekstensi tidak bisa diaktifkan lewat
cPanel, minta hosting mengaktifkannya.

## 2. Unggah berkas

- Document root domain **harus mengarah ke folder `public/`**, bukan ke akar proyek. Kalau hosting tidak bisa
  mengubah document root, unggah isi `public/` ke `public_html/` lalu sesuaikan `index.php` (dua baris `require`
  menuju `../mbg-app/vendor/...`) — cara ini kurang aman, utamakan document root langsung.
- Jangan unggah `.env` lokal, `database/database.sqlite`, `database/backup/`, `storage/logs/*`, atau `node_modules`.
- `storage/` dan `bootstrap/cache/` harus dapat ditulis (biasanya `755` atau `775`).

## 3. Database

Buat database dan user khusus (jangan pakai `root` di hosting):

```sql
CREATE DATABASE mbg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mbg'@'localhost' IDENTIFIED BY 'SANDI_KUAT';
GRANT ALL PRIVILEGES ON mbg.* TO 'mbg'@'localhost';
FLUSH PRIVILEGES;
```

## 4. Berkas `.env` produksi

```dotenv
APP_NAME="Menu MBG"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.ac.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mbg
DB_USERNAME=mbg
DB_PASSWORD=SANDI_KUAT

SESSION_DRIVER=database
SESSION_LIFETIME=720
CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_LEVEL=error
```

Lalu:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate        # hanya bila APP_KEY belum ada
php artisan migrate --force
php artisan db:seed --force     # ReferenceSeeder + MbgSeeder + CycleSeeder + UserSeeder
php artisan mbg:import-tkpi     # 1.021 bahan dari database/data/tkpi.csv
php artisan mbg:isi-harga       # isi harga bahan yang masih 0 dari harga acuan
php artisan storage:link        # agar logo SPPG tampil
```

`db:seed` mengisi data contoh (6 menu, penerima contoh, akun demo). Untuk instalasi produksi yang langsung
dipakai, boleh dilewati — tetapi `ReferenceSeeder` (DBMP 50 bahan + 32 harga acuan) sebaiknya tetap dijalankan:

```bash
php artisan db:seed --class=ReferenceSeeder --force
```

Untuk instalasi yang **sudah berjalan** dan baru menerima fitur siklus 5/10, cukup jalankan seeder siklus
(idempoten; menu yang sudah diedit tidak ditimpa):

```bash
php artisan db:seed --class=CycleSeeder --force
```

Seeder ini menambahkan pengaturan `pola_siklus=5/10` dan `panjang_siklus=10`, serta siklus 5, 10, dan 12 hari.

## 4b. Harga pasar otomatis

```bash
php artisan mbg:tarik-harga           # tarik PIHPS (11 komoditas strategis)
php artisan mbg:isi-harga             # terapkan ke bahan berharga 0
```

Tambahkan cron harian agar harga diperbarui sendiri:

```cron
30 5 * * * cd /path/ke/mbg-app && php artisan schedule:run >> /dev/null 2>&1
```

Sumber: PIHPS Bank Indonesia via agregator publik `harga-pangan.fly.dev` (bukan API resmi). Bila hosting memblokir
koneksi keluar, tombol "Tarik harga pasar" akan menampilkan pesan gagal dan harga acuan lama tetap dipakai.

## 5. Optimasi

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Setiap kali `.env` diubah: `php artisan config:clear` lalu `config:cache` lagi.

## 6. Keamanan sebelum dipakai

- Ganti sandi semua akun demo (`admin@wareg.id`, `rina@wareg.id`, `sari@wareg.id`, `haccp@wareg.id`,
  `akuntan@wareg.id`) — semuanya dibuat dengan sandi `password` oleh `UserSeeder`.
- Pastikan `APP_DEBUG=false` dan `APP_ENV=production`.
- Aktifkan HTTPS (Let's Encrypt dari cPanel) dan arahkan `APP_URL` ke `https://`.
- Batasi akses `/audit` dan `/pengguna` hanya untuk admin/akuntan (sudah default).

## 7. Backup

Harian, simpan di luar folder publik:

```bash
mysqldump -u mbg -p mbg | gzip > ~/backup/mbg-$(date +%F).sql.gz
```

Simpan 30 hari terakhir. Untuk SQLite lama (sebelum pindah) arsipkan `database/database.sqlite`.

## 8. Pindah dari SQLite (lokal)

Data lokal sudah dipindah dengan `database/scripts/copy_sqlite_to_mysql.php` (27 tabel, jumlah baris identik).
Skrip itu **tidak** dipakai di hosting: hosting dimulai dari `migrate --seed` seperti langkah 4.

Rollback lokal: kembalikan `.env.sqlite-backup` menjadi `.env`, lalu `php artisan config:clear`.

## 8b. Mode offline

Form HACCP dan Sisa Makanan menyimpan entri di perangkat (`localStorage`) saat jaringan putus, lalu mengirim
otomatis ketika online. HACCP memakai `client_uuid` sehingga kiriman ulang tidak menggandakan baris.

Yang belum: cache halaman penuh (PWA). Service worker membutuhkan HTTPS; di jaringan lokal saat ini aplikasi
diakses lewat HTTP Tailscale, jadi halaman tetap butuh koneksi. Aktifkan HTTPS bila ingin PWA penuh.

## 9. Catatan MySQL lokal (komputer ini)

MySQL 8.4 sudah dipasang sebagai **Windows Service** `MySQL84` dengan start type **AUTO_START** (akun LocalSystem),
jadi otomatis hidup setelah reboot. Cek dan kendalikan (perlu admin):

```bat
sc query MySQL84
net start MySQL84
net stop MySQL84
```

Berkas konfigurasi: `C:/laragon/bin/mysql/mysql-8.4.3-winx64/my.ini`; data: `C:/laragon/data/mysql-8.4`.
Kredensial lokal: `root` tanpa sandi, database `mbg`, port `3306`.
