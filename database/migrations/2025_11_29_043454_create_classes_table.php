<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     public function up(): void
//     {
//         Schema::create('classes', function (Blueprint $table) {
//             $table->id();
//             $table->string('name'); // Contoh: "X RPL 1"
//             $table->string('grade'); // Contoh: "10", "11", "12"
//             $table->string('major')->nullable(); // RPL, TKJ
//             $table->string('homeroom_teacher')->nullable();
//             $table->integer('capacity')->default(30);
//             $table->boolean('is_active')->default(true);
//             $table->timestamps();
//         });
//     }

//     public function down(): void
//     {
//         Schema::dropIfExists('classes');
//     }
// };

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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "X RPL 1", "XII TKJ 2"
            $table->string('grade'); // Tingkat: "10", "11", "12"
            $table->string('major')->nullable(); // Jurusan: RPL, TKJ, MM, dll
            
            // ========== PERUBAHAN: TAMBAH SEQUENCE ==========
            $table->integer('sequence')->nullable(); // Urutan: 1, 2, 3, dst
            // ===============================================
            
            $table->string('homeroom_teacher')->nullable();
            $table->integer('capacity')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // ========== PERUBAHAN: TAMBAH INDEX ==========
            $table->index(['grade', 'major', 'sequence']);
            // ============================================
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};