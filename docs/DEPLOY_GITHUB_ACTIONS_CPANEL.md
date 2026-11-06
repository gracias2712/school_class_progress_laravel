# Panduan Deploy Laravel ke Domainesia dengan GitHub Actions (FTPS)

Panduan ini untuk domain `gracias-scp.web.id`, hosting cPanel Domainesia, dan pemula. Kode dibangun oleh GitHub Actions lalu dikirim melalui **FTPS**. Tidak perlu Terminal/SSH dari laptop. Pengaturan file dilakukan di File Manager, database di phpMyAdmin, dan migrasi (jika Cron Jobs tersedia) dijalankan melalui cron sementara—bukan endpoint publik.

## 1. Pastikan paket hosting siap

Sebelum mengubah DNS, tanyakan ke Domainesia atau periksa cPanel:

- Apakah paket Nimbus One menyediakan **PHP 8.3** dan ekstensi `pdo_mysql`, `mbstring`, `xml`, serta `zip`.
- Apakah **cPanel Cron Jobs** tersedia dan dapat menjalankan PHP CLI versi 8.3. Terminal interaktif tidak diperlukan.
- Apakah FTP terenkripsi **FTPS (explicit TLS), port 21** tersedia. Jangan memakai FTP biasa untuk kata sandi.
- Apakah document root domain utama bisa diarahkan ke folder proyek `public/`.

Proyek ini membutuhkan PHP `^8.3`. Jika salah satu fitur belum tersedia, minta bantuan Domainesia sebelum melanjutkan. Jangan memakai `--ignore-platform-reqs`.

## 2. Hubungkan domain dan aktifkan HTTPS

1. Masuk ke akun IDwebhost dan Domainesia.
2. Ikuti petunjuk DNS/nameserver **resmi yang tampil di akun atau email aktivasi Domainesia**. Jangan menyalin nameserver dari contoh atau panduan lama. Perubahan nameserver dapat mengganggu email domain jika DNS email belum disalin.
3. Tunggu DNS mengarah ke hosting, lalu di cPanel aktifkan sertifikat SSL untuk `gracias-scp.web.id` (dan `www` jika digunakan).
4. Di cPanel **Domains**, arahkan document root domain ke folder `public` aplikasi, misalnya:

   ```text
   /home/CPANELUSER/gracias-app/public
   ```

   Ganti `CPANELUSER` dengan nama akun cPanel. Source Laravel harus berada di luar `public_html` dan browser hanya boleh mengakses folder `public/`. Jika cPanel tidak mengizinkan mengganti document root domain utama, minta Domainesia mengaturnya. **Jangan** mengunggah seluruh proyek ke `public_html` atau membuat webhook publik sebagai pengganti Terminal.

## 3. Buat database MySQL

Di cPanel buka **MySQL Database Wizard**:

1. Buat database dan database user dengan kata sandi acak yang kuat.
2. Berikan user tersebut hak **ALL PRIVILEGES** hanya untuk database aplikasi.
3. Catat nama lengkap database, nama user, kata sandi, dan host database. cPanel biasanya menambahkan prefix akun ke nama database/user. Host sering `localhost`, tetapi gunakan nilai yang diberikan hosting.

Jangan kirim kredensial ini lewat chat atau commit ke GitHub.

## 4. Siapkan folder aplikasi dan akun FTPS

1. Di cPanel **File Manager**, buat folder aplikasi di luar `public_html`, misalnya `/home/CPANELUSER/gracias-app`.
2. Di **FTP Accounts**, buat akun khusus deploy dan batasi directory-nya tepat ke folder `gracias-app` tersebut, bukan seluruh home directory dan bukan `public_html`.
3. Catat hostname FTPS, username, dan kata sandi. Jika hosting memberi pilihan, gunakan FTPS explicit TLS di port 21.
4. Pastikan document root yang dibuat di langkah 2 menunjuk ke `gracias-app/public`.

## 5. Siapkan repository GitHub

Cara termudah untuk pemula adalah memasang **GitHub Desktop** (sudah menyediakan Git yang diperlukan; tidak perlu SSH):

1. Buat repository baru di GitHub. Jika proyek sudah berisi file, jangan tambahkan README/license dari GitHub agar tidak terjadi konflik commit awal.
2. Di GitHub Desktop pilih **File → Add Local Repository** dan pilih folder proyek ini. Jika diminta, buat repository Git di folder tersebut.
3. Pastikan `.env`, `vendor`, dan `node_modules` tidak dipilih untuk commit (sudah diabaikan oleh `.gitignore`). Jangan menghapus `.gitignore`.
4. Buat commit pertama, pilih **Publish repository**, lalu gunakan repository private jika kode tidak ingin dipublikasikan.
5. Pilih branch `main`. Push ke branch `main` akan memulai deploy otomatis.

