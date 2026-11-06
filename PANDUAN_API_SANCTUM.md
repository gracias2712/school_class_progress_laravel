# Panduan API Laravel Sanctum

Panduan ini menjelaskan implementasi dan pengujian API JSON yang memakai token Laravel Sanctum pada proyek ini. API dapat dipakai oleh klien yang berbeda tanpa memberikan akses langsung ke database MySQL.

> **Batas panduan:** halaman Blade dan login web yang memakai session tetap berjalan seperti sekarang. API token berdiri terpisah dari alur web tersebut. Panduan ini tidak membahas Flutter.

## 1. Kondisi proyek saat ini

Proyek menggunakan Laravel `^13.8` dan Sanctum `^4.0`. Rute API didaftarkan di `bootstrap/app.php`. API yang aktif mencakup autentikasi, jadwal, jurnal, presensi, laporan, pengajuan/verifikasi izin, dan pengelolaan profil. Otorisasi mobile menggunakan nama role dari relasi pengguna; jangan mengasumsikan ID role.

Tabel `personal_access_tokens` juga sudah memiliki migration di:

```text
database/migrations/2026_10_14_122125_create_personal_access_tokens_table.php
```

Migration tabel token tersebut sudah dijalankan pada database lokal yang diperiksa. Di lingkungan lain, periksa status migration sebelum mencoba autentikasi token. **Jangan membuat migration kedua untuk tabel `personal_access_tokens`.**

## 2. Alur autentikasi yang akan dibuat

1. Klien mengirim `username` dan `password` ke `POST /api/login`.
2. API memeriksa kredensial dan mengembalikan token Sanctum sekali saja.
3. Klien mengirim token pada header `Authorization: Bearer ...` untuk rute yang dilindungi.
4. API membaca identitas pengguna dari token yang valid.
5. `POST /api/logout` menghapus token yang sedang digunakan.

Token bukan password dan tidak boleh dicatat ke log atau dikirim lewat koneksi HTTP tanpa TLS. Di production, gunakan HTTPS.

## 3. Pastikan database dan migration siap

Periksa status migration:

```powershell
php artisan migrate:status
```

Jika tabel `personal_access_tokens` belum berstatus `Ran`, jalankan:

```powershell
php artisan migrate
```

Perintah ini menggunakan koneksi database yang sudah diatur di `.env`. Jangan masukkan password database ke source code atau file panduan ini.

## 4. Aktifkan token Sanctum pada model User

