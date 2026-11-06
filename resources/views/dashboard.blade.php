<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Waka - SCP School</title>
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
    <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
        
        <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200 p-5 flex justify-between items-center z-10">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Overview</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-700">{{ Auth::user()->fullname ?? 'Bapak Waka Kurikulum' }}</p>
                    <p class="text-[11px] font-semibold text-slate-400">ID Akses: {{ Auth::user()->role_id ?? 1 }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold shadow-sm border border-indigo-200">
                    {{ substr(Auth::user()->fullname ?? 'B', 0, 1) }}
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            
            @if(session('success')) 
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold">
                    {{ session('success') }}
                </div> 
            @endif
            
            @if(session('error')) 
                <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold">
                    {{ session('error') }}
                </div> 
            @endif

            <!-- STATISTIK (4 Card) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Siswa</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ collect($siswaCount ?? [])->count() > 0 ? $siswaCount : \Illuminate\Support\Facades\DB::table('users')->where('role_id', 4)->count() }}</h3>
                    <p class="text-[11px] font-medium text-emerald-500 mt-2">Data sistem saat ini</p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Guru</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ collect($guruCount ?? [])->count() > 0 ? $guruCount : \Illuminate\Support\Facades\DB::table('users')->where('role_id', 2)->count() }}</h3>
                    <p class="text-[11px] font-medium text-emerald-500 mt-2">Data sistem saat ini</p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Kelas</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ collect($kelasCount ?? [])->count() > 0 ? $kelasCount : \Illuminate\Support\Facades\DB::table('classes')->count() }}</h3>
                    <p class="text-[11px] font-medium text-emerald-500 mt-2">Data sistem saat ini</p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Ruangan</p>
                    <h3 class="text-3xl font-black text-slate-800">{{ collect($ruanganCount ?? [])->count() > 0 ? $ruanganCount : \Illuminate\Support\Facades\DB::table('rooms')->count() }}</h3>
                    <p class="text-[11px] font-medium text-emerald-500 mt-2">Data sistem saat ini</p>
                </div>

            </div>

            <!-- LOGIKA CEK STATUS KUNCI -->
            @php
                $statusController = isset($isLocked) ? $isLocked : null;
                
                if($statusController === null) {
                    try {
                        $dbValue = \Illuminate\Support\Facades\DB::table('configs')->where('key', 'is_schedule_locked')->value('value');
                        $statusController = $dbValue;
                    } catch(\Exception $e) {
                        $statusController = false; 
                    }
                }

                $lockStatus = ($statusController == 1 || $statusController == '1' || $statusController == true || $statusController === 'true');
            @endphp

            <!-- KONTROL WAKA -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Card Siklus Pembelajaran -->
                <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm relative overflow-hidden flex flex-col justify-between h-full">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-blue-50 rounded-full opacity-50"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-extrabold text-slate-800">Siklus<br>Pembelajaran</h3>
                            <span class="bg-blue-50 text-blue-600 border border-blue-100 font-bold px-4 py-2 rounded-full text-xs shadow-sm">
                                Putaran {{ $activeCycle ?? '1' }}
                            </span>
                        </div>
                        
                        <p class="text-sm text-slate-500 font-medium mb-8 leading-relaxed">
                            @if($lockStatus)
                                <span class="text-red-500 font-bold">Siklus Terkunci:</span> Jadwal sedang dikunci. Buka kunci terlebih dahulu untuk mengganti putaran.
                            @else
                                Sesuaikan siklus putaran jika minggu berganti. Aplikasi Mobile akan menyesuaikan otomatis.
                            @endif
                        </p>
                    </div>
                    
                    <form action="{{ route('waka.toggleCycle') }}" method="POST" class="relative z-10 mt-auto">
                        @csrf
                        <input type="hidden" name="active_cycle" value="{{ ($activeCycle ?? '1') == '1' ? '2' : '1' }}">
                        <button type="submit" 
                                {{ $lockStatus ? 'disabled' : '' }}
                                class="w-full font-bold py-4 rounded-xl transition shadow-md text-center
                                {{ $lockStatus ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'bg-slate-800 hover:bg-slate-900 text-white' }}">
                            Ubah ke Putaran {{ ($activeCycle ?? '1') == '1' ? '2' : '1' }}
                        </button>
                    </form>
                </div>

                <!-- Card Keamanan Jadwal -->
                <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm relative overflow-hidden flex flex-col justify-between h-full">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-emerald-50 rounded-full opacity-50"></div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-extrabold text-slate-800">Keamanan Jadwal</h3>
                            @if($lockStatus)
                                <span class="bg-red-50 text-red-600 border border-red-200 font-bold px-4 py-2 rounded-full text-xs shadow-sm">Terkunci</span>
                            @else
                                <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 font-bold px-4 py-2 rounded-full text-xs shadow-sm">Terbuka</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 font-medium mb-8 leading-relaxed">Kunci jadwal jika penyusunan selesai untuk mencegah perubahan tak terduga.</p>
                    </div>

                    <form action="{{ route('waka.toggleLock') }}" method="POST" class="relative z-10 mt-auto">
                        @csrf
                        @if($lockStatus)
                            <button type="submit" class="w-full text-center bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-bold py-4 rounded-xl transition shadow-sm">
                                Buka Jadwal Sekarang
                            </button>
                        @else
                            <button type="submit" class="w-full text-center bg-red-50 hover:bg-red-100 border border-red-200 text-red-600 font-bold py-4 rounded-xl transition shadow-sm">
                                Kunci Jadwal Sekarang
                            </button>
                        @endif
                    </form>
                </div>

            </div>
        </div>
    </main>
</body>
</html>