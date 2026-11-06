<aside class="w-64 bg-white border-r border-slate-200 flex flex-col h-full z-20 shadow-sm hidden md:flex shrink-0">
    <!-- Logo & Title -->
    <div class="p-6 flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center font-bold text-lg shadow-md text-white">SCP</div>
        <div>
            <h2 class="font-extrabold text-sm tracking-tight text-slate-800">SMKN 1 Bantul</h2>
            <p class="text-[11px] font-semibold text-blue-600 uppercase tracking-widest">Waka Panel</p>
        </div>
    </div>

    <!-- Menu Navigasi -->
    <div class="flex-1 overflow-y-auto py-6 px-4 space-y-6">
        
        <!-- Main Dashboard -->
        <a href="/dashboard" class="block px-4 py-3 {{ request()->is('dashboard') ? 'bg-blue-50 text-blue-700 border border-blue-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition' }} rounded-xl text-sm">
            Dashboard Utama
        </a>

        <!-- MANAJEMEN AKADEMIK -->
        <div>
            <p class="px-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Akademik</p>
            <div class="space-y-1">
                <a href="{{ route('schedules.index') }}" class="block px-3 py-2 {{ request()->routeIs('schedules.*') ? 'bg-indigo-50 text-indigo-700 border border-indigo-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    Jadwal Mengajar
                </a>
                <a href="{{ route('waka.journals') }}" class="block px-3 py-2 {{ request()->routeIs('waka.journals') ? 'bg-pink-50 text-pink-700 border border-pink-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    Jurnal Mengajar
                </a>
            </div>
        </div>

        <!-- LAPORAN & REKAP -->
        <div>
            <p class="px-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Laporan</p>
            <div class="space-y-1">
                <a href="{{ route('histories.cycles') }}" class="block px-3 py-2 {{ request()->routeIs('histories.*') ? 'bg-purple-50 text-purple-700 border border-purple-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    History Putaran
                </a>
                
                <!-- LAPORAN PRESENSI -->
                <a href="{{ route('waka.attendance-reports') }}" class="block px-3 py-2 {{ request()->routeIs('waka.attendance-reports') ? 'bg-orange-50 text-orange-700 border border-orange-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    Laporan Presensi
                </a>
            </div>
        </div>

        <!-- DATA MASTER -->
        <div>
            <p class="px-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-3">Data Master</p>
            <div class="space-y-1">
                <a href="{{ route('users.guru') }}" class="block px-3 py-2 {{ request()->routeIs('users.guru') ? 'bg-teal-50 text-teal-700 border border-teal-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    Data Guru
                </a>
                <a href="{{ route('users.walikelas') }}" class="block px-3 py-2 {{ request()->routeIs('users.walikelas') ? 'bg-amber-50 text-amber-700 border border-amber-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    Data Wali Kelas
                </a>
                <a href="{{ route('users.bk') }}" class="block px-3 py-2 {{ request()->routeIs('users.bk') ? 'bg-pink-50 text-pink-700 border border-pink-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    Data Guru BK
                </a>
                <a href="{{ route('users.siswa') }}" class="block px-3 py-2 {{ request()->routeIs('users.siswa') ? 'bg-emerald-50 text-emerald-700 border border-emerald-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    Data Siswa
                </a>
                <a href="{{ route('users.orangtua') }}" class="block px-3 py-2 {{ request()->routeIs('users.orangtua') ? 'bg-indigo-50 text-indigo-700 border border-indigo-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    Data Orang Tua
                </a>

                <!-- MENU MASTER LAINNYA -->
                <a href="{{ route('classes.index') }}" class="block px-3 py-2 {{ request()->routeIs('classes.*') ? 'bg-blue-50 text-blue-700 border border-blue-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm mt-3">
                    Data Kelas
                </a>
                <a href="{{ route('rooms.index') }}" class="block px-3 py-2 {{ request()->routeIs('rooms.*') ? 'bg-amber-50 text-amber-700 border border-amber-100 shadow-sm font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600 font-medium transition' }} rounded-lg text-sm">
                    Data Ruangan
                </a>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <div class="p-5 border-t border-slate-100 bg-slate-50/50">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full block text-center bg-white hover:bg-red-50 text-slate-600 hover:text-red-600 py-2.5 rounded-xl text-sm font-bold transition border border-slate-200 hover:border-red-200 shadow-sm">
                Keluar Sistem
            </button>
        </form>
    </div>
</aside>