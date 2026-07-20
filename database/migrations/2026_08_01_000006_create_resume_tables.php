<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The master resume: the single source of truth. Never overwritten by
        // AI output — only ever edited directly by the user.
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('file_path'); // preserved original upload (S3/local)
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->json('parsed_data')->nullable(); // structured extraction result
            $table->enum('parse_status', ['pending', 'processing', 'completed', 'failed', 'needs_review'])
                ->default('pending');
            $table->text('parse_error')->nullable();
            $table->timestamps();
        });

        // Every AI-generated (or user-branched) resume becomes a version in a
        // tree rooted at the master resume — this is what "Master Resume ->
        // Backend Developer Resume -> Senior Full Stack Resume" maps to.
        Schema::create('resume_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_version_id')->nullable()
                ->constrained('resume_versions')->nullOnDelete();
            $table->foreignId('job_posting_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->string('label'); // e.g. "Laravel Developer Resume"
            $table->json('content'); // structured resume content for this version
            $table->string('exported_pdf_path')->nullable();
            $table->string('exported_docx_path')->nullable();
            $table->boolean('is_master')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_versions');
        Schema::dropIfExists('resumes');
    }
};
