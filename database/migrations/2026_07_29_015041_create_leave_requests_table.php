<?php



use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;



return new class extends Migration

{

    public function up(): void

    {

        Schema::create('leave_requests', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('student_id'); // Siswa yang bersangkutan

            $table->unsignedBigInteger('applicant_id'); // Siapa yang mengajukan (Bisa Siswa itu sendiri, atau Orang Tuanya)

            $table->unsignedBigInteger('verified_by')->nullable(); // ID Wali Kelas / BK yang ACC

            $table->date('start_date');

            $table->date('end_date');

            $table->text('reason');

            $table->string('document_path'); // Tempat menyimpan file foto/PDF surat izin

            $table->enum('status_approval', ['Pending', 'Approved', 'Rejected'])->default('Pending');

            $table->timestamps();



            // Relasi (Opsional tapi sangat disarankan agar data rapi)

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');

            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');

            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');

        });

    }



    public function down(): void

    {

        Schema::dropIfExists('leave_requests');

    }

};