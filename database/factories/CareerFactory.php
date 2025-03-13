<?php

namespace Database\Factories;

use App\Models\Career;
use Illuminate\Database\Eloquent\Factories\Factory;

class CareerFactory extends Factory
{
    protected $model = Career::class;

    public function definition()
    {
        return [
            'job_role' => $this->faker->jobTitle(),
            'job_category' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'job_type' => $this->faker->randomElement(['full_time', 'part_time', 'full_time_on_site', 'full_time_remote', 'part_time_on_site', 'part_time_remote']),
            'address' => $this->faker->address(),
            'deadline' => $this->faker->date(),
        ];
    }
}
