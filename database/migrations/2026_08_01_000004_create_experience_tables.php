<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('company');
            $table->string('title');
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            // Bullet points stored as a JSON array of strings — these are the
            // atomic units the AI reorders/rewrites during optimization, and
            // each one traces back to something the user actually wrote.
            $table->json('responsibilities')->nullable();
            $table->json('achievements')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('technologies')->nullable();
            $table->string('project_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
        Schema::dropIfExists('work_experiences');
        Schema::dropIfExists('educations');
    }
};
