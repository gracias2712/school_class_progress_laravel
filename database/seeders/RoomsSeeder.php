<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomsSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar ruangan yang sering dipakai XII RPL 1 dan XII RPL 2 berdasarkan jadwal
        $rooms = [
            'Ruang 12 (Teori)',
            'Ruang 22 (Teori)',
            'LAB 16 (Komputer)',
            'LAB 18 (Komputer)',
            'LAB 19 (Komputer)',
            'Lapangan Olahraga (OR)',
            'Lab IPA (LIPA)'
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate([
                'room_name' => $room,
            ]);
        }
    }
}