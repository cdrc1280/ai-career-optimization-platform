<?php

namespace Database\Factories;

use App\Models\Resume;
use App\Models\ResumeVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResumeVersionFactory extends Factory
{
    protected $model = ResumeVersion::class;

    public function definition(): array
    {
        return [
            'resume_id'  => Resume::factory(),
            'label'      => 'Master Resume',
            'content'    => [
                'personal_info'      => ['full_name' => $this->faker->name(), 'email' => $this->faker->email()],
                'professional_title' => $this->faker->jobTitle(),
                'summary'            => $this->faker->paragraph(),
                'skills'             => ['PHP', 'Laravel', 'Vue.js'],
                'work_experience'    => [],
                'education'          => [],
            ],
            'is_master'  => true,
        ];
    }

    public function notMaster(): static
    {
        return $this->state(['is_master' => false, 'label' => 'Optimized Resume']);
    }
}
