<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Ruangan - SCP School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden font-sans text-slate-800">

    @include('layouts.sidebar')

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200 p-5 flex justify-between items-center z-10">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Manajemen Data Ruangan</h1>
                <p class="text-xs text-slate-500 font-medium mt-1">Kelola daftar ruang kelas dan laboratorium untuk penyusunan jadwal</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-700">{{ Auth::user()->fullname }}</p>
                    <p class="text-[11px] font-semibold text-slate-400">ID Akses: {{ Auth::user()->role_id }}</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex justify-center items-center text-indigo-600 font-bold border border-indigo-200">
                    {{ substr(Auth::user()->fullname, 0, 1) }}
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold flex items-center gap-3">
                    <span class="text-xl">✅</span> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold flex items-center gap-3">
                    <span class="text-xl">❌</span> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- KOLOM KIRI: Form Tambah -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sticky top-0">
                        <h3 class="text-lg font-extrabold text-slate-800 mb-4 flex items-center gap-2">
                            <span>➕</span> Tambah Ruangan
                        </h3>
                        <form action="{{ route('rooms.store') }}" method="POST">
                            @csrf
                            <div class="mb-5">
                                <label class="block text-sm font-bold text-slate-600 mb-2">Nama Ruangan</label>
                                <input type="text" name="room_name" required placeholder="Contoh: LAB KOMPUTER 1" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 transition text-sm font-medium uppercase">
                                <p class="text-[11px] text-slate-400 mt-2">Nama ruangan tidak boleh ganda (harus unik).</p>
                            </div>
                            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-4 rounded-xl transition shadow-md">
                                Simpan Ruangan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- KOLOM KANAN: Tabel Data -->
                <div class="lg:col-span-2 space-y-4">
                    
                    <!-- Header Tabel & Tombol Import -->
                    <div class="flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-sm font-bold text-slate-700">Daftar Ruangan Aktif</p>
                        <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-xs font-extrabold shadow-sm hover:bg-indigo-100 transition">
                            📥 Import Excel
                        </button>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase w-1/4">ID Ruangan</th>
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase w-1/2">Nama Ruangan / Lab</th>
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase text-right w-1/4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($rooms as $room)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-4 px-6 text-sm font-medium text-slate-500">RM-0{{ $room->id }}</td>
                                        <td class="py-4 px-6 text-sm font-extrabold text-slate-800">{{ $room->room_name }}</td>
                                        <td class="py-4 px-6 text-right">
                                            <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ruangan ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-bold transition">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-10 text-center text-slate-400 text-sm font-medium">Belum ada data ruangan yang didaftarkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL IMPORT RUANGAN -->
        <div id="importModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="bg-indigo-600 px-6 py-4"><h3 class="text-white font-bold">Import Data Ruangan</h3></div>
                <form action="{{ route('rooms.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File Excel Data Ruangan (Kolom B: Nama Ruang)</label>
                    <input type="file" name="file" accept=".xlsx, .csv" required class="w-full border p-2 rounded-lg mb-4">
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 rounded-lg font-bold">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold">Upload Data</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>