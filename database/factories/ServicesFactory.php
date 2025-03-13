<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Services;
use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\ServiceSubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicesFactory extends Factory
{
    protected $model = Services::class;

    public function definition()
    {
        return [
            'provider_id' => User::factory(),
            'service_category_id' => ServiceCategory::factory(),
            'service_sub_categories_id' => ServiceSubCategory::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'image' => $this->faker->imageUrl(200, 200, 'business', true),
            'service_type' => $this->faker->randomElement(['virtual', 'in-person']),
        ];
    }
}
