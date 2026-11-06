<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Imports\RoomImport;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RoomController extends Controller
{
    public function index()
    {
        // Proteksi Manual: Tendang user jika bukan Waka Kurikulum (Role 1)
        if (Auth::user()->role_id != 1) {
            return redirect('/dashboard')->with('error', 'Akses Ditolak! Halaman khusus Waka Kurikulum.');
        }

        // Ambil data ruangan dan urutkan berdasarkan nama
        $rooms = Room::orderBy('room_name', 'asc')->get();
        return view('master.rooms.index', compact('rooms'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role_id != 1) {
            return redirect('/dashboard')->with('error', 'Akses Ditolak!');
        }

        $request->validate([
            'room_name' => 'required|string|max:50|unique:rooms,room_name'
        ]);

        Room::create([
            'room_name' => strtoupper($request->room_name) // Format huruf besar (Contoh: LAB KOMPUTER 1)
        ]);

        return redirect()->back()->with('success', 'Ruangan baru berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        if (Auth::user()->role_id != 1) {
            return redirect('/dashboard')->with('error', 'Akses Ditolak!');
        }

        Room::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Ruangan berhasil dihapus!');
    }

    public function import(Request $request)
    {
        if (!Auth::check() || Auth::user()->role_id != 1) {
            return redirect('/dashboard')->with('error', 'Akses Ditolak!');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new RoomImport(), $request->file('file'));
            return redirect()->back()->with('success', 'Data Ruangan berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}