Buka `app/Models/User.php`, tambahkan import `HasApiTokens`, lalu gunakan trait tersebut di model. Jangan menghapus trait atau relasi model yang sudah ada.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'role_id',
        'class_id',
        'username',
        'password',
        'fullname',
        'email',
        'nisn_nip',
        'subject',
    ];

    protected $hidden = [
        'password',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'student_id');
    }
}
```

Kode di atas memperlihatkan susunan lengkap model saat ini dengan perubahan utama `use HasApiTokens`. Jika model di proyek sudah berubah, pertahankan field dan relasi yang terbaru; cukup tambahkan import serta trait Sanctum.

## 5. Buat controller autentikasi API

Buat file `app/Http/Controllers/Api/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::query()
            ->where('username', $validated['username'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Username atau password salah.',
            ], 401);
        }

        $token = $user->createToken(
            $validated['device_name'],
            ['*'],
            now()->addDays(30),
        )->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userData($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role', 'classe']);

        return response()->json([
            'data' => $this->userData($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'fullname' => $user->fullname,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role?->role_name,
            'class' => $user->classe?->class_name,
        ];
    }
}
```

Catatan:

- API login menggunakan `username`, mengikuti login web yang sudah ada; alamat email tidak diwajibkan karena data lama bisa memiliki email kosong.
- `device_name` digunakan sebagai nama token agar token yang tersimpan dapat dikenali dan dicabut.
- Token pada contoh berlaku 30 hari. Ubah durasi sesuai kebijakan aplikasi.
- `plainTextToken` hanya dikirim pada respons login. Sanctum menyimpan hash token di database, sehingga token mentah tidak bisa ditampilkan lagi dari tabel.
- Payload profil dibuat eksplisit agar password, NISN/NIP, dan field internal lain tidak ikut terkirim.
- Logout ini ditujukan untuk alur bearer token. Ia menghapus token yang sedang digunakan, bukan token dari perangkat lain.

## 6. Contoh endpoint baca jadwal

Catatan: contoh kode historis di bawah bukan implementasi yang harus ditempel ulang. `app/Http/Controllers/Api/ScheduleController.php` adalah implementasi aktif dan otorisasi saat ini memakai role `Guru`, `Wali Kelas`, `Siswa`, dan `Orang Tua` berdasarkan nama role. Wali Kelas/Guru hanya melihat jadwal yang diajar; Siswa melihat kelasnya; Orang Tua hanya melihat jadwal anak yang ditautkan.

Buat file `app/Http/Controllers/Api/ScheduleController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cycle_type' => ['sometimes', 'in:1,2'],
        ]);

        $user = $request->user();

        abort_unless(in_array((int) $user->role_id, [1, 2], true), 403, 'Akses ditolak.');

        $query = Schedule::query()
            ->with([
                'teacher:id,fullname',
                'classe:id,class_name',
                'room:id,room_name',
            ]);

        if ((int) $user->role_id === 2) {
            $query->where('teacher_id', $user->id);
        }

        if (isset($validated['cycle_type'])) {
            $query->where('cycle_type', 'Putaran '.$validated['cycle_type']);
        }

        $schedules = $query
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get([
                'id',
                'teacher_id',
                'class_id',
                'room_id',
                'subject',
                'day_of_week',
                'start_time',
                'end_time',
                'cycle_type',
            ]);

        return response()->json([
            'data' => $schedules->map(fn (Schedule $schedule) => [
                'id' => $schedule->id,
                'subject' => $schedule->subject,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'cycle_type' => $schedule->cycle_type,
                'teacher' => [
                    'id' => $schedule->teacher?->id,
                    'fullname' => $schedule->teacher?->fullname,
                ],
                'class' => [
                    'id' => $schedule->classe?->id,
                    'class_name' => $schedule->classe?->class_name,
                ],
                'room' => [
                    'id' => $schedule->room?->id,
                    'room_name' => $schedule->room?->room_name,
                ],
            ]),
        ]);
    }
}
```

Parameter `cycle_type` bersifat opsional. Gunakan `?cycle_type=1` atau `?cycle_type=2` untuk memfilter putaran. Database proyek menyimpan nilai siklus sebagai `Putaran 1` atau `Putaran 2`.

## 7. Daftarkan rute API

Ganti isi `routes/api.php` dengan rute berikut. Rute lama `GET /api/user` digantikan oleh `GET /api/me`, yang mengembalikan data profil yang sudah dibatasi field-nya.

```php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/schedules', [ScheduleController::class, 'index']);
});
```

Karena `bootstrap/app.php` proyek ini sudah mendaftarkan `routes/api.php`, tidak perlu menambahkan konfigurasi routing API lagi. Middleware `auth:sanctum` akan memvalidasi bearer token. Middleware `throttle:6,1` membatasi upaya login menjadi enam request per menit.

## 8. Endpoint dan respons

Bagian ini **bukan kode yang perlu ditempel ke file PHP**. Tabel di bawah adalah daftar URL API yang dibuat pada langkah 7. Contoh JSON adalah gambaran respons yang akan dikirim server setelah klien memanggil endpoint login.

| Method | Path | Auth | Keterangan |
|---|---|---|---|
| `POST` | `/api/login` | Tidak | Menerima `username`, `password`, dan `device_name`; menghasilkan bearer token |
| `GET` | `/api/me` | Bearer token | Mengambil profil pengguna yang sedang terautentikasi |
| `POST` | `/api/logout` | Bearer token | Menghapus token yang digunakan pada request |
| `GET` | `/api/schedules` | Bearer token | Mengambil jadwal sesuai role; opsional `cycle_type=1` atau `2` |

Route mobile tambahan yang aktif adalah `GET /api/children` (Orang Tua), `GET /api/leave-requests/mine` (Siswa/Orang Tua), `POST /api/journals` (Guru/Wali Kelas), `GET /api/students` dan `POST /api/attendances` (Guru/Wali Kelas), route verifikasi izin (Wali Kelas/Guru BK), serta laporan presensi (Wali Kelas/Guru BK). Wali Kelas hanya dapat mengakses kelasnya; Orang Tua hanya dapat mengakses siswa yang ID-nya tertaut pada field `parent_id` akun Orang Tua. Foto jurnal dan pengajuan izin wajib karena kolom path pada skema database bersifat wajib. Registrasi API hanya menerima role Siswa; akun staf dan Orang Tua memakai alur provisioning website/admin. Endpoint ekspor presensi saat ini belum menghasilkan file PDF/Excel.

Respons login yang berhasil:

```json
{
  "message": "Login berhasil.",
  "token_type": "Bearer",
  "access_token": "1|token-rahasia-yang-diberikan-saat-login",
  "user": {
    "id": 1,
    "fullname": "Nama Pengguna",
    "username": "namauser",
    "email": null,
    "role": "Guru",
    "class": null
  }
}
```

Respons endpoint terlindungi yang dipanggil tanpa token atau dengan token tidak valid adalah `401 Unauthorized`. Pengguna yang sudah login tetapi tidak memiliki role yang diizinkan menerima `403 Forbidden`. Request dengan data tidak valid menerima `422 Unprocessable Entity`.

## 9. Uji API di Windows PowerShell

Kode pada bagian ini juga **tidak ditaruh ke file proyek**. Jalankan perintahnya di terminal untuk menguji API secara manual.

Anda boleh memakai terminal yang ada di Visual Studio Code. Buka menu **Terminal > New Terminal** (atau tekan ``Ctrl+Shift+` ``) untuk membuat terminal pertama, lalu jalankan:

