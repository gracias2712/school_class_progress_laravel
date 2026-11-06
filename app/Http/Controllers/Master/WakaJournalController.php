<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Journal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WakaJournalController extends Controller
{
    public function index(Request $request)
    {
        // 1. Set Tanggal Pilihan (Default: Hari Ini)
        $selectedDate = $request->input('date', date('Y-m-d'));
        
        // 2. Dapatkan Nama Hari dalam Bahasa Indonesia (Senin, Selasa, dll)
        $dayName = Carbon::parse($selectedDate)->locale('id')->isoFormat('dddd'); 

        // 3. Ambil SELURUH Jadwal Mengajar pada hari tersebut beserta Relasi Guru dan Kelas
        $schedules = Schedule::with(['teacher', 'classe'])
            ->where('day_of_week', $dayName)
            ->orderBy('start_time', 'asc')
            ->get();

        // 4. Ambil jurnal yang SUDAH DIBUAT pada tanggal tersebut
        $journals = Journal::whereDate('date', $selectedDate)
            ->get()
            ->keyBy('schedule_id'); // Jadikan ID jadwal sebagai key index array

        $allSchedules = [];
        $missingJournals = [];
        $terisiCount = 0;

        $now = Carbon::now();
        $isToday = Carbon::parse($selectedDate)->isToday();
        $isPastDate = Carbon::parse($selectedDate)->isPast() && !$isToday;

        // 5. Looping Jadwal dan Gabungkan dengan Status Jurnal
        foreach ($schedules as $schedule) {
            // Cek apakah jadwal ini sudah ada di data jurnal yang ditarik
            $journal = $journals->get($schedule->id);
            
            // Format waktu selesai kelas hari ini
            $endTimeStr = $selectedDate . ' ' . $schedule->end_time;
            $endTime = Carbon::parse($endTimeStr);
            
            $status = 'Menunggu';
            if ($journal) {
                $status = 'Terisi';
                $terisiCount++;
            } else {
                // Jika hari sudah lewat, ATAU hari ini tapi jam mengajar sudah lewat dari 30 menit
                if ($isPastDate || ($isToday && $now->copy()->subMinutes(30)->greaterThan($endTime))) {
                    $status = 'Terlewat';
                    $missingJournals[] = $schedule; // Masukkan ke array panel kelalaian jurnal
                } else {
                    $status = 'Belum Diisi'; // Sedang berlangsung / belum waktunya
                }
            }

            // Kemas data utuh ke dalam array object untuk dilempar ke Blade
            $allSchedules[] = (object) [
                'schedule' => $schedule,
                'journal'  => $journal,
                'status'   => $status
            ];
        }

        // 6. Return ke view Blade beserta data pendukungnya
        return view('master.journals.index', compact('selectedDate', 'allSchedules', 'missingJournals', 'terisiCount'));
    }
}