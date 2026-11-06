<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            // Menambahkan kolom setelah class_name sesuai urutan di gambar
            $table->string('grade_level', 10)->nullable()->after('class_name'); // Contoh isian: X, XI, XII
            $table->string('major', 50)->nullable()->after('grade_level');      // Contoh isian: RPL, TKJ, DKV
            $table->string('section', 10)->nullable()->after('major');          // Contoh isian: 1, 2, 3
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['grade_level', 'major', 'section']);
        });
    }
};