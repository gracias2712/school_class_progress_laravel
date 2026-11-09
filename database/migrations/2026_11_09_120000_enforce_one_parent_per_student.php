<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateStudent = DB::table('users')
            ->whereNotNull('student_id')
            ->select('student_id')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicateStudent) {
            throw new RuntimeException(
                "Siswa {$duplicateStudent->student_id} sudah terhubung ke lebih dari satu akun. "
                .'Rapikan relasi akun Orang Tua sebelum menjalankan migrasi ini.',
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_id']);
        });
    }
};
