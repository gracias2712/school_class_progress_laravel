<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemConfiguration;
use Illuminate\Support\Facades\Auth;

class SystemConfigController extends Controller
{
    public function toggleCycle(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'active_cycle' => 'required|in:1,2'
        ]);

        // 1. Ambil data konfigurasi saat ini
        $config = \App\Models\SystemConfiguration::first() ?? new \App\Models\SystemConfiguration();
        
        $oldCycle = str_replace('Putaran ', '', $config->active_cycle ?? '1');
        $newCycle = $request->active_cycle;

        // 2. Ubah dan simpan siklus baru ke tabel konfigurasi
        $config->active_cycle = $newCycle;
        $config->updated_by = \Illuminate\Support\Facades\Auth::id() ?? 1;
        $config->save();

        // 3. CATAT KE DALAM HISTORY PUTARAN (Logika yang sebelumnya tertinggal)
        \App\Models\CycleHistory::create([
            'changed_by_user_id' => \Illuminate\Support\Facades\Auth::id(),
            'from_cycle' => $oldCycle,
            'to_cycle' => $newCycle,
            'change_reason' => 'Pergantian Siklus dari Dasbor Waka'
        ]);

        return redirect()->back()->with('success', "Siklus blok aktif berhasil diubah ke Putaran {$newCycle}.");
    }

    public function toggleLock(Request $request)
    {
        if (Auth::user()->role_id != 1) {
            return back()->withErrors(['unauthorized' => 'Anda tidak memiliki hak akses untuk mengunci jadwal.']);
        }

        $config = SystemConfiguration::first() ?? SystemConfiguration::create(['active_cycle' => 'Putaran 1', 'is_schedule_locked' => false]);
        
        $config->is_schedule_locked = !$config->is_schedule_locked;
        $config->updated_by = Auth::id();
        $config->save();

        $statusText = $config->is_schedule_locked ? 'dikunci' : 'dibuka kembali';
        return back()->with('success', 'Jadwal berhasil ' . $statusText . '!');
    }
}