```powershell
php artisan serve
```

Biarkan terminal pertama tetap terbuka dan berjalan; terminal itu sedang menjalankan server Laravel. Di Visual Studio Code, pilih **Terminal > New Terminal** sekali lagi untuk membuka terminal kedua. Pastikan terminal kedua berada di folder proyek yang sama dan menggunakan PowerShell. Terminal kedua inilah yang dimaksud dengan “PowerShell lain”—tidak harus membuka aplikasi PowerShell terpisah di luar Visual Studio Code.

Jalankan kode login berikut di terminal kedua. Ganti nilai username dan password dengan akun lokal yang memang boleh digunakan:

```powershell
$body = @{
    username = "namauser"
    password = "password-lokal"
    device_name = "browser-dev"
} | ConvertTo-Json

$login = Invoke-RestMethod `
    -Method Post `
    -Uri "http://127.0.0.1:8000/api/login" `
    -ContentType "application/json" `
    -Body $body

$token = $login.access_token
```

Ambil profil dan jadwal dengan token yang baru diterbitkan:

```powershell
$headers = @{
    Authorization = "Bearer $token"
    Accept = "application/json"
}

Invoke-RestMethod `
    -Method Get `
    -Uri "http://127.0.0.1:8000/api/me" `
    -Headers $headers

Invoke-RestMethod `
    -Method Get `
    -Uri "http://127.0.0.1:8000/api/schedules?cycle_type=1" `
    -Headers $headers
```

Logout dan pastikan token tersebut tidak dapat digunakan lagi:

```powershell
Invoke-RestMethod `
    -Method Post `
    -Uri "http://127.0.0.1:8000/api/logout" `
    -Headers $headers
```

Perintah PowerShell menyimpan token di variabel sesi lokal. Jangan menyalin token sungguhan ke issue, chat, screenshot, log, atau repository.

## 10. Pemeriksaan setelah menerapkan panduan

Kedua baris di bawah adalah **perintah terminal**, bukan kode PHP untuk dimasukkan ke file. Jalankan perintah pertama, tunggu sampai selesai dan periksa daftar rute. Setelah itu jalankan perintah kedua untuk tes.

```powershell
php artisan route:list --path=api
```

Jika daftar rute sudah tampil, jalankan:

```powershell
php artisan test
```

Pastikan daftar rute menunjukkan `POST api/login`, `GET api/me`, `POST api/logout`, dan `GET api/schedules`. Tes otomatis yang sudah ada juga harus tetap lulus.

## 11. Checklist sebelum deploy

- Gunakan HTTPS untuk semua request API.
- Pastikan `.env` production berisi koneksi database yang benar dan tidak pernah di-commit.
- Jalankan migration di environment yang benar setelah membuat backup database.
- Jangan menonaktifkan validasi role atau mengganti query jadwal dengan query tanpa pembatasan pengguna.
- Jangan mengirim password, token, atau data sensitif ke log.
- Tentukan durasi token dan prosedur pencabutan token sesuai kebijakan aplikasi.
- Pastikan reverse proxy/web server mengarahkan request `/api/*` ke aplikasi Laravel.
- Verifikasi respons `401`, `403`, dan `422`, selain respons sukses.

API yang ditambahkan pada panduan ini hanya menyediakan autentikasi, profil, dan pembacaan jadwal. Untuk data lain, tambahkan endpoint terpisah dengan validasi input, pembatasan akses per role/pemilik data, dan hanya field respons yang diperlukan.
