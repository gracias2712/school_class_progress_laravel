<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Journal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        // 1. Set Tanggal Pilihan (Default: Hari Ini)
        $selectedDate = $request->input('date', date('Y-m-d'));
        
        // 2. Dapatkan Nama Hari dalam Bahasa Indonesia (Senin, Selasa, dll)
        $dayName = Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd'); 

        // 3. Ambil SELURUH Jadwal Mengajar pada hari tersebut
        $schedules = Schedule::with(['teacher', 'classe'])
            ->where('day_of_week', $dayName)
            ->orderBy('start_time', 'asc')
            ->get();

        // 4. Ambil jurnal yang SUDAH DIBUAT pada tanggal tersebut
        $journals = Journal::whereDate('created_at', $selectedDate)
            ->get()
            ->keyBy('schedule_id'); // Jadikan ID jadwal sebagai key agar mudah dipetakan

        $allSchedules = [];
        $missingJournals = [];
        $terisiCount = 0;

        $now = Carbon::now();
        $isToday = Carbon::parse($selectedDate)->isToday();
        $isPastDate = Carbon::parse($selectedDate)->isPast() && !$isToday;

        // 5. Gabungkan Jadwal dengan Status Jurnal
        foreach ($schedules as $schedule) {
            $journal = $journals->get($schedule->id);
            $endTimeStr = $selectedDate . ' ' . $schedule->end_time;
            $endTime = Carbon::parse($endTimeStr);
            
            $status = 'Menunggu';
            if ($journal) {
                $status = 'Terisi';
                $terisiCount++;
            } else {
                // Jika hari sudah lewat, ATAU hari ini tapi jam mengajar sudah selesai >30 menit lalu
                if ($isPastDate || ($isToday && $now->copy()->subMinutes(30)->greaterThan($endTime))) {
                    $status = 'Terlewat';
                    $missingJournals[] = $schedule; // Masukkan ke panel kelalaian kanan
                } else {
                    $status = 'Belum Diisi'; // Sedang berlangsung / belum waktunya
                }
            }

            $allSchedules[] = (object) [
                'schedule' => $schedule,
                'journal'  => $journal,
                'status'   => $status
            ];
        }

        return view('master.journals.index', compact('selectedDate', 'allSchedules', 'missingJournals', 'terisiCount'));
    }
}