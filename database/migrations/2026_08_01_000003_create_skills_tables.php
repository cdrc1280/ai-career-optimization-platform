<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalized skill catalogue lets us do fast matching/analytics
        // ("most common skills") instead of grepping free-text JSON.
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->nullable(); // e.g. "Backend", "Soft Skill"
            $table->timestamps();
        });

        Schema::create('profile_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->enum('proficiency', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable();
            $table->timestamps();

            $table->unique(['profile_id', 'skill_id']);
        });

        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('issuing_organization')->nullable();
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('credential_url')->nullable();
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('proficiency', ['basic', 'conversational', 'fluent', 'native'])->default('conversational');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('profile_skill');
        Schema::dropIfExists('skills');
    }
};
