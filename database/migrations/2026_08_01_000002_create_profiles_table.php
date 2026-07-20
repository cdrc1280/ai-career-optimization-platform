<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Personal information
            $table->string('full_name')->nullable();
            $table->string('professional_title')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('avatar_path')->nullable();

            // Career information
            $table->unsignedTinyInteger('years_of_experience')->nullable();
            $table->json('preferred_roles')->nullable();
            $table->json('preferred_industries')->nullable();
            $table->json('preferred_locations')->nullable();
            $table->unsignedInteger('expected_salary_min')->nullable();
            $table->unsignedInteger('expected_salary_max')->nullable();
            $table->string('currency', 3)->default('PHP');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'freelance', 'internship'])
                ->nullable();
            $table->text('career_goals')->nullable();

            $table->unsignedTinyInteger('completion_percentage')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
