<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use App\Models\ServiceSubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceSubCategory>
 */
class ServiceSubCategoryFactory extends Factory
{
    protected $model = ServiceSubCategory::class;

    public function definition()
    {
        return [
            'service_category_id' => ServiceCategory::factory(), 
            'name' => $this->faker->word(),
            'image' => $this->faker->imageUrl(),
        ];
    }
}
