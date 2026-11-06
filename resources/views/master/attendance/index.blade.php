<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi - SCP School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- Hapus CSS dan JS Buttons karena tidak digunakan lagi -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        .dataTables_filter input {
            border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 4px 12px;
            outline: none; margin-left: 8px; font-size: 14px;
        }
        .dataTables_filter input:focus { border-color: #4f46e5; }
        input[type="month"]::-webkit-calendar-picker-indicator,
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden font-sans text-slate-800">

    @include('layouts.sidebar')

    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200 p-5 flex justify-between items-center z-10">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Laporan Presensi Siswa</h1>
                <p class="text-xs text-slate-500 font-medium mt-1">Pantau rekapitulasi ketidakhadiran harian maupun bulanan</p>
            </div>
            <div class="text-right hidden md:block">
                <p class="text-sm font-bold text-slate-700">{{ Auth::user()->fullname ?? 'Admin' }}</p>
                <p class="text-[11px] font-semibold text-slate-400">ID Akses: {{ Auth::user()->role_id ?? '1' }}</p>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
                
                <!-- BARIS 1: FILTER WAKTU (HARIAN ATAU BULANAN) KE SERVER -->
                <form action="{{ route('waka.attendance-reports') }}" method="GET" class="flex flex-wrap items-end gap-4 pb-5 border-b border-slate-100">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Pilih Tanggal (Harian)</label>
                        <input type="date" name="date" id="filterDate" value="{{ request('date') }}" class="w-[170px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 outline-none focus:border-blue-500 shadow-sm transition cursor-pointer">
                    </div>
                    
                    <div class="pb-2 text-[10px] font-extrabold text-slate-400">ATAU</div>
                    
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Bulan & Tahun (Rekap)</label>
                        <input type="month" name="month" id="filterMonth" value="{{ request('date') ? '' : request('month', $month ?? date('Y-m')) }}" class="w-[170px] px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold text-slate-700 outline-none focus:border-blue-500 shadow-sm transition cursor-pointer">
                    </div>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition shadow-md text-sm">
                        Terapkan Filter
                    </button>
                    
                    @if(request()->has('date') || request()->has('month'))
                        <a href="{{ route('waka.attendance-reports') }}" class="text-slate-500 hover:text-red-500 text-sm font-bold underline px-2">Reset Filter</a>
                    @endif
                </form>

                <!-- BARIS 2: FILTER KELAS -->
                <div class="flex flex-col md:flex-row gap-4 pt-5">
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Tingkat Kelas</label>
                        <select id="filterKelasPresensi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-blue-500 cursor-pointer">
                            <option value="">-- Semua Kelas --</option>
                            <option value="X ">10 (X)</option>
                            <option value="XI ">11 (XI)</option>
                            <option value="XII ">12 (XII)</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Jurusan</label>
                        <select id="filterJurusanPresensi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-blue-500 cursor-pointer">
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
                        <select id="filterRombelPresensi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-blue-500 cursor-pointer">
                            <option value="">-- Semua Rombel --</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- TABEL REKAPITULASI -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 overflow-x-auto">
                <table id="reportTable" class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="py-3 px-4 text-[11px] font-extrabold text-slate-400 uppercase">NISN</th>
                            <th class="py-3 px-4 text-[11px] font-extrabold text-slate-400 uppercase">Nama Siswa</th>
                            <th class="py-3 px-4 text-[11px] font-extrabold text-slate-400 uppercase text-center">Kelas</th>
                            <th class="py-3 px-4 text-[11px] font-extrabold text-emerald-500 uppercase text-center">Hadir</th>
                            <th class="py-3 px-4 text-[11px] font-extrabold text-orange-500 uppercase text-center">Sakit</th>
                            <th class="py-3 px-4 text-[11px] font-extrabold text-blue-500 uppercase text-center">Izin</th>
                            <th class="py-3 px-4 text-[11px] font-extrabold text-red-500 uppercase text-center">Alpha</th>
                            <th class="py-3 px-4 text-[11px] font-extrabold text-slate-700 uppercase text-center">Total Absen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if(isset($students) && count($students) > 0)
                            @foreach($students as $s)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3 px-4 text-sm font-semibold text-slate-600">{{ $s->nisn_nip ?? '-' }}</td>
                                    <td class="py-3 px-4 text-sm font-extrabold text-slate-800">{{ $s->fullname }}</td>
                                    <td class="py-3 px-4 text-xs font-bold text-center text-indigo-600 bg-indigo-50/50">{{ $s->class_name }}</td>
                                    <td class="py-3 px-4 text-sm font-bold text-center {{ $s->hadir > 0 ? 'text-emerald-600' : 'text-slate-300' }}">{{ $s->hadir }}</td>
                                    <td class="py-3 px-4 text-sm font-bold text-center {{ $s->sakit > 0 ? 'text-orange-600' : 'text-slate-300' }}">{{ $s->sakit }}</td>
                                    <td class="py-3 px-4 text-sm font-bold text-center {{ $s->izin > 0 ? 'text-blue-600' : 'text-slate-300' }}">{{ $s->izin }}</td>
                                    <td class="py-3 px-4 text-sm font-bold text-center {{ $s->alpha > 0 ? 'text-red-600' : 'text-slate-300' }}">{{ $s->alpha }}</td>
                                    <td class="py-3 px-4 text-sm font-extrabold text-center {{ $s->total_absen >= 3 ? 'text-red-600 bg-red-50' : 'text-slate-700' }}">{{ $s->total_absen }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        $(document).ready(function() {
            $('#filterDate').on('change', function() {
                if($(this).val()) { $('#filterMonth').val(''); }
            });
            $('#filterMonth').on('change', function() {
                if($(this).val()) { $('#filterDate').val(''); }
            });

            // INISIALISASI DATATABLES TANPA TOMBOL EXPORT
            var tablePresensi = $('#reportTable').DataTable({
                "pageLength": 25,
                // Hilangkan huruf 'B' dari dom agar buttons tidak muncul
                "dom": '<"flex flex-col md:flex-row justify-between items-center gap-4 mb-4"f>rt<"flex flex-col md:flex-row justify-between items-center mt-4"i p>',
                "language": {
                    "search": "Cari Nama/NISN:",
                    "lengthMenu": "Tampilkan _MENU_ data",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": { "first": "Awal", "last": "Akhir", "next": "Lanjut", "previous": "Kembali" },
                    "emptyTable": "Belum ada rekap data presensi pada rentang waktu ini."
                }
            });

            function applyPresensiFilters() {
                var kelas = $('#filterKelasPresensi').val();
                var jurusan = $('#filterJurusanPresensi').val();
                var rombel = $('#filterRombelPresensi').val();
                
                var searchLokasi = (kelas + ' ' + jurusan + ' ' + rombel).trim();
                searchLokasi = searchLokasi.replace(/\s+/g, ' ');

                tablePresensi.column(2).search(searchLokasi).draw();
            }

            $('#filterKelasPresensi, #filterJurusanPresensi, #filterRombelPresensi').on('change', applyPresensiFilters);
        });
    </script>
</body>
</html>