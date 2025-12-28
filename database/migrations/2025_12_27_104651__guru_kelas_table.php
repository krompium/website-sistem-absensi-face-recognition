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
     * - Nama tabel: _guru_kelas
     * - Junction table untuk many-to-many
     * - Guru (dari users) bisa mengajar di banyak kelas
     * - Satu kelas bisa diajar oleh banyak guru
     * ===========================================
     */
    public function up(): void
    {
        Schema::create('_guru_kelas', function (Blueprint $table) {
            $table->id('id_guru_kelas');
            $table->unsignedBigInteger('id_user'); // FK ke users
            $table->string('id_kelas', 30); // FK ke kelas
            $table->timestamp('created_at')->useCurrent();
            
            // Foreign keys
            $table->foreign('id_user', 'fk_gk_user')
                  ->references('id_user')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->foreign('id_kelas', 'fk_gk_kelas')
                  ->references('id_kelas')
                  ->on('kelas')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            // Unique constraint
            $table->unique(['id_user', 'id_kelas'], 'uniq_guru_kelas');
            
            // Indexes
            $table->index('id_user');
            $table->index('id_kelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('_guru_kelas');
    }
};