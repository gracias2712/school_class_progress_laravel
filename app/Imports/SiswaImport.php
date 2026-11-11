<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Classe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SiswaImport implements ToCollection, WithMultipleSheets 
{
    protected $role_id;

    public function __construct($role_id)
    {
        $this->role_id = $role_id;
    }

    public function collection(Collection $rows): void
    {
        // Tetap kosong, pancingan untuk Laravel Excel
    }

    public function sheets(): array
    {
        return [
            0 => new SiswaSheetImport($this->role_id),
            1 => new SiswaSheetImport($this->role_id),
            2 => new SiswaSheetImport($this->role_id),
        ];
    }
}

class SiswaSheetImport implements ToCollection
{
    protected $role_id;
    protected $classes;
    protected $defaultPassword;

    public function __construct($role_id)
    {
        $this->role_id = $role_id;
        
        // AMBIL DATA KELAS: Bersihkan SEMUA karakter non-huruf/angka agar kebal spasi
        $this->classes = Classe::pluck('id', 'class_name')->mapWithKeys(function ($id, $name) {
            $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($name));
            return [$cleanName => $id];
        })->toArray();
        
        $this->defaultPassword = Hash::make('password');
    }

    public function collection(Collection $rows): void
    {
        $currentClassId = null;

        foreach ($rows as $row) {
            Log::info("Membaca baris: ", $row->toArray());

            if (!isset($row[0])) continue;

            $col0 = trim((string)$row[0]); 
            $col1 = isset($row[1]) ? trim((string)$row[1]) : ''; 
            $col2 = isset($row[2]) ? trim((string)$row[2]) : ''; 

            // Cek apakah ini Baris Judul Kelas
            if (str_contains(strtoupper($col0), 'KELAS')) {
                $rawClassName = preg_replace('/^.*?\bKELAS\b\s*:?\s*/i', '', $col0);
                $className = preg_replace('/[^A-Z0-9]/', '', strtoupper($rawClassName ?? ''));
                
                // Cari ID Kelas
                $currentClassId = $this->classes[$className] ?? null;
                Log::info("Kelas Ditemukan: " . $className . " | ID: " . $currentClassId);
                continue; 
            }

            // Cek apakah ini Baris Data Siswa
            if (is_numeric($col0) && $col1 !== '' && $col2 !== '') {
                $nis = $col1;
                $nama = $col2;
                $username = strtolower($nis);

                User::updateOrCreate(
                    ['username' => $username], 
                    [
                        'fullname' => strtoupper($nama),
                        'nisn_nip' => $nis,
                        'class_id' => $currentClassId, 
                        'role_id'  => $this->role_id,
                        'email'    => $username . '@ukkscp.com',
                        'password' => $this->defaultPassword,
                    ]
                );

                Log::info("Data Siswa Disimpan: NIS " . $nis . " dengan class_id " . $currentClassId);
            }
        }
    }
}