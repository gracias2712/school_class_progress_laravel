<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Orang Tua - SCP School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container .select2-selection--single {
            height: 38px; border-radius: 0.5rem; border-color: #e2e8f0;
            padding: 4px 12px; font-size: 0.875rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    </style>
</head>
<body class="bg-slate-50 flex h-screen overflow-hidden font-sans text-slate-800">
    @include('layouts.sidebar')
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="bg-white/80 border-b border-slate-200 p-5 z-10">
            <h1 class="text-xl font-extrabold text-slate-800">Manajemen Orang Tua</h1>
        </header>

        <div class="flex-1 overflow-y-auto p-4 md:p-8">
            @if(session('success')) <div class="bg-emerald-50 text-emerald-700 px-5 py-4 rounded-xl mb-6 font-bold">✅ {{ session('success') }}</div> @endif
            @if(session('error')) <div class="bg-red-50 text-red-700 px-5 py-4 rounded-xl mb-6 font-bold">❌ {{ session('error') }}</div> @endif
            @if($errors->any())
                <div class="bg-red-50 text-red-700 px-5 py-4 rounded-xl mb-6 font-bold">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif
            <div class="bg-blue-50 text-blue-800 px-5 py-4 rounded-xl mb-6 text-sm">
                Akun Orang Tua dibuat manual oleh Waka dan ditautkan ke satu siswa.
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- FORM TAMBAH -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border p-6 sticky top-0">
                        <h3 class="text-lg font-extrabold text-slate-800 mb-4">➕ Tambah Akun Ortu</h3>
                        @if($students->isEmpty())
                            <p class="mb-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                                Tidak ada siswa yang belum memiliki akun Orang Tua.
                            </p>
                        @endif
                        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="role_id" value="6">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Orang Tua</label>
                                <input type="text" name="user_identifier" required class="w-full px-3 py-2 border rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">NISP Orang Tua</label>
                                <input type="text" name="nisn_nip" required class="w-full px-3 py-2 border rounded-lg text-sm">
                                <p class="text-[10px] text-slate-500 mt-1">*Username otomatis berdasarkan NISP. Password awal: <strong>password</strong>.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Email Ortu (Untuk Lupa Password)</label>
                                <input type="email" name="email" placeholder="orangtua@gmail.com" class="w-full px-3 py-2 border rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Pilih Siswa</label>
                                <select name="student_id" id="student_select" required class="w-full px-3 py-2 border rounded-lg text-sm">
                                    @foreach($students as $s)
                                        <option value="{{ $s->id }}">{{ $s->nisn_nip }} - {{ $s->fullname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" @disabled($students->isEmpty()) class="w-full bg-teal-600 hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50 text-white font-bold py-3 rounded-xl mt-4">Simpan Akun</button>
                        </form>
                    </div>
                </div>

                <!-- TABEL DATA -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-3xl shadow-sm border p-4">
                        <table id="dataTable" class="w-full text-left text-sm border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b">
                                    <th class="py-3 px-4">Profil Orang Tua</th>
                                    <th class="py-3 px-4">Data Anak (Siswa)</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                <tr class="border-b border-slate-50">
                                    <td class="py-3 px-4">
                                        <p class="font-bold text-slate-800">{{ $u->fullname }}</p>
                                        <p class="text-xs text-slate-500">NISP: {{ $u->nisn_nip ?? '-' }} | @ {{ $u->username }}</p>
                                        <p class="text-xs text-indigo-600"> {{ $u->email ?? 'Belum ada email' }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-xs">
                                        @if($u->students->isNotEmpty())
                                            @foreach($u->students as $student)
                                                <span class="font-bold text-indigo-600">{{ $student->fullname }}</span>
                                                <span class="text-slate-500">(NIS: {{ $student->nisn_nip ?? '-' }})</span><br>
                                            @endforeach
                                        @else
                                            <span class="text-red-500 italic">Belum terhubung</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun Orang Tua ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 font-bold bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg transition">Hapus</button>
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
    </main>
    <script> 
        $(document).ready(function() { 
            $('#dataTable').DataTable({"pageLength": 10}); 
            $('#student_select').select2({
                placeholder: "-- Cari & Pilih Siswa --"
            });
        }); 
    </script>
</body>
</html>