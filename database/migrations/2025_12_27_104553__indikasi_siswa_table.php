<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ========== TABEL BARU (MATCH SQL) ==========
     * - Nama tabel: _indikasi_siswa
     * - Menyimpan hasil deteksi mabuk dari model AI
     * - Terpisah dari tabel absensi
     * - Data dari Python/AI model
     * ===========================================
     */
    public function up(): void
    {
        Schema::create('_indikasi_siswa', function (Blueprint $table) {
            $table->id('id_indikasi');
            $table->foreignId('id_absensi'); // FK ke absensi
            $table->string('session_id', 100)->nullable(); // Contoh: "session_20251227_152853"
            $table->enum('attendance_type', ['in', 'out']); // Masuk atau keluar
            $table->enum('final_decision', ['SOBER', 'DRUNK INDICATION', 'INCONCLUSIVE']);
            
            // Data dari AI model
            $table->integer('frames_used')->nullable(); // Jumlah frame yang dianalisis
            $table->decimal('average_prob_sober', 5, 3)->nullable(); // Rata-rata probabilitas
            $table->decimal('median_prob_sober', 5, 3)->nullable(); // Median probabilitas
            
            // Path/storage
            $table->string('face_image', 255)->nullable(); // Path foto wajah
            $table->string('frames_dir', 255)->nullable(); // Directory frames video
            
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key
            $table->foreign('id_absensi', 'fk_indikasi_absensi')
                  ->references('id_absensi')
                  ->on('absensi')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            // Indexes
            $table->index('session_id');
            $table->index('final_decision');
            $table->index('attendance_type');
            $table->index(['id_absensi', 'attendance_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_indikasi_siswa');
    }
};