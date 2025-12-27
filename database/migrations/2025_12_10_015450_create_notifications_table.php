<?php

// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
//     /**
//      * Run the migrations.
//      */
//     public function up(): void
//     {
//         Schema::create('notifications', function (Blueprint $table) {
//             $table->uuid('id')->primary();
//             $table->string('type');
//             $table->morphs('notifiable');
//             $table->text('data');
//             $table->timestamp('read_at')->nullable();
//             $table->timestamps();
//         });
//     }

//     /**
//      * Reverse the migrations.
//      */
//     public function down(): void
//     {
//         Schema::dropIfExists('notifications');
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
     * - In-app notifications untuk dashboard admin
     * - Notifikasi real-time di interface (bell icon)
     * - Berbeda dengan WhatsApp notifications
     * ==================================================
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable'); // Polymorphic relation (User, Teacher, dll)
            $table->text('data'); // JSON data notifikasi
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};