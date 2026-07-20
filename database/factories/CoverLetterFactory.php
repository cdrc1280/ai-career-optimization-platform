<?php

namespace Database\Factories;

use App\Models\CoverLetter;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Models\ResumeVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoverLetterFactory extends Factory
{
    protected $model = CoverLetter::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'job_posting_id'    => JobPosting::factory(),
            'resume_version_id' => ResumeVersion::factory(),
            'tone'              => $this->faker->randomElement(['professional', 'friendly', 'executive', 'technical']),
            'content'           => $this->faker->paragraphs(3, true),
        ];
    }
}
