<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('job_url')->nullable()->after('notes');
            $table->unsignedSmallInteger('match_score')->nullable()->after('job_url');
            $table->date('interview_date')->nullable()->after('match_score');
            $table->date('follow_up_date')->nullable()->after('interview_date');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['job_url', 'match_score', 'interview_date', 'follow_up_date']);
        });
    }
};
