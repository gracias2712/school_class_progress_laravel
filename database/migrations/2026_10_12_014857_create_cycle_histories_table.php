<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cycle_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('changed_by_user_id'); // Waka yang mengganti
            $table->string('from_cycle', 10); // Dari putaran berapa
            $table->string('to_cycle', 10);   // Menjadi putaran berapa
            $table->string('change_reason')->nullable(); // Alasan ganti (opsional)
            $table->timestamps();

            // Relasi ke tabel users
            $table->foreign('changed_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycle_histories');
    }
};
