<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class GuruBkImport implements ToCollection
{
    public function collection(Collection $rows): void
    {
        $defaultPassword = Hash::make('password');

        foreach ($rows as $index => $row) {
            // Kita gunakan Kolom A (Nomor Urut) sebagai penanda utama
            $kolomNo = isset($row[0]) ? trim((string)$row[0]) : '';
            
            // Jika kita menemukan kata "KETERANGAN" di kolom A atau B, 
            // hentikan seluruh proses import (break) karena data di bawahnya pasti bukan tabel guru lagi.
            $kolomB = isset($row[1]) ? trim((string)$row[1]) : '';
            if (str_contains(strtoupper($kolomNo), 'KETERANGAN') || str_contains(strtoupper($kolomB), 'KETERANGAN')) {
                break; // Berhenti total
            }

            // Validasi: Hanya baca baris yang Kolom A-nya benar-benar berisi ANGKA (Nomor Urut 1, 2, 3...)
            if (!is_numeric($kolomNo)) {
                continue; // Skip baris ini jika bukan angka
            }

            // Ambil Nama Guru BK di Kolom D (Index 3)
            $namaBK = isset($row[3]) ? trim((string)$row[3]) : '';
            
            if ($namaBK === '' || str_contains(strtoupper($namaBK), 'NAMA GURU') || str_contains(strtoupper($namaBK), 'GURU BK')) {
                continue;
            }

            // Buat Username
            $username = strtolower(preg_replace('/[^A-Za-z]/', '', explode(',', $namaBK)[0])) . 'bk';

            User::updateOrCreate(
                ['fullname' => strtoupper($namaBK), 'role_id' => 4], 
                [
                    'username' => $username,
                    'email'    => $username . '@ukkscp.com',
                    'password' => $defaultPassword,
                ]
            );
        }
    }
}