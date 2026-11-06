<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Perizinan Siswa - School Class Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl font-bold">Panel Kesiswaan / BK</h1>
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="bg-blue-700 hover:bg-blue-800 px-3 py-1.5 rounded-lg text-sm transition">Dashboard</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded-lg text-sm font-semibold transition">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8 flex-1">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Verifikasi Izin & Sakit Siswa</h2>
                <p class="text-sm text-gray-500 mt-1">Tinjau dokumen persetujuan untuk auto-sync presensi kehadiran.</p>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm font-medium">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Tabel Verifikasi Izin -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700 text-xs uppercase tracking-wider">
                            <th class="py-3 px-4">Siswa / Pemohon</th>
                            <th class="py-3 px-4">Tanggal Izin</th>
                            <th class="py-3 px-4">Alasan & Dokumen</th>
                            <th class="py-3 px-4 text-center">Status Saat Ini</th>
                            <th class="py-3 px-4 text-center">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                        @forelse($leaveRequests as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-4 px-4">
                                    <span class="font-bold block text-gray-800 text-base">{{ $item->student->fullname ?? 'Nama Siswa' }}</span>
                                    <span class="text-xs text-gray-500 font-semibold bg-gray-100 px-2 py-0.5 rounded">
                                        Kelas: {{ $item->student->class->class_name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="font-bold text-gray-700 block">{{ \Carbon\Carbon::parse($item->start_date)->format('d M Y') }}</span>
                                    <span class="text-xs text-gray-500">s/d {{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <p class="font-medium text-gray-800 mb-1">{{ $item->reason }}</p>
                                    <a href="{{ asset('storage/' . $item->document_path) }}" target="_blank" class="text-xs text-blue-600 hover:underline font-bold flex items-center gap-1">
                                        📎 Buka Lampiran Surat
                                    </a>

                                    {{-- Muncul jika siswa mengajukan banding --}}
                                    @if($item->appeal_reason)
                                        <div class="mt-2 p-2 bg-indigo-50 border border-indigo-200 rounded text-xs text-indigo-900">
                                            <strong>Alasan Banding:</strong> {{ $item->appeal_reason }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($item->status_approval == 'Pending')
                                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 border border-amber-300 rounded-full text-xs font-bold">Menunggu</span>
                                    @elseif($item->status_approval == 'Approved')
                                        <span class="px-2.5 py-1 bg-green-100 text-green-800 border border-green-300 rounded-full text-xs font-bold">Disetujui</span>
                                    @elseif($item->status_approval == 'Rejected')
                                        <span class="px-2.5 py-1 bg-red-100 text-red-800 border border-red-300 rounded-full text-xs font-bold">Ditolak</span>
                                    @elseif($item->status_approval == 'Appealed')
                                        <span class="px-2.5 py-1 bg-indigo-100 text-indigo-800 border border-indigo-300 rounded-full text-xs font-bold animate-pulse">Banding Diajukan</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center">
                                    {{-- Tampilkan tombol Approve/Reject HANYA jika statusnya Pending atau Banding --}}
                                    @if($item->status_approval == 'Pending' || $item->status_approval == 'Appealed')
                                        <div class="flex justify-center gap-2">
                                            <!-- Tombol Approve -->
                                            <form action="{{ route('leave_requests.verify', $item->id) }}" method="POST" onsubmit="return confirm('Setujui izin siswa ini? Presensi akan di-sync otomatis.')">
                                                @csrf
                                                <input type="hidden" name="status_approval" value="Approved">
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white p-2 rounded shadow-sm transition tooltip" title="Setujui">
                                                    ✔️
                                                </button>
                                            </form>

                                            <!-- Tombol Reject -->
                                            <form action="{{ route('leave_requests.verify', $item->id) }}" method="POST" onsubmit="return confirm('Tolak izin siswa ini?')">
                                                @csrf
                                                <input type="hidden" name="status_approval" value="Rejected">
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded shadow-sm transition tooltip" title="Tolak">
                                                    ❌
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Sudah diverifikasi</span><br>
                                        <span class="text-[10px] text-gray-500">Oleh: {{ $item->verifier->fullname ?? 'Sistem' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">
                                    Belum ada data pengajuan perizinan siswa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>