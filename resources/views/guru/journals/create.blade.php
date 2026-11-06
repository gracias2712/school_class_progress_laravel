<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Jurnal & Presensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Form Jurnal & Presensi Kelas</h2>
            <a href="{{ route('guru.journals.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-600">Kembali</a>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded-lg">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 p-4 bg-gray-50 rounded-lg text-sm text-gray-700">
            <p><strong>Mata Pelajaran:</strong> {{ $schedule->subject }}</p>
            <p><strong>Kelas:</strong> {{ $schedule->classe->class_name ?? '-' }}</p>
            <p><strong>Ruangan:</strong> {{ $schedule->room->room_name ?? '-' }}</p>
            <p><strong>Hari / Jam:</strong> {{ $schedule->day }} ({{ $schedule->start_time }} - {{ $schedule->end_time }})</p>
        </div>

        <form action="{{ route('guru.journals.store', $schedule->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Materi Pembelajaran</label>
                <input type="text" name="material" value="{{ old('material') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Catatan Kegiatan (Opsional)</label>
                <textarea name="notes" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Foto Dokumentasi (Real-time)</label>
                <input type="file" name="photo" accept="image/*" capture="environment" required
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">Gunakan perangkat seluler untuk mengambil foto langsung atau unggah file gambar max 2MB.</p>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-3">Daftar Presensi Siswa</h3>
                <table class="w-full border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-50 text-left text-sm text-gray-600">
                            <th class="border border-gray-200 p-3 w-12 text-center">No</th>
                            <th class="border border-gray-200 p-3">Nama Siswa</th>
                            <th class="border border-gray-200 p-3 text-center">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedule->classe->students as $index => $student)
                            <tr class="text-sm text-gray-700">
                                <td class="border border-gray-200 p-3 text-center">{{ $index + 1 }}</td>
                                <td class="border border-gray-200 p-3">{{ $student->fullname ?? $student->username }}</td>
                                <td class="border border-gray-200 p-3 text-center">
                                    <select name="attendances[{{ $student->id }}]" class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        <option value="Hadir">Hadir</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Izin">Izin</option>
                                        <option value="Alpha">Alpa</option>
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="border border-gray-200 p-4 text-center text-gray-500">Tidak ada data siswa pada kelas ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                Simpan Jurnal & Presensi
            </button>
        </form>
    </div>
</body>
</html>