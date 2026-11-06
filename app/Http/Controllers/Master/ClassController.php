<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ClassController extends Controller
{
    public function index()
    {
        // Proteksi Manual: Tendang user jika bukan Waka Kurikulum (Role 1)
        if (Auth::user()->role_id != 1) {
            return redirect('/dashboard')->with('error', 'Akses Ditolak! Halaman khusus Waka Kurikulum.');
        }

        // Ambil semua data kelas beserta jumlah siswa di dalamnya
        $classes = Classe::withCount('users')->orderBy('class_name', 'asc')->get();
        return view('master.classes.index', compact('classes'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role_id != 1) {
            return redirect('/dashboard')->with('error', 'Akses Ditolak!');
        }

        $request->validate([
            'class_name' => 'required|string|max:50|unique:classes,class_name'
        ]);

        // Simpan class_name beserta pecahan kolom spesifiknya (grade_level, major, section)
        Classe::create([
            'class_name'  => strtoupper($request->class_name),
            'grade_level' => $request->grade_level,
            'major'       => $request->major,
            'section'     => $request->section
        ]);

        return redirect()->back()->with('success', 'Kelas baru berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        if (Auth::user()->role_id != 1) {
            return redirect('/dashboard')->with('error', 'Akses Ditolak!');
        }

        Classe::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Kelas berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new \App\Imports\ClassImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Kelas berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}