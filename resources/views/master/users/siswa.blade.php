<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - SCP School</title>
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
        
        <header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200 p-5 z-10">
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Manajemen Siswa</h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Kelola data dan hak akses Siswa</p>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            
            @if(session('success')) <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold">{{ session('success') }}</div> @endif
            @if(session('error')) <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 shadow-sm font-bold">{{ session('error') }}</div> @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- KOTAK KIRI: FORM TAMBAH -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sticky top-0">
                        <h3 class="text-lg font-extrabold text-slate-800 mb-6">Tambah Akun Baru</h3>
                        
                        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="role_id" value="5">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap Siswa</label>
                                <input type="text" name="fullname" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:border-indigo-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">NIS (Otomatis jadi Username)</label>
                                <input type="text" name="nisn_nip" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:border-indigo-500 outline-none">
                                <p class="text-[10px] text-slate-400 mt-1">*Password default otomatis: <b>password</b></p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Email Siswa (Untuk Lupa Password)</label>
                                <input type="email" name="email" placeholder="siswa@sekolah.sch.id" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:border-indigo-500 outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Kelas</label>
                                <select name="class_id" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:border-indigo-500 outline-none">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="w-full mt-2 bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-md">
                                Simpan Akun
                            </button>
                        </form>
                    </div>
                </div>

                <!-- KOTAK KANAN: TABEL DATA -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden p-6">
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-sm font-extrabold text-slate-700">Daftar Siswa Aktif</h3>
                            <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-4 py-2 rounded-lg text-xs font-extrabold transition shadow-sm">
                                Import Excel
                            </button>
                        </div>

                        <!-- FILTER KUSTOM DENGAN TAMBAHAN ROMBEL -->
                        <div class="mb-5 grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Filter Kelas</label>
                                <select id="filterTingkat" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white outline-none focus:border-indigo-500">
                                    <option value="">Semua Tingkat</option>
                                    <option value="Kelas: X ">Kelas 10 (X)</option>
                                    <option value="Kelas: XI ">Kelas 11 (XI)</option>
                                    <option value="Kelas: XII ">Kelas 12 (XII)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Filter Jurusan</label>
                                <select id="filterJurusan" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white outline-none focus:border-indigo-500">
                                    <option value="">Semua Jurusan</option>
                                    <option value="RPL">RPL</option>
                                    <option value="TKJ">TKJ</option>
                                    <option value="DKV">DKV</option>
                                    <option value="AK">AK</option>
                                    <option value="LPS">LPS</option>
                                    <option value="MP">MP</option>
                                    <option value="BR">BR</option>
                                    <option value="BD">BD</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Filter Rombel</label>
                                <select id="filterRombel" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white outline-none focus:border-indigo-500">
                                    <option value="">Semua Rombel</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table id="siswaTable" class="w-full text-left border-collapse min-w-[500px]">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-100">
                                        <th class="py-3 px-4 text-xs font-extrabold text-slate-500">Nama Siswa</th>
                                        <th class="py-3 px-4 text-xs font-extrabold text-slate-500">NIS, Kelas & Email</th>
                                        <th class="py-3 px-4 text-xs font-extrabold text-slate-500 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($users as $u)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="py-4 px-4">
                                                <div class="font-extrabold text-slate-800 uppercase">{{ $u->fullname }}</div>
                                                <div class="text-xs text-slate-400 font-medium">@ {{ $u->username }}</div>
                                            </td>
                                            <td class="py-4 px-4">
                                                <div class="text-xs font-medium text-slate-700">NIS: {{ $u->nisn_nip ?? '-' }}</div>
                                                <div class="text-[11px] font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded inline-block mt-0.5 mb-1">
                                                    Kelas: {{ $u->class_name ?? '-' }}
                                                </div>
                                                <div class="text-[11px] font-medium {{ str_contains($u->email, '@ukkscp.com') ? 'text-amber-500 italic' : 'text-blue-600' }}">
                                                    {{ str_contains($u->email, '@ukkscp.com') ? 'Belum Update Email Asli' : $u->email }}
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus siswa ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-bold transition border border-red-100">Hapus</button>
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
        </div>
        
        <!-- MODAL IMPORT -->
        <div id="importModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-white font-bold text-lg">Import Data Siswa</h3>
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="text-indigo-200 hover:text-white transition font-bold text-xl">X</button>
                </div>
                <div class="p-6">
                    <form action="{{ route('users.import.siswa') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="role_id" value="5"> <!-- Pastikan role ID Siswa sesuai dengan database Anda -->
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File (.xlsx)</label>
                            <input type="file" name="file" accept=".xlsx, .xls, .csv" required class="w-full text-sm text-slate-500 border border-slate-200 rounded-lg p-2">
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-lg transition">Batal</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition shadow-md">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
    $(document).ready(function() {
        var table = $('#siswaTable').DataTable({
            "language": {
                "search": "Pencarian NIS/Nama:",
                "lengthMenu": "Tampilkan _MENU_ entri"
            }
        });

        // Custom Filtering Logic
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'siswaTable') return true;
            
            var tingkat = $('#filterTingkat').val();
            var jurusan = $('#filterJurusan').val();
            var rombel = $('#filterRombel').val();
            
            // Kolom ke-2 (Index 1) menyimpan string: "NIS: 123 Kelas: X RPL 1 email@..."
            var searchCol = data[1] || ''; 
            
            if (tingkat && searchCol.indexOf(tingkat) === -1) return false;
            if (jurusan && searchCol.indexOf(jurusan) === -1) return false;
            if (rombel) {
                // Menggunakan Regex untuk memastikan angka yang difilter benar-benar dari Rombel, 
                // bukan angka dari NIS. Kita cek kata "Kelas:" dan angka rombel di akhirnya.
                var regex = new RegExp("Kelas:.*?\\b" + rombel + "\\b");
                if (!searchCol.match(regex)) return false;
            }
            
            return true;
        });

        // Trigger filter ketika dropdown mana pun berubah
        $('#filterTingkat, #filterJurusan, #filterRombel').on('change', function() {
            table.draw();
        });
    });
    </script>
</body>
</html>