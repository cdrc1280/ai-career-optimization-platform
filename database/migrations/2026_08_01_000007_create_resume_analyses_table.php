<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per (resume version x job posting) comparison. Kept even
        // after a new version is generated so score history/trends are
        // queryable for the dashboard and analytics.
        Schema::create('resume_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('overall_match_score')->nullable();
            $table->unsignedTinyInteger('skills_match_score')->nullable();
            $table->unsignedTinyInteger('experience_match_score')->nullable();
            $table->unsignedTinyInteger('education_match_score')->nullable();
            $table->unsignedTinyInteger('ats_compatibility_score')->nullable();
            $table->unsignedTinyInteger('keyword_coverage_score')->nullable();
            $table->unsignedTinyInteger('industry_alignment_score')->nullable();

            $table->json('matching_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->json('present_keywords')->nullable();
            $table->json('missing_keywords')->nullable();
            $table->json('recommended_keywords')->nullable();
            $table->json('ats_issues')->nullable();
            $table->json('score_explanations')->nullable();
            $table->json('skill_gap_details')->nullable();
            $table->json('career_recommendations')->nullable();
            $table->json('interview_prep')->nullable();
            $table->json('recruiter_review')->nullable();
            $table->json('integrity_flags')->nullable();

            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_analyses');
    }
};
