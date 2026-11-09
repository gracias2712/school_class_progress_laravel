<aside id="waka-sidebar" aria-label="Menu navigasi utama" inert class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-slate-200 flex flex-col h-screen h-[100dvh] shadow-sm shrink-0 transform -translate-x-full transition-transform duration-300 md:static md:z-20 md:h-full md:translate-x-0">
    <!-- Logo & Title -->
    <div class="p-6 flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white">
        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center font-bold text-lg shadow-md text-white">SCP</div>
        <div>
            <h2 class="font-extrabold text-sm tracking-tight text-slate-800">SMKN 1 Bantul</h2>
            <p class="text-[11px] font-semibold text-blue-600 uppercase tracking-widest">Waka Panel</p>
        </div>
        <button id="sidebar-close" type="button" aria-label="Tutup menu navigasi" class="ml-auto inline-flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 hover:bg-blue-50 hover:text-blue-700 md:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <!-- Menu Navigasi -->
    <div class="min-h-0 flex-1 overflow-y-auto py-6 px-4 space-y-6">
        
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
    <div class="mt-auto flex-none p-4 border-t border-slate-200 bg-white">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full block text-center bg-white hover:bg-red-50 text-slate-700 hover:text-red-600 py-3 rounded-xl text-sm font-bold transition border border-slate-200 hover:border-red-200 shadow-sm">
                Keluar Sistem
            </button>
        </form>
    </div>
</aside>

<button id="sidebar-overlay" type="button" aria-label="Tutup menu navigasi" class="hidden fixed inset-0 z-20 bg-slate-900/40 md:hidden"></button>

<button id="sidebar-toggle" type="button" aria-controls="waka-sidebar" aria-expanded="false" aria-label="Buka menu navigasi" class="absolute top-3 left-4 z-40 inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-bold text-blue-700 shadow-lg md:hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <circle cx="12" cy="5" r="2" />
        <circle cx="12" cy="12" r="2" />
        <circle cx="12" cy="19" r="2" />
    </svg>
    Menu
</button>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('waka-sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarClose = document.getElementById('sidebar-close');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const desktopViewport = window.matchMedia('(min-width: 768px)');
        const pageHeader = document.querySelector('main > header');
        const pageScrollAreas = Array.from(document.querySelectorAll('main .overflow-y-auto'));

        if (pageHeader) {
            pageHeader.classList.add('relative', 'pt-14', 'md:pt-5');
            pageHeader.appendChild(sidebarToggle);
        }

        function updateSidebarToggleVisibility() {
            const pageIsScrolled = pageScrollAreas.some((area) => area.scrollTop > 8);
            sidebarToggle.classList.toggle('hidden', !desktopViewport.matches && (!sidebar.inert || pageIsScrolled));
        }

        function setSidebarOpen(open) {
            if (desktopViewport.matches) {
                sidebar.inert = false;
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                sidebarToggle.setAttribute('aria-expanded', 'false');
                updateSidebarToggleVisibility();
                return;
            }

            sidebar.inert = !open;
            sidebar.classList.toggle('-translate-x-full', !open);
            sidebarOverlay.classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open);
            sidebarToggle.setAttribute('aria-expanded', String(open));
            sidebarToggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
            updateSidebarToggleVisibility();
        }

        sidebarToggle.addEventListener('click', () => setSidebarOpen(sidebar.inert));
        sidebarClose.addEventListener('click', () => {
            setSidebarOpen(false);
            if (!sidebarToggle.classList.contains('hidden')) {
                sidebarToggle.focus();
            }
        });
        sidebarOverlay.addEventListener('click', () => setSidebarOpen(false));
        sidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setSidebarOpen(false));
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !desktopViewport.matches && !sidebar.inert) {
                setSidebarOpen(false);
                if (!sidebarToggle.classList.contains('hidden')) {
                    sidebarToggle.focus();
                }
            }
        });
        desktopViewport.addEventListener('change', () => setSidebarOpen(false));
        pageScrollAreas.forEach((area) => {
            area.addEventListener('scroll', updateSidebarToggleVisibility, { passive: true });
        });
        setSidebarOpen(false);
    });
</script>