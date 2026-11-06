<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal - SCP School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden font-sans text-slate-800">

    <!-- SIDEBAR KIRI -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col h-full z-20 shadow-sm hidden md:flex">
        <div class="p-6 flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center font-bold text-lg shadow-md text-white">SCP</div>
            <div>
                <h2 class="font-extrabold text-sm tracking-tight text-slate-800">SMKN 1 Bantul</h2>
                <p class="text-[11px] font-semibold text-blue-600 uppercase tracking-widest">Waka Panel</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-6">
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition rounded-xl text-sm">
                <span class="text-lg">📊</span> Dashboard Utama
            </a>

            <div>
                <p class="px-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Akademik</p>
                <div class="space-y-1">
                    <a href="{{ route('schedules.index') }}" class="flex items-center gap-3 px-3 py-2 bg-indigo-50 text-indigo-700 border border-indigo-100 shadow-sm font-bold rounded-lg text-sm">
                        <span>📅</span> Jadwal Mengajar
                    </a>
                    <a href="{{ route('waka.journals') }}" class="flex items-center gap-3 px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition rounded-lg text-sm">
                        <span>📝</span> Jurnal Mengajar
                    </a>
                </div>
            </div>

            <div>
                <p class="px-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Laporan</p>
                <a href="{{ route('histories.cycles') }}" class="flex items-center gap-3 px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition rounded-lg text-sm">
                    <span>⏳</span> History Putaran
                </a>
            </div>

            <div>
                <p class="px-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Data Master</p>
                <div class="space-y-1">
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition rounded-lg text-sm">
                        <span>👥</span> Pengguna & Role
                    </a>
                    <a href="{{ route('classes.index') }}" class="flex items-center gap-3 px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition rounded-lg text-sm">
                        <span>🏫</span> Data Kelas
                    </a>
                    <a href="{{ route('rooms.index') }}" class="flex items-center gap-3 px-3 py-2 text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition rounded-lg text-sm">
                        <span>🚪</span> Data Ruangan
                    </a>
                </div>
            </div>
        </div>

        <div class="p-5 border-t border-slate-100 bg-slate-50/50">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex justify-center items-center gap-2 bg-white hover:bg-red-50 text-slate-600 hover:text-red-600 py-2.5 rounded-xl text-sm font-bold transition border border-slate-200 hover:border-red-200 shadow-sm">
                    <span>🚪</span> Keluar Sistem
                </button>
            </form>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
        
        <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200 p-5 flex justify-between items-center z-10">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                    <a href="{{ route('schedules.index') }}" class="text-slate-400 hover:text-indigo-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    </a>
                    Edit Jadwal Mengajar
                </h1>
                <p class="text-xs font-medium mt-1 text-slate-500 ml-8">Perbarui detail jadwal yang sudah terdaftar di sistem.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ route('schedules.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg text-sm font-bold transition border border-slate-200 shadow-sm">
                    Batal
                </a>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 flex justify-center">
            
            <div class="w-full max-w-2xl">
                
                @if(session('error')) <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold flex items-center gap-3">❌ {{ session('error') }}</div> @endif
                @if($errors->any()) <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold"><ul class="list-disc pl-5">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul></div> @endif

                <!-- Card Form Edit -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <h3 class="text-lg font-extrabold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4">
                        <span>✏️</span> Form Pembaruan Jadwal
                    </h3>
                    
                    <form action="{{ route('schedules.update', $schedule->id) }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Guru Pengampu</label>
                            <select name="teacher_id" id="guru_select" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                                <option value="">-- Pilih Guru --</option>
                                @foreach($users as $t) 
                                    <option value="{{ $t->id }}" data-mapel="{{ $t->subject }}" {{ old('teacher_id', $schedule->teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->fullname }}</option> 
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Kelas</label>
                                <select name="class_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                                    <option value="">-- Kelas --</option>
                                    @foreach($classes as $c) 
                                        <option value="{{ $c->id }}" {{ old('class_id', $schedule->class_id) == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option> 
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Ruangan</label>
                                <select name="room_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                                    <option value="">-- Ruang --</option>
                                    @foreach($rooms as $r) 
                                        <option value="{{ $r->id }}" {{ old('room_id', $schedule->room_id) == $r->id ? 'selected' : '' }}>{{ $r->room_name }}</option> 
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5">Mata Pelajaran</label>
                            <input type="text" name="subject" id="mapel_input" value="{{ old('subject', $schedule->subject) }}" required placeholder="Pilih guru terlebih dahulu..." readonly class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-bold uppercase text-indigo-700 outline-none cursor-not-allowed">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Hari</label>
                                <select name="day_of_week" id="day_select" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition cursor-pointer">
                                    <option value="">-- Hari --</option>
                                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat'] as $hari)
                                        <option value="{{ $hari }}" {{ old('day_of_week', $schedule->day_of_week) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Siklus</label>
                                <select name="cycle_type" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                                    <option value="1" {{ old('cycle_type', $schedule->cycle_type) == '1' || old('cycle_type', $schedule->cycle_type) == 'Putaran 1' ? 'selected' : '' }}>Putaran 1</option>
                                    <option value="2" {{ old('cycle_type', $schedule->cycle_type) == '2' || old('cycle_type', $schedule->cycle_type) == 'Putaran 2' ? 'selected' : '' }}>Putaran 2</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Jam Mulai Sesi</label>
                                <select name="start_time" id="start_time_select" data-old="{{ old('start_time', \Carbon\Carbon::parse($schedule->start_time)->format('H:i')) }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium cursor-pointer focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                                    <option value="">-- Pilih Hari Dulu --</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5">Jam Selesai Sesi</label>
                                <select name="end_time" id="end_time_select" data-old="{{ old('end_time', \Carbon\Carbon::parse($schedule->end_time)->format('H:i')) }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium cursor-pointer focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                                    <option value="">-- Pilih Hari Dulu --</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 mt-2 border-t border-slate-100 flex gap-3">
                            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-4 rounded-xl transition shadow-md flex justify-center items-center gap-2">
                                💾 Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // DATA WAKTU RESMI SMKN 1 BANTUL (SENIN - JUMAT)
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
                { label: 'Jam ke-3 (08.50 - 09.30)', start: '08:50', end: '09:30' },
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
            // Event listener ketika hari diganti
            $('#day_select').on('change', updateDynamicTimeOptions);
            
            // Panggil saat halaman dimuat agar jam yang tersimpan di database langsung terpilih secara otomatis
            updateDynamicTimeOptions();

            // Auto-fill mata pelajaran ketika guru dipilih
            $('#guru_select').on('change', function() {
                var mapelGuru = $(this).find(':selected').data('mapel');
                var mapelInput = $('#mapel_input');
                if(mapelGuru) {
                    mapelInput.val(mapelGuru);
                } else {
                    mapelInput.val('');
                }
            });
        });
    </script>
</body>
</html>