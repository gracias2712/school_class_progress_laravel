<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas - SCP School</title>
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
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Manajemen Data Kelas</h1>
                <p class="text-xs text-slate-500 font-medium mt-1">Kelola daftar kelas untuk penyusunan jadwal</p>
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
                            <span>➕</span> Tambah Kelas Baru
                        </h3>
                        
                        <form action="{{ route('classes.store') }}" method="POST" id="formTambahKelas">
                            @csrf
                            <input type="hidden" name="grade_level" id="hidden_grade">
                            <input type="hidden" name="major" id="hidden_major">
                            <input type="hidden" name="section" id="hidden_section">

                            <div class="mb-5">
                                <label class="block text-sm font-bold text-slate-600 mb-2">Nama Kelas</label>
                                <input type="text" name="class_name" id="input_class_name" required placeholder="CONTOH: XII RPL 1" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm font-bold uppercase">
                                <p class="text-[11px] text-slate-400 mt-2">Format <b>WAJIB</b> dipisah spasi. (Cth: X DKV 2)</p>
                            </div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-md">
                                Simpan Kelas
                            </button>
                        </form>
                    </div>
                </div>

                <!-- KOLOM KANAN: Tabel Data -->
                <div class="lg:col-span-2 space-y-4">
                    
                    <!-- Bagian Header Tabel dan Tombol Import -->
                    <div class="flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-sm font-bold text-slate-700">Daftar Kelas Aktif</p>
                        <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-xs font-extrabold shadow-sm hover:bg-indigo-100 transition">
                            📥 Import Excel
                        </button>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase">ID</th>
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase">Nama Kelas</th>
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase text-center">Jml Siswa</th>
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($classes as $class)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-4 px-6 text-sm font-medium text-slate-500">{{ $class->id }}</td>
                                        <td class="py-4 px-6 text-sm font-extrabold text-slate-800">{{ $class->class_name }}</td>
                                        <td class="py-4 px-6 text-sm font-bold text-slate-600 text-center">
                                            <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs">
                                                {{ $class->users_count ?? 0 }} Siswa
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <form action="{{ route('classes.destroy', $class->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kelas ini? Siswa di dalamnya akan kehilangan relasi kelas.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-bold transition">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-10 text-center text-slate-400 text-sm font-medium">Belum ada data kelas yang didaftarkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL IMPORT KELAS -->
        <div id="importModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="bg-indigo-600 px-6 py-4"><h3 class="text-white font-bold">Import Data Kelas</h3></div>
                <form action="{{ route('classes.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File Excel Data Kelas (Kolom A: Nama Kelas)</label>
                    <input type="file" name="file" accept=".xlsx, .csv" required class="w-full border p-2 rounded-lg mb-4">
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 rounded-lg font-bold">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold">Upload Data</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('formTambahKelas').addEventListener('submit', function(e) {
            let className = document.getElementById('input_class_name').value.trim().toUpperCase();
            let parts = className.split(/\s+/);
            
            if (parts.length >= 3) {
                document.getElementById('hidden_grade').value = parts[0];
                document.getElementById('hidden_section').value = parts[parts.length - 1];
                let majorParts = parts.slice(1, parts.length - 1);
                document.getElementById('hidden_major').value = majorParts.join(' ');
            } else {
                e.preventDefault();
                alert("Peringatan: Format nama kelas salah!\nPastikan Anda menggunakan spasi.\n\nContoh yang benar: XII RPL 1");
            }
        });
    </script>
</body>
</html>