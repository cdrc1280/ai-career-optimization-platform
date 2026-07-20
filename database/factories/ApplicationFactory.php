<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'job_posting_id' => JobPosting::factory(),
            'status'         => ApplicationStatus::Saved,
            'notes'          => $this->faker->optional()->sentence(),
            'applied_at'     => $this->faker->optional()->date(),
        ];
    }
}
