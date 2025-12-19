<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('detection_id')->nullable()->constrained()->onDelete('set null');
            
            $table->enum('type', ['attendance', 'drunk_detection', 'late', 'absent', 'general']);
            $table->string('recipient_phone');
            $table->string('recipient_name');
            
            $table->string('title');
            $table->text('message');
            
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'read'])->default('pending');
            $table->text('whatsapp_response')->nullable();
            $table->string('whatsapp_message_id')->nullable();
            
            $table->timestamp('sent_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notifications');
    }
};