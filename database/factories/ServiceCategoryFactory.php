<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    public function definition()
    {
        return [
            'provider_id' => User::factory(),
            'name' => $this->faker->word(),
            'icon' => $this->faker->imageUrl(),
        ];
    }
}
