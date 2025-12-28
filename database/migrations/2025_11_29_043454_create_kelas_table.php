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
     * - Nama tabel: kelas (bukan classes)
     * - Primary Key: id_kelas VARCHAR (bukan auto-increment)
     * - Contoh: "XIIRPL", "XTKJ1"
     * - Unique constraint: tingkat + jurusan + urutan
     * ===============================================
     */
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->string('id_kelas', 30)->primary(); // VARCHAR PK
            $table->string('tingkat', 10); // "X", "XI", "XII"
            $table->string('jurusan', 50); // "Rekayasa Perangkat Lunak", "TKJ", dll
            $table->integer('urutan'); // 1, 2, 3, dst
            $table->timestamp('created_at')->useCurrent();
            
            // Unique constraint untuk kombinasi kelas
            $table->unique(['tingkat', 'jurusan', 'urutan'], 'uniq_kelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};