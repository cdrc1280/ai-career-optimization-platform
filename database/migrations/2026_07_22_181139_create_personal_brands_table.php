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
        Schema::create('personal_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('headline')->nullable();
            $table->text('about_section')->nullable();
            $table->text('elevator_pitch')->nullable();
            $table->json('linkedin_posts')->nullable();
            $table->text('github_bio')->nullable();
            $table->json('portfolio_copy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_brands');
    }
};
