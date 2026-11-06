<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Izin - School Class Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl font-bold">Verifikasi Pengajuan Izin</h1>
        <a href="/dashboard" class="bg-blue-700 hover:bg-blue-800 px-3 py-1.5 rounded-lg text-sm transition">Kembali ke Dashboard</a>
    </nav>

    <div class="container mx-auto px-6 py-8 flex-1">
        
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 text-sm rounded-lg font-bold border border-green-300">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white text-sm uppercase">
                        <th class="py-3 px-4">Tgl Pengajuan</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Rentang Waktu</th>
                        <th class="py-3 px-4">Alasan</th>
                        <th class="py-3 px-4 text-center">Bukti</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-200">
                    @forelse($leaveRequests as $leave)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $leave->created_at->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 font-bold text-gray-800">{{ $leave->student->fullname }}</td>
                            <td class="py-3 px-4">{{ optional($leave->student->classe)->class_name ?? '-' }}</td>
                            <td class="py-3 px-4">
                                {{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }} - <br>
                                {{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4">{{ $leave->reason }}</td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ asset('storage/' . $leave->document_path) }}" target="_blank" class="text-blue-600 hover:underline font-semibold">
                                    Lihat Surat
                                </a>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($leave->status_approval == 'Pending')
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-bold">Pending</span>
                                @elseif($leave->status_approval == 'Approved')
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-bold">Disetujui</span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-bold">Ditolak</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($leave->status_approval == 'Pending')
                                    <form action="{{ route('leave.updateStatus', $leave->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_approval" value="Approved">
                                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-xs font-bold transition">Terima</button>
                                    </form>
                                    <form action="{{ route('leave.updateStatus', $leave->id) }}" method="POST" class="inline-block ml-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_approval" value="Rejected">
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs font-bold transition">Tolak</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-gray-500 italic">Belum ada data pengajuan izin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>