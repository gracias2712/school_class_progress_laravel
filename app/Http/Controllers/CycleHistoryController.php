<?php

namespace App\Http\Controllers;

use App\Models\CycleHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CycleHistoryController extends Controller
{
    public function index()
    {
        // Proteksi: Hanya Waka Kurikulum
        if (Auth::user()->role_id != 1) {
            return redirect('/dashboard')->with('error', 'Akses Ditolak!');
        }

        // Ambil data history urut dari yang paling baru
        $histories = CycleHistory::with('user')->orderBy('created_at', 'desc')->get();

        return view('histories.cycles.index', compact('histories'));
    }
}