<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source_url')->nullable(); // null if pasted manually
            $table->string('source_site')->nullable(); // linkedin, indeed, greenhouse, manual...
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->text('raw_description'); // always keep the raw text, scraped or pasted
            $table->json('required_skills')->nullable();
            $table->json('preferred_skills')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('qualifications')->nullable();
            $table->string('experience_requirement')->nullable();
            $table->string('industry')->nullable();
            $table->json('keywords')->nullable();
            $table->json('technologies')->nullable();
            $table->json('soft_skills')->nullable();
            $table->string('salary_range')->nullable();
            $table->enum('work_setup', ['remote', 'hybrid', 'onsite'])->nullable();
            $table->enum('extraction_status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
