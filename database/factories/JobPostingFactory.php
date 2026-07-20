<?php

namespace Database\Factories;

use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobPostingFactory extends Factory
{
    protected $model = JobPosting::class;

    public function definition(): array
    {
        return [
            'user_id'                => User::factory(),
            'company_name'           => $this->faker->company(),
            'job_title'              => $this->faker->jobTitle(),
            'raw_description'        => $this->faker->paragraphs(3, true),
            'required_skills'        => ['PHP', 'Laravel', 'MySQL'],
            'preferred_skills'       => ['Redis', 'Docker'],
            'responsibilities'       => [$this->faker->sentence(), $this->faker->sentence()],
            'qualifications'         => ['5+ years experience', 'Bachelor\'s degree'],
            'experience_requirement' => '3-5 years',
            'industry'               => 'Technology',
            'keywords'               => ['backend', 'api', 'rest'],
            'technologies'           => ['PHP', 'Laravel'],
            'soft_skills'            => ['Communication', 'Teamwork'],
            'work_setup'             => $this->faker->randomElement(['remote', 'hybrid', 'onsite']),
            'extraction_status'      => 'completed',
        ];
    }
}
