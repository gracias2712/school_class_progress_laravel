<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jadwal - SCP School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden font-sans text-slate-800">

    @include('layouts.sidebar')

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200 p-5 flex justify-between items-center z-10">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Manajemen Jadwal</h1>
                <p class="text-xs font-medium mt-1 {{ optional($config)->is_schedule_locked ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                    {{ optional($config)->is_schedule_locked ? 'Sistem Terkunci (Read-Only)' : 'Kelola jadwal mengajar secara real-time' }}
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <form action="{{ route('schedules.index') }}" method="GET" class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg px-3 py-1.5 shadow-sm">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tampil:</label>
                    <select name="cycle_type" onchange="this.form.submit()" class="bg-transparent text-sm font-bold text-blue-700 outline-none cursor-pointer">
                        <option value="1" {{ $selectedCycle == '1' ? 'selected' : '' }}>Putaran 1</option>
                        <option value="2" {{ $selectedCycle == '2' ? 'selected' : '' }}>Putaran 2</option>
                    </select>
                </form>
                <a href="/dashboard" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-bold md:hidden">Kembali</a>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            
            @if(session('success')) <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold">{{ session('error') }}</div> @endif
            @if($errors->any()) <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold"><ul class="list-disc pl-5">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul></div> @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                @if(Auth::user()->role_id == 1 && !optional($config)->is_schedule_locked)
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sticky top-0">
                        <h3 class="text-lg font-extrabold text-slate-800 mb-4">Buat Jadwal</h3>
                        
                        <form action="{{ route('schedules.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Guru Pengampu</label>
                                <select name="teacher_id" id="guru_select" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                                    <option value="">-- Pilih Guru --</option>
                                    @foreach($teachers as $t) 
                                        <option value="{{ $t->id }}" data-mapel="{{ $t->subject }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>
                                            {{ $t->fullname }}
                                        </option> 
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Kelas</label>
                                    <select name="class_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium">
                                        <option value="">-- Kelas --</option>
                                        @foreach($classes as $c) <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option> @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Ruangan</label>
                                    <select name="room_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium">
                                        <option value="">-- Ruang --</option>
                                        @foreach($rooms as $r) <option value="{{ $r->id }}" {{ old('room_id') == $r->id ? 'selected' : '' }}>{{ $r->room_name }}</option> @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Mata Pelajaran</label>
                                <input type="text" name="subject" id="mapel_input" value="{{ old('subject') }}" required placeholder="Pilih guru terlebih dahulu..." readonly class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-bold uppercase text-indigo-700 outline-none cursor-not-allowed">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Hari</label>
                                    <select name="day_of_week" id="day_select" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium focus:border-indigo-500 cursor-pointer">
                                        <option value="">-- Hari --</option>
                                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat'] as $hari)
                                            <option value="{{ $hari }}" {{ old('day_of_week') == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Siklus</label>
                                    <select name="cycle_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium">
                                        <option value="1" {{ old('cycle_type') == '1' ? 'selected' : '' }}>Putaran 1</option>
                                        <option value="2" {{ old('cycle_type') == '2' ? 'selected' : '' }}>Putaran 2</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Jam Mulai Sesi</label>
                                    <select name="start_time" id="start_time_select" data-old="{{ old('start_time') }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium focus:border-indigo-500 outline-none transition cursor-pointer">
                                        <option value="">-- Pilih Hari Dulu --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Jam Selesai Sesi</label>
                                    <select name="end_time" id="end_time_select" data-old="{{ old('end_time') }}" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium focus:border-indigo-500 outline-none transition cursor-pointer">
                                        <option value="">-- Pilih Hari Dulu --</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-md">
                                Simpan Jadwal
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <div class="{{ (Auth::user()->role_id == 1 && !optional($config)->is_schedule_locked) ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-4">
                    
                    <div class="flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                        <p class="text-sm font-bold text-slate-700">Daftar Jadwal Mengajar</p>
                        @if(Auth::user()->role_id == 1 && !optional($config)->is_schedule_locked)
                        <button type="button" onclick="document.getElementById('importScheduleModal').classList.remove('hidden')" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-4 py-2 rounded-lg text-xs font-extrabold transition shadow-sm">
                            Import Data File
                        </button>
                        @endif
                    </div>

                    <!-- KOTAK FILTER DATA TABLES -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Filter Hari</label>
                                <select id="filterHari" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-blue-500 cursor-pointer">
                                    <option value="">-- Semua Hari --</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Pencarian Guru / Pelajaran</label>
                                <input type="text" id="filterGuru" placeholder="Ketik nama guru atau mapel..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500">
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-4 pt-2 border-t border-slate-100">
                            <div class="flex-1">
                                <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Tingkat Kelas</label>
                                <select id="filterKelas" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-blue-500 cursor-pointer">
                                    <option value="">-- Semua Kelas --</option>
                                    <option value="X ">10 (X)</option>
                                    <option value="XI ">11 (XI)</option>
                                    <option value="XII ">12 (XII)</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Jurusan</label>
                                <select id="filterJurusan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-blue-500 cursor-pointer">
                                    <option value="">-- Semua Jurusan --</option>
                                    <option value="RPL">RPL</option>
                                    <option value="TKJ">TKJ</option>
                                    <option value="DKV">DKV</option>
                                    <option value="AKL">AKL</option>
                                    <option value="MP">MP</option>
                                    <option value="BR">BR</option>
                                    <option value="BD">BD</option>
                                    <option value="LPS">LPS</option>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Rombel</label>
                                <select id="filterRombel" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-blue-500 cursor-pointer">
                                    <option value="">-- Semua Rombel --</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden overflow-x-auto p-4">
                        <table id="scheduleTable" class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase">Waktu & Hari</th>
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase">Pelajaran & Guru</th>
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase text-center">Lokasi</th>
                                    @if(Auth::user()->role_id == 1 && !optional($config)->is_schedule_locked)
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase text-right">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($schedules as $schedule)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-4 px-6">
                                            <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-extrabold rounded mb-1 border border-blue-200 uppercase tracking-wide">
                                                PUTARAN {{ str_replace('Putaran ', '', $schedule->cycle_type) }}
                                            </span>
                                            <div class="font-extrabold text-slate-800">{{ $schedule->day_of_week }}</div>
                                            <div class="text-xs font-bold text-indigo-600 mt-0.5">
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <p class="font-extrabold text-slate-800 uppercase">{{ $schedule->subject }}</p>
                                            <p class="text-xs text-slate-500 font-medium mt-1">{{ optional($schedule->teacher)->fullname }}</p>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <div class="flex flex-col items-center gap-1.5">
                                                <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-md text-[11px] font-extrabold w-full text-center">{{ optional($schedule->classe)->class_name ?? optional($schedule->class)->class_name }}</span>
                                                <span class="bg-amber-50 text-amber-700 px-3 py-1 rounded-md text-[11px] font-extrabold w-full text-center">{{ optional($schedule->room)->room_name }}</span>
                                            </div>
                                        </td>
                                        
                                        @if(Auth::user()->role_id == 1 && !optional($config)->is_schedule_locked)
                                        <td class="py-4 px-6 text-right">
                                            <div class="flex justify-end gap-2">
                                                <a href="{{ route('schedules.edit', $schedule->id) }}" class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-bold transition border border-blue-100">
                                                    Edit
                                                </a>
                                                <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-bold transition border border-red-100">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- MODAL IMPORT -->
        <div id="importScheduleModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-white font-bold text-lg">Import Jadwal Mengajar</h3>
                    <button type="button" onclick="document.getElementById('importScheduleModal').classList.add('hidden')" class="text-indigo-200 hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-6">
                    <form action="{{ route('schedules.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="import_cycle_type" class="block text-sm font-bold text-slate-700 mb-2">Siklus / Putaran</label>
                            <select id="import_cycle_type" name="cycle_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium">
                                <option value="1" {{ old('cycle_type', $selectedCycle) == '1' ? 'selected' : '' }}>Putaran 1</option>
                                <option value="2" {{ old('cycle_type', $selectedCycle) == '2' ? 'selected' : '' }}>Putaran 2</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File (.xlsx / .csv)</label>
                            <input type="file" name="file" accept=".xlsx, .xls, .csv" required class="w-full text-sm text-slate-500 border border-slate-200 rounded-lg p-2">
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" onclick="document.getElementById('importScheduleModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-lg transition">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition shadow-md">Upload & Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <script>
    // DATA JADWAL
    const schoolScheduleData = {
        'Senin': [
            { label: 'Jam ke-1 (08.00 - 08.35)', start: '08:00', end: '08:35' },
            { label: 'Jam ke-2 (08.35 - 09.10)', start: '08:35', end: '09:10' },
            { label: 'Jam ke-3 (09.10 - 09.45)', start: '09:10', end: '09:45' },
            { label: 'Jam ke-4 (10.00 - 10.35)', start: '10:00', end: '10:35' },
            { label: 'Jam ke-5 (10.35 - 11.10)', start: '10:35', end: '11:10' },
            { label: 'Jam ke-6 (11.10 - 11.45)', start: '11:10', end: '11:45' },
            { label: 'Jam ke-7 (12.45 - 13.25)', start: '12:45', end: '13:25' },
            { label: 'Jam ke-8 (13.25 - 14.00)', start: '13:25', end: '14:00' },
            { label: 'Jam ke-9 (14.00 - 14.35)', start: '14:00', end: '14:35' },
            { label: 'Jam ke-10 (14.35 - 15.10)', start: '14:35', end: '15:10' }
        ],
        'Selasa': [
            { label: 'Jam ke-1 (07.30 - 08.10)', start: '07:30', end: '08:10' },
            { label: 'Jam ke-2 (08.10 - 08.50)', start: '08:10', end: '08:50' },
            { label: 'Jam ke-3 (08.50 - 09.30)', start: '08:50', end: '09.30' },
            { label: 'Jam ke-4 (09.45 - 10.25)', start: '09:45', end: '10:25' },
            { label: 'Jam ke-5 (10.25 - 11.05)', start: '10:25', end: '11:05' },
            { label: 'Jam ke-6 (11.05 - 11.45)', start: '11:05', end: '11:45' },
            { label: 'Jam ke-7 (12.45 - 13.25)', start: '12:45', end: '13:25' },
            { label: 'Jam ke-8 (13.25 - 14.00)', start: '13:25', end: '14:00' },
            { label: 'Jam ke-9 (14.00 - 14.35)', start: '14:00', end: '14:35' },
            { label: 'Jam ke-10 (14.35 - 15.10)', start: '14:35', end: '15:10' }
        ],
        'Rabu': [
            { label: 'Jam ke-1 (07.15 - 07.55)', start: '07:15', end: '07:55' },
            { label: 'Jam ke-2 (07.55 - 08.35)', start: '07:55', end: '08:35' },
            { label: 'Jam ke-3 (08.35 - 09.15)', start: '08:35', end: '09:15' },
            { label: 'Jam ke-4 (09.30 - 10.10)', start: '09:30', end: '10:10' },
            { label: 'Jam ke-5 (10.10 - 10.50)', start: '10:10', end: '10:50' },
            { label: 'Jam ke-6 (10.50 - 11.30)', start: '10:50', end: '11:30' },
            { label: 'Jam ke-7 (12.30 - 13.10)', start: '12:30', end: '13:10' },
            { label: 'Jam ke-8 (13.10 - 13.50)', start: '13:10', end: '13:50' },
            { label: 'Jam ke-9 (13.50 - 14.30)', start: '13:50', end: '14:30' },
            { label: 'Jam ke-10 (14.30 - 15.10)', start: '14:30', end: '15:10' }
        ],
        'Kamis': [
            { label: 'Jam ke-1 (07.15 - 07.55)', start: '07:15', end: '07:55' },
            { label: 'Jam ke-2 (07.55 - 08.35)', start: '07:55', end: '08:35' },
            { label: 'Jam ke-3 (08.35 - 09.15)', start: '08:35', end: '09:15' },
            { label: 'Jam ke-4 (09.30 - 10.10)', start: '09:30', end: '10:10' },
            { label: 'Jam ke-5 (10.10 - 10.50)', start: '10:10', end: '10:50' },
            { label: 'Jam ke-6 (10.50 - 11.30)', start: '10:50', end: '11:30' },
            { label: 'Jam ke-7 (12.30 - 13.10)', start: '12:30', end: '13:10' },
            { label: 'Jam ke-8 (13.10 - 13.50)', start: '13:10', end: '13:50' },
            { label: 'Jam ke-9 (13.50 - 14.30)', start: '13:50', end: '14:30' },
            { label: 'Jam ke-10 (14.30 - 15.10)', start: '14:30', end: '15:10' }
        ],
        'Jumat': [
            { label: 'Jam ke-1 (07.15 - 07.55)', start: '07:15', end: '07:55' },
            { label: 'Jam ke-2 (07.55 - 08.35)', start: '07:55', end: '08:35' },
            { label: 'Jam ke-3 (08.35 - 09.15)', start: '08:35', end: '09:15' },
            { label: 'Jam ke-4 (09.30 - 10.10)', start: '09:30', end: '10:10' },
            { label: 'Jam ke-5 (10.10 - 10.50)', start: '10:10', end: '10:50' },
            { label: 'Jam ke-6 (10.50 - 11.25)', start: '10:50', end: '11:25' },
            { label: 'Jam ke-7 (12.40 - 13.15)', start: '12:40', end: '13:15' },
            { label: 'Jam ke-8 (13.15 - 13.50)', start: '13:15', end: '13:50' }
        ]
    };

    function updateDynamicTimeOptions() {
        var selectedDay = $('#day_select').val();
        var startSelect = $('#start_time_select');
        var endSelect = $('#end_time_select');
        
        var oldStart = startSelect.attr('data-old') || startSelect.val();
        var oldEnd = endSelect.attr('data-old') || endSelect.val();
        
        startSelect.empty().append('<option value="">-- Jam Mulai --</option>');
        endSelect.empty().append('<option value="">-- Jam Selesai --</option>');
        
        if (selectedDay && schoolScheduleData[selectedDay]) {
            var dailyTimes = schoolScheduleData[selectedDay];
            dailyTimes.forEach(function(item) {
                var isStartSelected = (oldStart === item.start) ? 'selected' : '';
                var isEndSelected = (oldEnd === item.end) ? 'selected' : '';
                startSelect.append('<option value="'+item.start+'" '+isStartSelected+'>'+item.label+'</option>');
                endSelect.append('<option value="'+item.end+'" '+isEndSelected+'>'+item.label+'</option>');
            });
        } else {
            startSelect.append('<option value="">-- Pilih Hari Dulu --</option>');
            endSelect.append('<option value="">-- Pilih Hari Dulu --</option>');
        }
        startSelect.attr('data-old', '');
        endSelect.attr('data-old', '');
    }

    $(document).ready(function() {
        $('#day_select').on('change', updateDynamicTimeOptions);
        updateDynamicTimeOptions();

        $('#guru_select').on('change', function() {
            var mapelGuru = $(this).find(':selected').data('mapel');
            var mapelInput = $('#mapel_input');
            if(mapelGuru) { mapelInput.val(mapelGuru); } 
            else { mapelInput.val(''); }
        });

        // INISIALISASI DATATABLES DENGAN PENGURUTAN OTOMATIS (WAKTU & HARI)
        var tableJadwal = $('#scheduleTable').DataTable({
            "pageLength": 10,
            "order": [[0, "asc"]], // Mengurutkan berdasarkan kolom pertama (index 0: Waktu & Hari) secara Ascending
            "dom": '<"flex flex-col md:flex-row justify-between items-center gap-4 mb-4"l f>rt<"mt-4"p>',
            "language": {
                "search": "Cari Cepat (Semua):", 
                "lengthMenu": "Tampilkan _MENU_ jadwal",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ jadwal",
                "paginate": { "first": "Awal", "last": "Akhir", "next": "Lanjut", "previous": "Kembali" },
                "emptyTable": "Belum ada jadwal yang didaftarkan pada putaran ini.",
                "zeroRecords": "Tidak ada jadwal yang cocok dengan pencarian."
            }
        });

        // FUNGSI PENCARIAN CUSTOM YANG SANGAT KETAT UNTUK KELAS, JURUSAN & ROMBEL
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'scheduleTable') return true;

                var hariFilter = $('#filterHari').val().toLowerCase();
                var guruFilter = $('#filterGuru').val().toLowerCase();
                
                var kelasVal = $('#filterKelas').val(); // Contoh: "X ", "XI ", "XII "
                var jurusanVal = $('#filterJurusan').val(); // Contoh: "RPL", "TKJ"
                var rombelVal = $('#filterRombel').val(); // Contoh: "1", "2"

                // Ambil teks dari kolom tabel yang sesuai
                var kolomHariWaktu = data[0].toLowerCase();
                var kolomPelajaranGuru = data[1].toLowerCase();
                
                // Ambil teks kolom lokasi (misal: "XI RPL 1")
                var kolomLokasi = data[2] ? data[2].toUpperCase().replace(/\s+/g, ' ').trim() : ""; 

                // 1. Filter Input Text (Guru/Pelajaran)
                if (guruFilter && kolomPelajaranGuru.indexOf(guruFilter) === -1) {
                    return false;
                }

                // 2. Filter Dropdown Hari
                if (hariFilter && kolomHariWaktu.indexOf(hariFilter) === -1) {
                    return false;
                }

                // 3. FILTER LOKASI (Kelas + Jurusan + Rombel) YANG SUPER KETAT
                if (kelasVal || jurusanVal || rombelVal) {
                    
                    // Bersihkan nilai kelas dari spasi/angka (mengambil romawinya saja)
                    var romawi = "";
                    if (kelasVal.includes("X") && !kelasVal.includes("XI")) romawi = "X";
                    if (kelasVal.includes("XI") && !kelasVal.includes("XII")) romawi = "XI";
                    if (kelasVal.includes("XII")) romawi = "XII";

                    var regexPattern = "";
                    
                    if (romawi) {
                        regexPattern += "\\b" + romawi + "\\b";
                    }
                    
                    if (jurusanVal) {
                        if (regexPattern) regexPattern += ".*?"; // beri jarak jika ada romawi sebelumnya
                        regexPattern += "\\b" + jurusanVal + "\\b";
                    }
                    
                    if (rombelVal) {
                        if (regexPattern) regexPattern += ".*?"; // beri jarak jika ada jurusan sebelumnya
                        regexPattern += "\\b" + rombelVal + "\\b";
                    }

                    // Jika ada filter yang aktif, tapi lokasi tidak cocok dengan susunan Regex
                    if (regexPattern !== "") {
                        var regex = new RegExp(regexPattern);
                        if (!kolomLokasi.match(regex)) {
                            return false; 
                        }
                    }
                }

                return true; 
            }
        );

        // TRIGGER SAAT FILTER DIGANTI
        $('#filterHari, #filterKelas, #filterJurusan, #filterRombel').on('change', function() {
            tableJadwal.draw();
        });
        
        $('#filterGuru').on('keyup change', function() {
            tableJadwal.draw();
        });
    });
</script>
</body>
</html>