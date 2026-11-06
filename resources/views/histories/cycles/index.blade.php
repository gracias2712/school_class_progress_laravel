<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan History Putaran - SCP School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden font-sans text-slate-800">

    <!-- PANGGIL SIDEBAR DARI PUSAT -->
    @include('layouts.sidebar')

    <!-- KONTEN UTAMA -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Header Atas -->
        <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200 p-5 flex justify-between items-center z-10">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                    Jejak Aktivitas Siklus Putaran
                </h1>
                <p class="text-xs text-slate-500 font-medium mt-1">Laporan rekam jejak kapan sistem mengubah jadwal blok mingguan</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-700">{{ Auth::user()->fullname }}</p>
                    <p class="text-[11px] font-semibold text-slate-400">ID Akses: {{ Auth::user()->role_id }}</p>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-5xl mx-auto">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                        <h3 class="font-extrabold text-slate-700 uppercase tracking-wide text-sm">📅 Log Aktivitas Terbaru</h3>
                        <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-bold">{{ count($histories) }} Rekaman</span>
                    </div>

                    <div class="p-2">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white border-b border-slate-100">
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase">Waktu Perubahan</th>
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase">Perubahan Siklus</th>
                                    <th class="py-4 px-6 text-[11px] font-extrabold text-slate-400 uppercase">Oleh (Eksekutor)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($histories as $h)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-5 px-6">
                                            <div class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($h->created_at)->format('d F Y') }}</div>
                                            <div class="text-xs font-bold text-slate-400 mt-1 flex items-center gap-1">
                                                <span>⏱️</span> Pukul {{ \Carbon\Carbon::parse($h->created_at)->format('H:i') }} WIB
                                            </div>
                                        </td>
                                        <td class="py-5 px-6">
                                            <div class="flex items-center gap-3">
                                                <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-md text-xs font-extrabold border border-slate-200">
                                                    Putaran {{ str_replace('Putaran ', '', $h->from_cycle) }}
                                                </span>
                                                <span class="text-slate-300 font-bold">➡️</span>
                                                <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-md text-xs font-extrabold border border-purple-200 shadow-sm animate-pulse">
                                                    Putaran {{ str_replace('Putaran ', '', $h->to_cycle) }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-2 font-medium">Info: {{ $h->change_reason }}</p>
                                        </td>
                                        <td class="py-5 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shadow-sm">
                                                    {{ substr(optional($h->user)->fullname, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="font-extrabold text-slate-700 text-sm">{{ optional($h->user)->fullname }}</p>
                                                    <span class="text-[10px] text-slate-400 font-bold bg-slate-100 px-2 py-0.5 rounded mt-0.5 inline-block">Waka Kurikulum</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-16 text-center text-slate-400 text-sm font-medium">
                                            <div class="text-4xl mb-3 opacity-20">📭</div>
                                            Belum ada rekaman perubahan putaran dari sistem.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </main>
</body>
</html>