## 6. Tambahkan GitHub Actions Secrets

Di repository GitHub buka **Settings → Secrets and variables → Actions → New repository secret**. Tambahkan:

| Secret | Isi |
|---|---|
| `FTP_SERVER` | Hostname FTPS dari Domainesia, tanpa `ftp://` |
| `FTP_USERNAME` | Username akun FTP khusus deploy |
| `FTP_PASSWORD` | Kata sandi akun FTP |
| `FTP_SERVER_DIR` | Folder tujuan relatif terhadap root FTP; gunakan `./` jika akun FTP dibatasi langsung ke folder `gracias-app` |

Jangan masukkan `.env`, kata sandi database, atau `APP_KEY` sebagai file di repository. Workflow sengaja mengecualikan `.env*`, `public/storage`, data lokal, dan log. Ia juga tidak menjalankan migrasi atau menghapus seluruh folder hosting.

## 7. Deploy pertama

1. Pastikan workflow tersedia di `.github/workflows/deploy.yml`.
2. Buat commit lalu **Push origin → main** dari GitHub Desktop.
3. Di GitHub buka tab **Actions**, pilih **Deploy Laravel to cPanel**, lalu pastikan build dan FTPS upload selesai dengan tanda hijau.
4. Setelah upload selesai, buat file bernama `.env` di folder `gracias-app` menggunakan cPanel File Manager. Workflow sengaja tidak mengunggah `.env.example`; buka file tersebut dari proyek lokal di VS Code, salin isinya ke file `.env` baru di File Manager, lalu ubah sekurang-kurangnya:

   ```dotenv
   APP_NAME="School Class Progress"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://gracias-scp.web.id

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=PREFIX_DATABASE
   DB_USERNAME=PREFIX_USER
   DB_PASSWORD="KATA_SANDI_DATABASE"

   SESSION_DRIVER=file
   CACHE_STORE=file
   QUEUE_CONNECTION=sync

   INITIAL_ADMIN_USERNAME=wakakur
   INITIAL_ADMIN_PASSWORD="KATA_SANDI_ADMIN_ACAK_YANG_KUAT"
   INITIAL_ADMIN_NAME="Waka Kurikulum"
   ```

   Ganti nilai database dengan informasi aktual cPanel. Buat kata sandi admin unik dengan password manager; jangan gunakan `password`. Simpan `.env` di folder aplikasi, bukan `public/`.
5. Buat `APP_KEY` tanpa Terminal:

   - Di komputer lokal, buka terminal Laragon pada folder proyek, lalu jalankan `php artisan key:generate --show`.
   - Salin nilai `base64:...` yang muncul ke `APP_KEY` di `.env` melalui File Manager.
   - Opsi `--show` hanya menampilkan key dan tidak mengubah `.env` lokal. Jangan membagikan atau menggunakan key ini untuk aplikasi lain.
6. Di cPanel File Manager, pastikan folder runtime berikut ada:

   ```text
   storage/app/public
   storage/app/private
   storage/logs
   storage/framework/cache/data
   storage/framework/sessions
   storage/framework/views
   bootstrap/cache
   ```

   Buat folder yang belum ada. Atur permission agar akun hosting dapat menulis sesuai arahan Domainesia; jangan gunakan `777`.
7. Untuk menjalankan migrasi/seeder tanpa Terminal, lanjutkan ke bagian 8 dan pastikan Cron Jobs tersedia. Jika Cron Jobs tidak tersedia, minta Domainesia membantu menjalankan Artisan dengan cara aman atau gunakan phpMyAdmin untuk mengimpor SQL hasil persiapan lokal. Jangan membuka database MySQL ke seluruh internet dan jangan membuat route migrasi publik.
8. Setelah migrasi dan seeder berhasil, hapus `INITIAL_ADMIN_PASSWORD` dari `.env` melalui File Manager. Buka situs dan uji login menggunakan username serta kata sandi admin yang sudah dipilih.

Workflow tidak mengirim database maupun berkas upload lokal di `storage/app/public`. Jika perlu memindahkan foto/dokumen yang sudah ada, salin secara terpisah lewat File Manager setelah memastikan file tersebut memang boleh dipindahkan; jangan commit data pengguna ke GitHub.

## 8. Jalankan migrasi tanpa Terminal

### Pilihan utama: Cron Jobs sementara di cPanel

Tanyakan ke Domainesia apakah Cron Jobs dapat menjalankan PHP CLI 8.3 dan minta **path PHP CLI 8.3 yang benar**. Jangan menebak path executable. Jika tersedia:

