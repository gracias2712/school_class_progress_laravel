# Panduan Deploy Revisi ke cPanel

Website sudah live. Setelah pengaturan awal di bawah selesai, revisi dikirim dari Terminal tanpa mengunggah file satu per satu. Setiap `git push` ke branch `main` akan memicu GitHub Actions untuk mengunggah file melalui FTPS dan menjalankan migrasi Laravel.

## Pengaturan satu kali

1. Pastikan workflow [deploy.yml](../.github/workflows/deploy.yml) ada di repository GitHub dan Actions aktif.
2. Di GitHub buka **Settings → Secrets and variables → Actions**, lalu tambahkan:

   | Secret | Isi |
   |---|---|
   | `FTP_SERVER` | Host FTPS dari cPanel |
   | `FTP_USERNAME` | Username akun FTP deploy |
   | `FTP_PASSWORD` | Password akun FTP deploy |
   | `FTP_SERVER_DIR` | Fol   der proyek Laravel yang berisi `artisan` |
   | `DEPLOY_SECRET` | Token rahasia yang sama dengan nilai di `.env` hosting |
   | `FTP_PORT` | Opsional; port FTPS, biasanya `21` |

   Untuk `FTP_SERVER_DIR`, gunakan `./` jika akun FTP langsung membuka folder proyek Laravel. Jika akun FTP membuka home akun, isi path folder proyek di dalamnya, misalnya `public_html/` **hanya jika** file `artisan` memang berada di sana. Jangan isi `public_html/public`.
3. Jika `.env` hosting belum memiliki `DEPLOY_SECRET`, buat token acak di Terminal:

   ```bash
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```

   Simpan token yang sama di `.env` hosting dan GitHub secret `DEPLOY_SECRET`. Jangan masukkan token ke repository atau chat.
4. Di GitHub, pastikan branch default adalah `main`. Setelah pengaturan ini, tidak perlu mengulang langkah di atas untuk setiap revisi.

## Deploy revisi berikutnya

Buka Terminal VS Code pada folder proyek, lalu jalankan:

```bash
git status
git add .
git commit -m "Jelaskan revisi"
git push origin main
```

Pastikan `git status` hanya menunjukkan file revisi yang memang ingin dikirim. Jangan commit `.env`, password, atau file rahasia. Jika tidak ada perubahan yang perlu di-commit, Git akan memberi tahu bahwa tidak ada perubahan baru.

Setelah push, buka **GitHub → Actions → Deploy Laravel to cPanel**. Tunggu sampai langkah build, FTPS, dan migrasi berstatus berhasil. Jika gagal, buka detail langkah yang merah; perbaiki penyebabnya lalu buat commit dan push lagi.

## Yang dilakukan otomatis

- Dependency produksi dipasang dan aset frontend dibangun di GitHub Actions.
- File aplikasi disalin ke folder tujuan melalui FTPS; `.env`, database, file upload, dan log tidak ikut dikirim.
- Migrasi Laravel dijalankan setelah file berhasil dikirim. Seeder tidak dijalankan.
- Perubahan file/folder lokal yang belum di-commit dan di-push tidak akan tampil di hosting.

Jika hosting tidak mendukung FTPS atau migrasi otomatis gagal karena konfigurasi cPanel, periksa pesan kegagalan di **GitHub Actions** dan minta bantuan penyedia hosting. Jangan membuat URL publik untuk menjalankan perintah Artisan.
