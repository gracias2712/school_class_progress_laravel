<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class GuruImport implements ToCollection
{
    protected $role_id;

    public function __construct($role_id)
    {
        $this->role_id = $role_id; // Role 2 = Guru Mapel
    }

    public function collection(Collection $rows): void
    {
        $defaultPassword = Hash::make('password');

        // Daftar kata kunci jabatan Non-Guru yang TIDAK BOLEH masuk ke Data Guru Mapel
        $jabatanNonGuru = [
            'KEPALA SEKOLAH', 'WAKA', 'BENDAHARA', 'BOS', 'PPAP', 
            'BIMBINGAN KONSELING', // Guru BK sudah punya halaman sendiri
            'DAPODIK', 'KESISWAAN', 'PENYIAP GAJI', 
            'KEARSIPAN', 'PERPUSTAKAAN', 'KEPEGAWAIAN', 
            'KUSTODIAN', 'BARANG', 'PEMBANTU UMUM', 
            'PENGELOLA LABORATORIUM', 'PERSURATAN', 
            'TEKNISI', 'SARANA PRASARANA', 'PUMK'
        ];

        foreach ($rows as $index => $row) {
            $noUrut = isset($row[0]) ? trim((string)$row[0]) : '';
            
            // Validasi: Hanya baca baris yang kolom pertamanya (No) berupa angka
            if (!is_numeric($noUrut)) {
                continue; 
            }

            $nip = isset($row[1]) ? trim((string)$row[1]) : '';
            $nama = isset($row[2]) ? trim((string)$row[2]) : '';
            $mapel = isset($row[8]) ? strtoupper(trim((string)$row[8])) : '';

            // Filter 1: Cek Kekosongan
            if ($nama === '' || $nip === '-' || $nip === '' || $mapel === '' || $mapel === 'MAPEL') {
                continue;
            }

            // Filter 2: TOLAK Jabatan Non-Guru (Staf, Kepala Sekolah, BK)
            $isNonGuru = false;
            foreach ($jabatanNonGuru as $jabatan) {
                if (str_contains($mapel, $jabatan)) {
                    $isNonGuru = true;
                    break;
                }
            }
            
            if ($isNonGuru) {
                continue; // Skip baris ini, jangan dimasukkan ke database guru
            }

            // Buat Username dari NIP
            $username = strtolower(preg_replace('/\s+/', '', $nip));

            // Simpan atau Update Akun Guru Mapel
            User::updateOrCreate(
                ['username' => $username], 
                [
                    'fullname' => strtoupper($nama),
                    'nisn_nip' => $nip,
                    'subject'  => $mapel,
                    'email'    => $username . '@ukkscp.com',
                    'role_id'  => $this->role_id,
                    'password' => $defaultPassword,
                ]
            );
        }
    }
}