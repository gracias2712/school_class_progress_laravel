<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Journal;
use App\Models\Schedule;
use Illuminate\Support\Carbon;

class JournalController extends Controller
{
    public function store(Request $request)
    {
        // Validasi ekstensi dan batas ukuran file sesuai rubrik UKK
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'topic_description' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
        ]);

        $today = Carbon::today()->toDateString();

        // Mencegah Guru menginput jurnal 2x di hari dan jadwal yang sama
        if (Journal::where('schedule_id', $request->schedule_id)->where('date', $today)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Jurnal untuk jadwal ini sudah diisi hari ini.'
            ], 400); // 400 Bad Request
        }

        // Proses Upload Foto Real-time
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('journals', 'public');
        }

        // Simpan Data Jurnal
        $journal = Journal::create([
            'schedule_id' => $request->schedule_id,
            'date' => $today,
            'topic_description' => $request->topic_description,
            'photo_path' => $photoPath,
        ]);

        // Mengembalikan response JSON agar bisa dibaca oleh Flutter
        return response()->json([
            'success' => true,
            'message' => 'Jurnal berhasil disimpan',
            'data' => $journal
        ], 201); // 201 Created
    }
}