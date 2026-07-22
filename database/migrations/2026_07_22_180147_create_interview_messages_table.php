<?php

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
        Schema::create('interview_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_interview_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // e.g., 'user', 'ai'
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_messages');
    }
};
