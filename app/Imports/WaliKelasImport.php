<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Classe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class WaliKelasImport implements ToCollection
{
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            if ($index < 2) continue; // Lewati baris judul atas
            
            $kelasNama = trim((string)$row[1]); // Kolom B
            $namaWalas = trim((string)$row[2]); // Kolom C

            if ($kelasNama === '' || $namaWalas === '' || str_contains(strtoupper($namaWalas), 'NAMA WALI')) continue;

            // Cari ID Kelas
            $kelas = Classe::whereRaw("REPLACE(UPPER(class_name), ' ', '') = ?", [str_replace(' ', '', strtoupper($kelasNama))])->first();
            
            // Buat Username dari nama depan + angka acak (karena NIP tidak ada di Excel)
            $username = strtolower(preg_replace('/[^A-Za-z]/', '', explode(',', $namaWalas)[0])) . rand(10,99);

            User::updateOrCreate(
                ['fullname' => strtoupper($namaWalas), 'role_id' => 3], // 3 = Wali Kelas
                [
                    'username' => $username,
                    'email'    => $username . '@ukkscp.com',
                    'class_id' => $kelas ? $kelas->id : null,
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}