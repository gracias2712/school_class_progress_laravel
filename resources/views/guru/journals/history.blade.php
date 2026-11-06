<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Jurnal Mengajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Riwayat Jurnal Mengajar</h2>
                <p class="text-sm text-gray-600">{{ $schedule->subject }} - {{ $schedule->classe->class_name ?? '-' }} ({{ $schedule->room->room_name ?? '-' }})</p>
            </div>
            <a href="{{ route('guru.journals.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-600">Kembali</a>
        </div>

        <div class="space-y-6">
            @forelse($journals as $journal)
                <div class="border border-gray-200 rounded-xl p-5 bg-gray-50 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full">Tanggal: {{ $journal->date }}</span>
                            <h3 class="text-lg font-bold text-gray-800 mt-2">Materi: {{ $journal->topic_description }}</h3>
                        </div>
                        @if($journal->photo_path)
                            <a href="{{ asset('storage/' . $journal->photo_path) }}" target="_blank" class="text-xs bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-300 font-semibold">
                                Lihat Foto Dokumentasi
                            </a>
                        @endif
                    </div>

                    <h4 class="text-sm font-bold text-gray-700 mt-4 mb-2">Presensi Kehadiran Siswa:</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach($journal->attendances as $attendance)
                            <div class="bg-white p-2.5 rounded-lg border border-gray-200 flex justify-between items-center text-xs">
                                <span class="font-medium text-gray-800">{{ $attendance->student->fullname ?? $attendance->student->username ?? 'Siswa' }}</span>
                                <span class="px-2 py-0.5 rounded text-white font-bold
                                    @if($attendance->status == 'Hadir') bg-green-500
                                    @elseif($attendance->status == 'Sakit') bg-yellow-500
                                    @elseif($attendance->status == 'Izin') bg-blue-500
                                    @else bg-red-500 @endif">
                                    {{ $attendance->status }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 border border-dashed rounded-xl">
                    Belum ada riwayat jurnal yang diisi untuk jadwal ini.
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>