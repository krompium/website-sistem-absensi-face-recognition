<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique(); // Nomor Induk Siswa
            $table->string('name');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date');
            $table->string('phone')->nullable();
            $table->string('parent_phone'); // Untuk notifikasi WhatsApp
            $table->string('parent_name');
            $table->text('address')->nullable();
            
            // Face Recognition Data
            $table->string('face_image')->nullable(); // Path foto wajah
            $table->json('face_embeddings')->nullable(); // Vector embeddings untuk face recognition
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Untuk soft delete
            
            // Indexes
            $table->index('nis');
            $table->index('class_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};