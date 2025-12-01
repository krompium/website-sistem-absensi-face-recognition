<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            
            // Deteksi mabuk
            $table->enum('drunk_status', ['sober', 'suspected', 'drunk'])->default('sober');
            $table->decimal('drunk_confidence', 5, 2)->nullable(); // Confidence score 0-100
            
            // Detail deteksi (bisa dari sensor atau analisis image)
            $table->json('detection_data')->nullable(); // {alcohol_level, eye_redness, behavioral_signs}
            
            // Image analysis results
            $table->boolean('red_eyes')->default(false);
            $table->boolean('unstable_posture')->default(false);
            $table->text('ai_analysis')->nullable();
            
            $table->enum('severity', ['low', 'medium', 'high'])->default('low');
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['drunk_status', 'created_at']);
            $table->index('severity');
            $table->index('notification_sent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detections');
    }
};