<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class ClassImport implements ToCollection
{
    public function collection(Collection $rows): void
    {
        $detectedClasses = [];

        foreach ($rows as $row) {
            foreach ($row as $cell) {
                // Bersihkan string dari spasi berlebih
                $val = strtoupper(trim(preg_replace('/\s+/', ' ', (string)$cell)));
                
                // REGEX KETAT: Hanya menangkap format (X/XI/XII/10/11/12) + spasi + (RPL/TKJ/DKV/AK/LPS/MP/BR/BD) + spasi + (Angka)
                if (preg_match('/^(10|11|12|X|XI|XII)\s+(RPL|TKJ|DKV|AK|LPS|MP|BR|BD)\s*([1-9])?$/i', $val, $matches)) {
                    // Standardisasi tingkat kelas (Ubah 10 jadi X, 11 jadi XI, 12 jadi XII)
                    $tingkat = $matches[1];
                    if ($tingkat == '10') $tingkat = 'X';
                    if ($tingkat == '11') $tingkat = 'XI';
                    if ($tingkat == '12') $tingkat = 'XII';
                    
                    $jurusan = $matches[2];
                    $rombel = isset($matches[3]) ? $matches[3] : '';
                    
                    $cleanClassName = trim("$tingkat $jurusan $rombel");
                    $detectedClasses[] = $cleanClassName;
                }
            }
        }

        $uniqueClasses = array_unique($detectedClasses);

        foreach ($uniqueClasses as $className) {
            $parts = explode(' ', $className);
            $grade = $parts[0] ?? null;
            $section = count($parts) >= 3 ? end($parts) : null;
            $major = count($parts) >= 3 ? implode(' ', array_slice($parts, 1, -1)) : ($parts[1] ?? null);

            DB::table('classes')->updateOrInsert(
                ['class_name' => $className],
                [
                    'grade_level' => $grade,
                    'major'       => $major,
                    'section'     => $section,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );

            // OTOMATIS MEMBUAT RUANGAN UNTUK KELAS INI (Solusi Ruangan Kelas)
            DB::table('rooms')->updateOrInsert(
                ['room_name' => 'RUANG ' . $className],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}