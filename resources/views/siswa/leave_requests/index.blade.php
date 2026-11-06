<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan & Banding Izin - School Class Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js untuk penanganan Modal Banding -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col" x-data="{ openAppealModal: false, activeId: null }">

    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl font-bold">Portal Perizinan Siswa</h1>
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="bg-blue-700 hover:bg-blue-800 px-3 py-1.5 rounded-lg text-sm transition">Dashboard</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded-lg text-sm font-semibold transition">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8 flex-1">
        
        <!-- Notifikasi Flash Message -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm font-medium">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl text-sm font-medium">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Form Pengajuan Izin Baru -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">📝 Buat Pengajuan Izin / Sakit</h2>
                    
                    <form action="{{ route('leave_requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tanggal Mulai</label>
                            <input type="date" name="start_date" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tanggal Selesai</label>
                            <input type="date" name="end_date" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Alasan Ketidakhadiran</label>
                            <textarea name="reason" rows="3" required placeholder="Jelaskan alasan izin / sakit..." class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Unggah Surat Dokter / Resmi</label>
                            <input type="file" name="document_path" accept=".pdf,.jpg,.jpeg,.png" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg cursor-pointer">
                            <span class="text-[11px] text-gray-400 mt-1 block">Format: JPG, PNG, PDF (Maks. 2MB)</span>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg text-sm transition shadow-sm">
                            Kirim Pengajuan Izin
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabel Riwayat & Status Pengajuan -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-bold text-gray-800">📋 Riwayat & Status Perizinan</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700 text-xs uppercase tracking-wider">
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Alasan & Dokumen</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-center">Aksi / Banding</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                @forelse($leaveRequests as $item)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3 px-4">
                                            <span class="font-bold block text-gray-800">{{ \Carbon\Carbon::parse($item->start_date)->format('d M Y') }}</span>
                                            <span class="text-xs text-gray-500">s/d {{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <p class="font-medium text-gray-800 mb-1">{{ $item->reason }}</p>
                                            <a href="{{ asset('storage/' . $item->document_path) }}" target="_blank" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                                📎 Lihat Dokumen Surat
                                            </a>
                                            @if($item->appeal_reason)
                                                <div class="mt-2 p-2 bg-indigo-50 border border-indigo-200 rounded text-xs text-indigo-900">
                                                    <strong>Catatan Banding:</strong> {{ $item->appeal_reason }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($item->status_approval == 'Pending')
                                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 border border-amber-300 rounded-full text-xs font-bold">
                                                    ⏳ Pending
                                                </span>
                                            @elseif($item->status_approval == 'Approved')
                                                <span class="px-2.5 py-1 bg-green-100 text-green-800 border border-green-300 rounded-full text-xs font-bold">
                                                    ✅ Disetujui
                                                </span>
                                            @elseif($item->status_approval == 'Rejected')
                                                <span class="px-2.5 py-1 bg-red-100 text-red-800 border border-red-300 rounded-full text-xs font-bold">
                                                    ❌ Ditolak
                                                </span>
                                            @elseif($item->status_approval == 'Appealed')
                                                <span class="px-2.5 py-1 bg-indigo-100 text-indigo-800 border border-indigo-300 rounded-full text-xs font-bold">
                                                    🔄 Proses Banding
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($item->status_approval == 'Rejected')
                                                <button @click="openAppealModal = true; activeId = {{ $item->id }}" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                                                    ⚖️ Ajukan Banding
                                                </button>
                                            @else
                                                <span class="text-xs text-gray-400 italic">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-gray-500">
                                            Belum ada riwayat pengajuan izin.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Form Banding Izin Siswa -->
    <div x-show="openAppealModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" x-cloak>
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" @click.away="openAppealModal = false">
            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                <h3 class="text-base font-bold text-gray-800">⚖️ Form Banding Perizinan</h3>
                <button @click="openAppealModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>

            <form :action="'{{ url('/leave-requests/appeal') }}/' + activeId" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                    Pengajuan izin Anda sebelumnya ditolak. Silakan berikan penjelasan atau lampirkan dokumen pendukung baru untuk ditinjau ulang oleh Kesiswaan.
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Alasan Banding</label>
                    <textarea name="appeal_reason" rows="3" required placeholder="Tuliskan sanggahan / alasan tambahan..." class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Unggah Dokumen Perbaikan (Opsional)</label>
                    <input type="file" name="document_path" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg cursor-pointer">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="openAppealModal = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition">
                        Kirim Sanggahan Banding
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>