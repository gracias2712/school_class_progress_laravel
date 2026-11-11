<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $guardianRows = DB::table('users')
            ->where('role_id', 6)
            ->get(['id', 'student_id', 'parent_id']);
        $studentGuardianMap = [];

        foreach ($guardianRows as $guardian) {
            foreach (array_unique(array_filter([$guardian->student_id, $guardian->parent_id])) as $studentId) {
                $isStudent = DB::table('users')
                    ->where('id', $studentId)
                    ->where('role_id', 5)
                    ->exists();

                if (! $isStudent) {
                    continue;
                }

                if (isset($studentGuardianMap[$studentId]) && $studentGuardianMap[$studentId] !== $guardian->id) {
                    throw new RuntimeException(
                        "Siswa {$studentId} terhubung ke lebih dari satu akun Orang Tua. "
                        .'Perbaiki relasi ganda sebelum menjalankan migrasi.',
                    );
                }

                $studentGuardianMap[$studentId] = $guardian->id;
            }
        }

        if (Schema::hasIndex('users', ['student_id'], 'unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['student_id']);
            });
        }

        if (! Schema::hasTable('guardian_student')) {
            Schema::create('guardian_student', function (Blueprint $table) {
                $table->foreignId('guardian_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->primary(['guardian_id', 'student_id']);
                $table->timestamps();
            });
        }

        foreach ($studentGuardianMap as $studentId => $guardianId) {
            $existingGuardianId = DB::table('guardian_student')
                ->where('student_id', $studentId)
                ->value('guardian_id');

            if ($existingGuardianId !== null && (int) $existingGuardianId !== (int) $guardianId) {
                throw new RuntimeException(
                    "Siswa {$studentId} terhubung ke akun Orang Tua yang berbeda di data relasi. "
                    .'Periksa relasi tersebut sebelum menjalankan migrasi.',
                );
            }

            if ($existingGuardianId === null) {
                DB::table('guardian_student')->insert([
                    'guardian_id' => $guardianId,
                    'student_id' => $studentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_student');
    }
};
