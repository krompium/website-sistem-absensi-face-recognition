<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up(): void
//     {
//         Schema::create('attendances', function (Blueprint $table) {
//             $table->id();
//             $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
//             $table->date('date');
//             $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent'); // TAMBAHKAN INI
//             $table->time('check_in_time')->nullable();
//             $table->time('check_out_time')->nullable();
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('attendances');
//     }
// };

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ========== PERUBAHAN BESAR ==========
     * File ini menggabungkan data absensi + deteksi mabuk
     * Menggantikan tabel 'detections' yang dihapus
     * 
     * FIELD BARU:
     * - attendance_image: Gambar_Absen dari Face Recognition
     * - drunk_confidence_score: Confidence score dari model AI
     * - drunk_status: Status deteksi (sober/suspected/drunk)
     * - red_eyes, unstable_posture: Detail deteksi
     * - parent_notified: Tracking notifikasi ke orang tua
     * =====================================
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); // ID_Absen
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade'); // NIS (FK)
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
            $table->time('check_in_time')->nullable(); // Jam_Masuk
            $table->time('check_out_time')->nullable(); // Jam_Keluar
            
            // ========== PERUBAHAN: DATA FACE RECOGNITION & DRUNK DETECTION ==========
            $table->string('attendance_image')->nullable(); // Gambar_Absen (foto saat face recognition)
            $table->decimal('drunk_confidence_score', 5, 2)->nullable(); // Confidence score 0-100
            $table->enum('drunk_status', ['sober', 'suspected', 'drunk'])->default('sober');
            
            // Detail deteksi untuk keperluan analisis
            $table->boolean('red_eyes')->default(false); // Deteksi mata merah
            $table->boolean('unstable_posture')->default(false); // Deteksi postur tidak stabil
            $table->text('ai_notes')->nullable(); // Catatan dari AI analysis
            
            // Tracking notifikasi ke orang tua
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            // ========================================================================
            
            $table->text('notes')->nullable(); // Catatan manual dari guru/admin
            $table->timestamps();
            
            // ========== PERUBAHAN: INDEXES & CONSTRAINTS ==========
            $table->index(['student_id', 'date']);
            $table->index(['date', 'status']);
            $table->index('drunk_status'); // BARU: untuk filter siswa dengan indikasi mabuk
            $table->unique(['student_id', 'date']); // BARU: prevent duplicate attendance per hari
            // =====================================================
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};