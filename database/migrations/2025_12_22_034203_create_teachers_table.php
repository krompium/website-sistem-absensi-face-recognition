<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ========== FILE BARU: TABEL GURU ==========
     * Tabel ini sesuai dengan desain awal Anda:
     * - NIP sebagai Primary Key
     * - Username & Password untuk login
     * - Foreign Key ke tabel classes
     * ===========================================
     */
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->string('nip')->primary(); // NIP sebagai Primary Key (bukan auto-increment)
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            // Indexes untuk performa query
            $table->index('class_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};