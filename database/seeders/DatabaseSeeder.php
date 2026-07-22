<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(PlanSeeder::class);

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'     => 'Alex Mercer',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        $profile = $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name'             => 'Alex Mercer',
                'professional_title'    => 'Senior Full-Stack Engineer',
                'phone'                 => '+1 (555) 234-5678',
                'location'              => 'San Francisco, CA (Open to Remote)',
                'linkedin_url'          => 'https://linkedin.com/in/alexmercer',
                'github_url'            => 'https://github.com/alexmercer',
                'portfolio_url'         => 'https://alexmercer.dev',
                'career_goals'          => 'To lead high-impact engineering teams building scalable cloud architecture.',
                'years_of_experience'   => 6,
                'completion_percentage' => 95,
            ]
        );

        $resume = $user->resumes()->firstOrCreate(
            ['original_filename' => 'Alex_Mercer_Master_Resume.pdf'],
            [
                'original_filename' => 'Alex_Mercer_Master_Resume.pdf',
                'file_path'         => 'resumes/demo_master.pdf',
                'mime_type'         => 'application/pdf',
                'file_size'         => 102400,
                'parse_status'      => 'completed',
            ]
        );

        $version = $resume->masterVersion()->firstOrCreate(
            ['resume_id' => $resume->id],
            [
                'label'     => 'Master Version v1.0',
                'is_master' => true,
                'content'   => [
                    'professional_title' => 'Senior Full-Stack Engineer',
                    'summary'            => 'Architect with 6+ years of experience specializing in Laravel, Vue 3, cloud infrastructure, and microservices.',
                    'skills'             => ['Laravel', 'Vue.js', 'TypeScript', 'PostgreSQL', 'Docker', 'AWS', 'Redis', 'Tailwind CSS'],
                ],
            ]
        );

        $job = $user->jobPostings()->firstOrCreate(
            ['job_title' => 'Senior Full-Stack Developer'],
            [
                'company_name'      => 'Stripe',
                'job_title'         => 'Senior Full-Stack Developer',
                'salary_range'      => '$140,000 - $175,000',
                'work_setup'        => 'remote',
                'extraction_status' => 'completed',
                'raw_description'   => 'We are seeking a Senior Full-Stack Engineer with experience in Laravel, Vue.js, PostgreSQL, and scalable systems.',
            ]
        );

        $user->applications()->firstOrCreate(
            ['job_posting_id' => $job->id],
            [
                'status'            => 'interview',
                'applied_at'        => now()->subDays(5),
                'resume_version_id' => $version->id,
            ]
        );
    }
}
