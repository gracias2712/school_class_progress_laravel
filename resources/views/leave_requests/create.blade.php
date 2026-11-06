<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin - School Class Progress</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <h1 class="text-xl font-bold">Pengajuan Izin / Sakit</h1>
        <a href="/dashboard" class="bg-blue-700 hover:bg-blue-800 px-3 py-1.5 rounded-lg text-sm transition">Kembali ke Dashboard</a>
    </nav>

    <div class="container mx-auto px-6 py-8 flex-1 max-w-2xl">
        <div class="bg-white p-8 rounded-xl shadow-md border-t-4 border-blue-500">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Formulir Pengajuan Izin</h2>
            <p class="text-gray-600 mb-6 text-sm">Silakan isi detail izin dan unggah bukti surat dokter/keterangan yang valid.</p>

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 text-sm rounded-lg border border-red-300">
                    <ul class="list-disc pl-5 font-semibold">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Alasan Izin / Sakit</label>
                    <textarea name="reason" rows="4" required placeholder="Jelaskan alasan ketidakhadiran secara detail..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('reason') }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Unggah Bukti Surat (PDF / JPG / PNG)</label>
                    <input type="file" name="document" accept=".pdf, .jpg, .jpeg, .png" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">*Maksimal ukuran file 2MB.</p>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                    Kirim Pengajuan Izin
                </button>
            </form>
        </div>
    </div>
</body>
</html>