<?php
// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     public function up(): void
//     {
//         Schema::create('whatsapp_notifications', function (Blueprint $table) {
//             $table->id();
//             $table->foreignId('student_id')->constrained()->onDelete('cascade');
//             $table->foreignId('detection_id')->nullable()->constrained()->onDelete('set null');
            
//             $table->enum('type', ['attendance', 'drunk_detection', 'late', 'absent', 'general']);
//             $table->string('recipient_phone');
//             $table->string('recipient_name');
            
//             $table->string('title');
//             $table->text('message');
            
//             $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'read'])->default('pending');
//             $table->text('whatsapp_response')->nullable();
//             $table->string('whatsapp_message_id')->nullable();
            
//             $table->timestamp('sent_at')->nullable();
//             $table->integer('retry_count')->default(0);
//             $table->timestamps();
            
//             $table->index(['status', 'created_at']);
//         });
//     }

//     public function down(): void
//     {
//         Schema::dropIfExists('whatsapp_notifications');
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
     * ========== FILE INI TETAP DIPERTAHANKAN ==========
     * Fungsi:
     * - Tracking status pengiriman notifikasi WhatsApp
     * - Monitoring retry & failure
     * - Audit log untuk bukti notifikasi terkirim
     * - Integrasi dengan WhatsApp API
     * ==================================================
     */
    public function up(): void
    {
        Schema::create('whatsapp_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->onDelete('set null');
            
            $table->enum('type', ['attendance', 'drunk_detection', 'late', 'absent', 'general']);
            $table->string('recipient_phone'); // Nomor WhatsApp penerima (dari parent_phone)
            $table->string('recipient_name');
            
            $table->string('title');
            $table->text('message');
            
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'read'])->default('pending');
            $table->text('whatsapp_response')->nullable(); // Response dari WhatsApp API
            $table->string('whatsapp_message_id')->nullable(); // Message ID dari WhatsApp
            
            $table->timestamp('sent_at')->nullable();
            $table->integer('retry_count')->default(0); // Jumlah retry jika gagal
            $table->timestamps();
            
            // Indexes untuk monitoring & reporting
            $table->index(['status', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notifications');
    }
};