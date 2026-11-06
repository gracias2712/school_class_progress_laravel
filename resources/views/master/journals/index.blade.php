<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantau Jurnal Mengajar - SCP School</title>
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
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Monitoring Jurnal Mengajar</h1>
                <p class="text-xs text-slate-500 font-medium mt-1">Pantau rekapitulasi harian dan kelalaian pengisian jurnal guru</p>
            </div>
            <div class="flex items-center gap-4">
                <form action="{{ route('waka.journals') }}" method="GET" class="flex items-center gap-2 bg-white border border-slate-200 rounded-lg px-3 py-1.5 shadow-sm">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tgl:</label>
                    <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="bg-transparent text-sm font-bold text-pink-700 outline-none cursor-pointer">
                </form>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6">
            
            @if(count($missingJournals) > 0)
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-red-100 text-red-600 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-extrabold text-red-800 uppercase tracking-wide">Peringatan Kelalaian ({{ count($missingJournals) }} Jadwal)</h3>
                        <p class="text-xs text-red-600 font-medium mt-1">Daftar kelas di bawah ini sudah selesai mengajar (>30 menit) namun jurnalnya belum diisi:</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($missingJournals as $mj)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-red-200 text-slate-700 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                {{ optional($mj->teacher)->fullname }} ({{ optional($mj->classe)->class_name }})
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Pencarian Bebas</label>
                        <input type="text" id="filterGuruJurnal" placeholder="Ketik Nama Guru atau Materi..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm outline-none focus:border-pink-500">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide text-indigo-600">Status Jurnal</label>
                        <select id="filterStatusJurnal" class="w-full px-3 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-lg text-sm font-bold outline-none focus:border-indigo-500 cursor-pointer">
                            <option value="">-- Semua Status --</option>
                            <option value="Terisi">Sudah Diisi</option>
                            <option value="Belum Diisi">Belum Diisi / Berlangsung</option>
                            <option value="Terlewat">Kelalaian (Bolong)</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-4 pt-2 border-t border-slate-100">
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Tingkat Kelas</label>
                        <select id="filterKelasJurnal" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-pink-500 cursor-pointer">
                            <option value="">-- Semua Kelas --</option>
                            <option value="X ">10 (X)</option>
                            <option value="XI ">11 (XI)</option>
                            <option value="XII ">12 (XII)</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-[11px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide">Jurusan</label>
                        <select id="filterJurusanJurnal" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-pink-500 cursor-pointer">
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
                        <select id="filterRombelJurnal" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-medium outline-none focus:border-pink-500 cursor-pointer">
                            <option value="">-- Semua Rombel --</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-extrabold text-slate-700 uppercase tracking-wide text-sm">
                        Jadwal & Status Jurnal Harian
                    </h3>
                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">{{ $terisiCount }} Terisi / {{ count($allSchedules) }} Total</span>
                </div>
                
                <div class="p-4 overflow-x-auto">
                    <table id="journalTable" class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="bg-white border-b border-slate-100">
                                <th class="py-4 px-5 text-[11px] font-extrabold text-slate-400 uppercase">Waktu & Kelas</th>
                                <th class="py-4 px-5 text-[11px] font-extrabold text-slate-400 uppercase">Guru & Materi</th>
                                <th class="py-4 px-5 text-[11px] font-extrabold text-slate-400 uppercase text-center">Status / Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($allSchedules as $item)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-4 px-5">
                                        <div class="text-xs font-bold text-indigo-600 mb-1">
                                            {{ \Carbon\Carbon::parse($item->schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->schedule->end_time)->format('H:i') }}
                                        </div>
                                        <div class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-extrabold rounded">
                                            {{ optional($item->schedule->classe)->class_name }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-5">
                                        <p class="font-extrabold text-slate-800 text-sm">{{ optional($item->schedule->teacher)->fullname }}</p>
                                        <p class="text-[11px] font-medium mt-1 truncate max-w-[200px] {{ $item->journal ? 'text-slate-500' : 'text-red-400 italic' }}" title="{{ $item->journal ? $item->journal->topic_description : 'Belum diisi' }}">
                                            {{ $item->journal ? $item->journal->topic_description : 'Materi belum diinput...' }}
                                        </p>
                                    </td>
                                    <td class="py-4 px-5 text-center">
                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                            @if($item->status == 'Terisi')
                                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-md text-[10px] font-extrabold border border-emerald-200 uppercase">Terisi</span>
                                                @if($item->journal->photo_path)
                                                    <a href="{{ asset('storage/' . $item->journal->photo_path) }}" target="_blank" class="inline-block mt-1 px-3 py-1 bg-blue-500 text-white rounded text-[10px] font-bold hover:bg-blue-600 transition">
                                                        Lihat Foto
                                                    </a>
                                                @endif
                                            @elseif($item->status == 'Terlewat')
                                                <span class="px-2.5 py-1 bg-red-50 text-red-600 rounded-md text-[10px] font-extrabold border border-red-200 uppercase">Terlewat</span>
                                            @else
                                                <span class="px-2.5 py-1 bg-amber-50 text-amber-600 rounded-md text-[10px] font-extrabold border border-amber-200 uppercase">Belum Diisi</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
    
    <script>
    $(document).ready(function() {
        var tableJurnal = $('#journalTable').DataTable({
            "pageLength": 10,
            "dom": '<"flex flex-col md:flex-row justify-between items-center gap-4 mb-4"l f>rt<"mt-4"p>',
            "language": {
                "search": "Cari (Semua):",
                "lengthMenu": "Tampilkan _MENU_",
                "paginate": { "first": "Awal", "last": "Akhir", "next": "Lanjut", "previous": "Kembali" },
                "emptyTable": "Belum ada jadwal mengajar pada tanggal ini.",
                "zeroRecords": "Tidak ada data yang cocok dengan filter."
            }
        });

        function applyJurnalFilters() {
            var guru   = $('#filterGuruJurnal').val();
            var status = $('#filterStatusJurnal').val();
            
            var kelas   = $('#filterKelasJurnal').val();
            var jurusan = $('#filterJurusanJurnal').val();
            var rombel  = $('#filterRombelJurnal').val();
            
            var searchLokasi = (kelas + ' ' + jurusan + ' ' + rombel).trim();
            searchLokasi = searchLokasi.replace(/\s+/g, ' ');

            tableJurnal.column(1).search(guru);
            tableJurnal.column(0).search(searchLokasi);
            tableJurnal.column(2).search(status);
            
            tableJurnal.draw();
        }

        $('#filterGuruJurnal, #filterStatusJurnal').on('input change', applyJurnalFilters);
        $('#filterKelasJurnal, #filterJurusanJurnal, #filterRombelJurnal').on('change', applyJurnalFilters);
    });
    </script>
</body>
</html>