1. Pastikan `.env` dan folder aplikasi sudah siap. Untuk deploy pertama, `INITIAL_ADMIN_PASSWORD` harus masih diset.
2. cPanel → **Cron Jobs** → buat cron sementara yang berjalan sekali (misalnya pada menit terdekat yang dapat dipilih). Perintahnya berbentuk:

   ```text
   cd /home/CPANELUSER/gracias-app && /PATH/KE/PHP83 artisan migrate --force && /PATH/KE/PHP83 artisan db:seed --force
   ```

   Ganti `CPANELUSER` dan `/PATH/KE/PHP83` dengan nilai yang dikonfirmasi Domainesia. Jangan gunakan placeholder tersebut apa adanya.
3. Cron dapat berjalan berulang sesuai jadwal; setelah migrasi dan seeding berhasil, **segera hapus cron sementara** dari cPanel.
4. Jika perintah gagal, baca email/output cron yang tersedia, perbaiki penyebabnya, lalu jalankan ulang. Seeder aplikasi bersifat idempotent dan tidak mengganti kata sandi akun yang sudah ada.
5. Hapus `INITIAL_ADMIN_PASSWORD` dari `.env` setelah akun admin dibuat.

Jangan menjadwalkan cron migrasi permanen tiap menit dan jangan menaruh password database di URL publik.

### Jika Cron Jobs tidak tersedia

Untuk migrasi pertama, pilihan paling sederhana adalah meminta Domainesia mengaktifkan Cron Jobs atau menjalankan Artisan sekali untuk Anda. Alternatifnya adalah menyiapkan SQL pada database lokal kosong yang memakai versi database kompatibel, lalu ekspor struktur dan data awal yang diperlukan melalui phpMyAdmin dan impor SQL tersebut di phpMyAdmin hosting. Pastikan ekspor tidak memuat data pribadi atau data uji yang tidak ingin dipublikasikan.

Untuk perubahan database selanjutnya, minta Domainesia menjalankan perintah migrasi atau mengaktifkan Cron Jobs. Jangan mengizinkan akses remote MySQL dari sembarang alamat IP GitHub Actions demi migrasi otomatis.

## 9. Deploy berikutnya

Setelah perubahan kode, buat commit lalu push ke `main`. GitHub Actions meng-install dependency produksi, membangun aset Vite, dan mengunggah file lewat FTPS. Workflow tidak mengunggah `.env`, tidak menjalankan seeder/migrasi, dan tidak melakukan clean-slate.

Jika ada perubahan database, unggah kode lewat GitHub Actions seperti biasa, lalu jalankan migrasi melalui cron sementara seperti bagian 8:

```text
cd /home/CPANELUSER/gracias-app && /PATH/KE/PHP83 artisan migrate --force
```

Ganti placeholder dengan informasi dari Domainesia, lalu hapus cron setelah berhasil. Jangan menjalankan `db:seed` untuk deploy rutin.

## 10. Email aplikasi (OTP/reset password)

Jika fitur email/OTP digunakan, isi pengaturan SMTP pada `.env` memakai kredensial mailbox yang dibuat di hosting (misalnya `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, dan `MAIL_FROM_ADDRESS`). Minta nilai SMTP dan TLS/SSL yang benar ke Domainesia. Setelah mengubah `.env`, hapus cache konfigurasi melalui mekanisme Artisan yang disediakan Domainesia (Cron Jobs sementara). Jika cPanel tidak menyediakan cara menjalankan perintah Artisan, minta bantuan mereka; jangan membuat URL publik untuk menjalankan perintah.

## 11. Jika ada masalah

- **Workflow gagal login FTP:** cek `FTP_SERVER`, username, password, root FTP, FTPS, dan port 21; lihat detail error pada tab GitHub Actions.
- **HTTP 500:** pastikan `APP_DEBUG=false`, `.env` berada di folder aplikasi, versi PHP web 8.3, folder writable, dan database benar. Cek `storage/logs/laravel.log` di File Manager; jangan menampilkan isi log kepada publik.
- **CSS/JS tidak muncul:** pastikan langkah `npm run build` hijau dan document root menunjuk ke folder `public/`.
- **Koneksi database gagal:** periksa prefix database/user, password, host dan privileges di cPanel.
- **Cron migrasi gagal:** pastikan path aplikasi, path PHP CLI 8.3, `.env`, folder writable, dan kredensial database benar. Minta Domainesia memastikan ekstensi PHP tersedia.
- **Deploy menimpa data hosting:** `.env`, log, cache runtime, database SQLite, dan `public/storage` dikecualikan; jangan mengaktifkan opsi clean-slate.

## Catatan keamanan penting

- Jangan membuat route HTTP untuk menjalankan `migrate`, `db:seed`, shell command, atau cache clear. Endpoint semacam itu berisiko memberi akses eksekusi jarak jauh bila salah konfigurasi.
- Jangan commit `.env`, password, database dump, atau file kredensial.
- Pakai akun FTP terbatas dan FTPS; rotasi password FTP jika pernah terekspos.
- Backup database dan file penting sebelum perubahan besar.
