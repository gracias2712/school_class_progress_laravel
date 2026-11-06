<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->id();
            $table->enum('active_cycle', ['Putaran 1', 'Putaran 2'])->default('Putaran 1');
            $table->boolean('is_schedule_locked')->default(false);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        DB::table('system_configurations')->insert([
            'active_cycle' => 'Putaran 1',
            'is_schedule_locked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_configurations');
    }
};