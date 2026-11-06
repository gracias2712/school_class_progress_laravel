<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal - School Class Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl font-bold">Tambah Jadwal Pelajaran</h1>
        <a href="{{ route('schedules.index') }}" class="bg-blue-700 hover:bg-blue-800 px-3 py-1.5 rounded-lg text-sm transition">Kembali</a>
    </nav>

    <div class="container mx-auto px-6 py-8 flex-1 max-w-xl">
        <div class="bg-white p-8 rounded-xl shadow-md">
            
            <!-- Notifikasi Pesan Error Bentrok / Lock -->
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 text-sm rounded-lg font-bold border border-red-300 shadow-sm">
                    🚨 {{ session('error') }}
                </div>
            @endif

            <!-- Notifikasi Pesan Error Validasi Form -->
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded-lg border border-red-300">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('schedules.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Mata Pelajaran</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="Contoh: Pemrograman Web"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Guru Pengampu</label>
                    <select name="teacher_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->fullname }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Kelas</label>
                        <select name="class_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Ruangan</label>
                        <select name="room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>{{ $room->room_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Hari</label>
                    <select name="day_of_week" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Senin" {{ old('day_of_week') == 'Senin' ? 'selected' : '' }}>Senin</option>
                        <option value="Selasa" {{ old('day_of_week') == 'Selasa' ? 'selected' : '' }}>Selasa</option>
                        <option value="Rabu" {{ old('day_of_week') == 'Rabu' ? 'selected' : '' }}>Rabu</option>
                        <option value="Kamis" {{ old('day_of_week') == 'Kamis' ? 'selected' : '' }}>Kamis</option>
                        <option value="Jumat" {{ old('day_of_week') == 'Jumat' ? 'selected' : '' }}>Jumat</option>
                        <option value="Sabtu" {{ old('day_of_week') == 'Sabtu' ? 'selected' : '' }}>Sabtu</option>
                    </select>
                </div>

                <!-- Dropdown Jam Mengajar Baku -->
                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Jam Pelajaran Mulai</label>
                        <select name="start_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Jam Mulai --</option>
                            <option value="07:15" {{ old('start_time') == '07:15' ? 'selected' : '' }}>07:15 (Jam ke-1)</option>
                            <option value="08:00" {{ old('start_time') == '08:00' ? 'selected' : '' }}>08:00 (Jam ke-2)</option>
                            <option value="08:45" {{ old('start_time') == '08:45' ? 'selected' : '' }}>08:45 (Jam ke-3)</option>
                            <option value="09:45" {{ old('start_time') == '09:45' ? 'selected' : '' }}>09:45 (Jam ke-4)</option>
                            <option value="10:30" {{ old('start_time') == '10:30' ? 'selected' : '' }}>10:30 (Jam ke-5)</option>
                            <option value="11:15" {{ old('start_time') == '11:15' ? 'selected' : '' }}>11:15 (Jam ke-6)</option>
                            <option value="12:30" {{ old('start_time') == '12:30' ? 'selected' : '' }}>12:30 (Jam ke-7)</option>
                            <option value="13:15" {{ old('start_time') == '13:15' ? 'selected' : '' }}>13:15 (Jam ke-8)</option>
                            <option value="14:00" {{ old('start_time') == '14:00' ? 'selected' : '' }}>14:00 (Jam ke-9)</option>
                            <option value="14:45" {{ old('start_time') == '14:45' ? 'selected' : '' }}>14:45 (Jam ke-10)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Jam Pelajaran Selesai</label>
                        <select name="end_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Jam Selesai --</option>
                            <option value="08:00" {{ old('end_time') == '08:00' ? 'selected' : '' }}>08:00 (Selesai Jam ke-1)</option>
                            <option value="08:45" {{ old('end_time') == '08:45' ? 'selected' : '' }}>08:45 (Selesai Jam ke-2)</option>
                            <option value="09:45" {{ old('end_time') == '09:45' ? 'selected' : '' }}>09:45 (Selesai Jam ke-3)</option>
                            <option value="10:30" {{ old('end_time') == '10:30' ? 'selected' : '' }}>10:30 (Selesai Jam ke-4)</option>
                            <option value="11:15" {{ old('end_time') == '11:15' ? 'selected' : '' }}>11:15 (Selesai Jam ke-5)</option>
                            <option value="12:30" {{ old('end_time') == '12:30' ? 'selected' : '' }}>12:30 (Selesai Jam ke-6)</option>
                            <option value="13:15" {{ old('end_time') == '13:15' ? 'selected' : '' }}>13:15 (Selesai Jam ke-7)</option>
                            <option value="14:00" {{ old('end_time') == '14:00' ? 'selected' : '' }}>14:00 (Selesai Jam ke-8)</option>
                            <option value="14:45" {{ old('end_time') == '14:45' ? 'selected' : '' }}>14:45 (Selesai Jam ke-9)</option>
                            <option value="15:30" {{ old('end_time') == '15:30' ? 'selected' : '' }}>15:30 (Selesai Jam ke-10)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tipe Siklus / Putaran</label>
                    <select name="cycle_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Putaran 1" {{ old('cycle_type') == 'Putaran 1' ? 'selected' : '' }}>Putaran 1</option>
                        <option value="Putaran 2" {{ old('cycle_type') == 'Putaran 2' ? 'selected' : '' }}>Putaran 2</option>
                    </select>
                </div>

                <button type="submit" 
                    class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                    Simpan Jadwal
                </button>
            </form>
        </div>
    </div>
</body>
</html>