<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cover_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_version_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('tone', ['professional', 'friendly', 'executive', 'technical'])
                ->default('professional');
            $table->text('content');
            $table->string('exported_pdf_path')->nullable();
            $table->string('exported_docx_path')->nullable();
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cover_letter_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', [
                'saved', 'applied', 'assessment', 'interview', 'offer', 'rejected', 'accepted',
            ])->default('saved');
            $table->date('applied_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Full audit trail of status changes, so the dashboard can show
        // "time in each stage" and the admin panel can show funnel analytics.
        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_history');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('cover_letters');
    }
};
