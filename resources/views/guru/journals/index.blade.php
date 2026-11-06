<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jurnal Mengajar Guru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Daftar Jadwal Mengajar</h2>
            <a href="{{ route('dashboard') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-600">Kembali</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 text-sm rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-600">
                    <th class="border border-gray-200 p-3">Hari & Jam</th>
                    <th class="border border-gray-200 p-3">Mata Pelajaran</th>
                    <th class="border border-gray-200 p-3">Kelas</th>
                    <th class="border border-gray-200 p-3">Ruangan</th>
                    <th class="border border-gray-200 p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                    <tr class="text-sm text-gray-700">
                        <td class="border border-gray-200 p-3">{{ $schedule->day_of_week ?? $schedule->day }} <br> {{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                        <td class="border border-gray-200 p-3 font-semibold">{{ $schedule->subject }}</td>
                        <td class="border border-gray-200 p-3">{{ $schedule->classe->class_name ?? '-' }}</td>
                        <td class="border border-gray-200 p-3">{{ $schedule->room->room_name ?? '-' }}</td>
                        <td class="border border-gray-200 p-3 text-center">
                            <div class="flex justify-center gap-2">
                                @php
                    // Cek apakah jurnal sudah diisi hari ini
                    $hasJournalToday = \App\Models\Journal::where('schedule_id', $schedule->id)
                        ->whereDate('date', \Carbon\Carbon::today('Asia/Jakarta'))
                        ->exists();
                    @endphp

                    @if($hasJournalToday)
                        <span class="bg-gray-400 text-white px-3 py-1.5 rounded-lg text-sm font-semibold cursor-not-allowed">✅ Sudah Diisi</span>
                    @else
                        <a href="{{ route('guru.journals.create', $schedule->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold transition shadow-sm">
                        + Isi Jurnal
                    </a>
                @endif

                        <a href="{{ route('guru.journals.history', $schedule->id) }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-sm font-semibold transition shadow-sm ml-2">
                        Lihat Riwayat
                    </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="border border-gray-200 p-4 text-center text-gray-500">Tidak ada jadwal mengajar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>