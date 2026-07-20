<?php

namespace Database\Factories;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResumeFactory extends Factory
{
    protected $model = Resume::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'original_filename' => $this->faker->word() . '.pdf',
            'file_path'         => 'resumes/test/' . $this->faker->uuid() . '.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => $this->faker->numberBetween(50000, 500000),
            'parse_status'      => 'completed',
        ];
    }
}
