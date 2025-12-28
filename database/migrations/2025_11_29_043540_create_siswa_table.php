<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ========== STRUKTUR BARU (MATCH SQL + EXTENDED) ==========
     * - Nama tabel: siswa (bukan students)
     * - Primary Key: kode_siswa VARCHAR (bukan auto-increment)
     * - Contoh: "2307027"
     * - TAMBAHAN: jenis_kelamin, tanggal_lahir (request user)
     * ==========================================================
     */
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->string('kode_siswa', 30)->primary(); // VARCHAR PK
            $table->string('nama_siswa', 150);
            $table->string('nomor_wali', 20)->nullable(); // Nomor WhatsApp wali
            $table->string('id_kelas', 30); // Foreign key ke kelas
            
            // ========== FIELD TAMBAHAN (REQUEST USER) ==========
            $table->enum('jenis_kelamin', ['L', 'P']); // L = Laki-laki, P = Perempuan
            $table->date('tanggal_lahir');
            // ===================================================
            
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign key
            $table->foreign('id_kelas', 'fk_siswa_kelas')
                  ->references('id_kelas')
                  ->on('kelas')
                  ->onUpdate('cascade');
            
            // Indexes
            $table->index('id_kelas');
            $table->index('nama_siswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};