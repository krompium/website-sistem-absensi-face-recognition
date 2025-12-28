<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ========== STRUKTUR BARU (MATCH SQL) ==========
     * - Nama tabel: absensi (bukan attendances)
     * - Primary Key: id_absensi (auto-increment)
     * - Status: HADIR/IZIN/SAKIT/ALPA
     * - Jam menggunakan DATETIME (bukan TIME)
     * - TANPA field deteksi mabuk (pindah ke _indikasi_siswa)
     * ===============================================
     */
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id('id_absensi');
            $table->string('kode_siswa', 30);
            $table->string('id_kelas', 30);
            $table->date('tanggal');
            $table->dateTime('jam_masuk')->nullable();
            $table->dateTime('jam_keluar')->nullable();
            $table->enum('status', ['HADIR', 'IZIN', 'SAKIT', 'ALPA'])->default('HADIR');
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign keys
            $table->foreign('kode_siswa', 'fk_absensi_siswa')
                  ->references('kode_siswa')
                  ->on('siswa')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->foreign('id_kelas', 'fk_absensi_kelas')
                  ->references('id_kelas')
                  ->on('kelas')
                  ->onUpdate('cascade');
            
            // Indexes & Constraints
            $table->index('tanggal');
            $table->index('status');
            $table->unique(['kode_siswa', 'tanggal'], 'uniq_absensi_harian');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};