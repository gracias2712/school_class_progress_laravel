<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data {{ $title }} - SCP School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden font-sans text-slate-800">
    @include('layouts.sidebar')
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="bg-white/80 border-b border-slate-200 p-5 flex justify-between items-center z-10">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Manajemen {{ $title }}</h1>
                <p class="text-xs text-slate-500 font-medium mt-1">Kelola data dan hak akses {{ $title }}</p>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            @if(session('success')) <div class="bg-emerald-50 text-emerald-700 px-5 py-4 rounded-xl mb-6 font-bold">✅ {{ session('success') }}</div> @endif
            @if(session('error')) <div class="bg-red-50 text-red-700 px-5 py-4 rounded-xl mb-6 font-bold">❌ {{ session('error') }}</div> @endif
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- FORM TAMBAH -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border p-6 sticky top-0">
                        <h3 class="text-lg font-extrabold text-slate-800 mb-4">➕ Tambah Akun Baru</h3>
                        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="role_id" value="{{ $role_id }}">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap {{ $title }}</label>
                                <input type="text" name="user_identifier" required class="w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">NIP (Nomor Induk Pegawai)</label>
                                <input type="text" name="nisn_nip" class="w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Email (Untuk Lupa Password)</label>
                                <input type="email" name="email" placeholder="contoh@sekolah.sch.id" class="w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
                            </div>
                            
                            <!-- Hanya Tampil untuk Guru Mapel (Role 2) -->
                            @if($role_id == 2)
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Mata Pelajaran</label>
                                <input type="text" name="subject" class="w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm uppercase">
                            </div>
                            @endif

                            <!-- Hanya Tampil untuk Wali Kelas (Role 3) -->
                            @if($role_id == 3)
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Kelas Wali</label>
                                <select name="class_id" class="w-full px-3 py-2 bg-slate-50 border rounded-lg text-sm">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            <hr class="my-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Username Login</label>
                                <input type="text" name="username" required class="w-full px-3 py-2 bg-white border rounded-lg text-sm">
                                <p class="text-[10px] text-slate-500 mt-1">*Password default otomatis: <strong>password</strong></p>
                            </div>
                            <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 rounded-xl">Simpan Akun</button>
                        </form>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex justify-between items-center bg-white p-4 rounded-2xl shadow-sm border">
                        <p class="text-sm font-bold text-slate-700">Daftar {{ $title }} Aktif</p>
                        <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-lg text-xs font-extrabold shadow-sm">📥 Import Excel</button>
                    </div>

                    <!-- DYNAMIC FILTER AREA -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 grid gap-4 grid-cols-1 md:grid-cols-2">
                        @if($role_id == 2)
                            <!-- Khusus Guru Mapel: Filter Pelajaran -->
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Filter Mata Pelajaran</label>
                                <select id="filterMapel" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg outline-none">
                                    <option value="">Semua Mata Pelajaran</option>
                                    @php
                                        // Mengambil daftar mapel unik dari data user yang ada
                                        $mapels = $users->pluck('subject')->filter()->unique()->sort();
                                    @endphp
                                    @foreach($mapels as $m)
                                        <option value="{{ $m }}">{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif($role_id == 3)
                            <!-- Khusus Wali Kelas: Filter Kelas & Jurusan -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Filter Tingkat Kelas</label>
                                <select id="filterTingkat" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white outline-none">
                                    <option value="">Semua Tingkat</option>
                                    <option value="Kelas: X ">Kelas 10 (X)</option>
                                    <option value="Kelas: XI ">Kelas 11 (XI)</option>
                                    <option value="Kelas: XII ">Kelas 12 (XII)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Filter Jurusan</label>
                                <select id="filterJurusan" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white outline-none">
                                    <option value="">Semua Jurusan</option>
                                    <option value="RPL">RPL</option><option value="TKJ">TKJ</option><option value="DKV">DKV</option><option value="AK">AK</option><option value="LPS">LPS</option><option value="MP">MP</option><option value="BR">BR</option><option value="BD">BD</option>
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border p-4 overflow-x-auto">
                        <table id="dataTable" class="w-full text-left text-sm border-collapse min-w-[500px]">
                            <thead>
                                <tr class="bg-slate-50 border-b">
                                    <th class="py-3 px-4 text-slate-500">Profil</th>
                                    <th class="py-3 px-4 text-slate-500">Info Tambahan & Email</th>
                                    <th class="py-3 px-4 text-slate-500 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                <tr class="border-b border-slate-50 hover:bg-slate-50">
                                    <td class="py-3 px-4">
                                        <p class="font-bold text-slate-800 uppercase">{{ $u->fullname }}</p>
                                        <p class="text-xs text-slate-500">@ {{ $u->username }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-xs font-semibold text-slate-600">
                                        NIP: {{ $u->nisn_nip ?? '-' }} <br>
                                        
                                        <!-- Menampilkan Mapel jika ada -->
                                        @if($u->subject) 
                                            Mapel: {{ $u->subject }} <br> 
                                        @endif
                                        
                                        <!-- PERBAIKAN: Menampilkan Kelas dari leftJoin ($u->class_name) -->
                                        @if($role_id == 3 && $u->class_id)
                                            <span class="text-teal-600">Kelas: {{ $u->class_name ?? '-' }}</span> <br>
                                        @endif

                                        <span class="text-indigo-600 font-normal"> {{ $u->email ?? 'Belum ada email' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 font-bold bg-red-50 px-3 py-1 rounded-lg">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL IMPORT -->
        <div id="importModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="bg-indigo-600 px-6 py-4"><h3 class="text-white font-bold">Import Data {{ $title }}</h3></div>
                <form action="{{ route('users.import.pegawai') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <input type="hidden" name="role_id" value="{{ $role_id }}">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File Excel (.xlsx)</label>
                    <input type="file" name="file" accept=".xlsx, .csv" required class="w-full border p-2 rounded-lg mb-4 text-sm">
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 rounded-lg font-bold">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold">Upload Data</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                "pageLength": 10,
                "language": {
                    "search": "Cari Data:"
                }
            });

            // Logika Filter Kustom (Menyesuaikan dengan apa yang ada di Layar)
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'dataTable') return true;
                
                var searchCol = data[1] || ''; // Mengambil teks dari kolom "Info Tambahan"
                
                // Filter Mapel (Khusus Role 2)
                if ($('#filterMapel').length > 0) {
                    var mapel = $('#filterMapel').val();
                    if (mapel && searchCol.indexOf('Mapel: ' + mapel) === -1) return false;
                }

                // Filter Kelas & Jurusan (Khusus Role 3 Wali Kelas)
                if ($('#filterTingkat').length > 0) {
                    var tingkat = $('#filterTingkat').val();
                    var jurusan = $('#filterJurusan').val();
                    if (tingkat && searchCol.indexOf(tingkat) === -1) return false;
                    if (jurusan && searchCol.indexOf(jurusan) === -1) return false;
                }

                return true;
            });

            // Jalankan filter saat dropdown dipilih
            $('#filterMapel, #filterTingkat, #filterJurusan').on('change', function() {
                table.draw();
            });
        });
    </script>
</body>
</html>