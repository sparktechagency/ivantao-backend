<?php

namespace Database\Seeders;

use App\Models\ServiceSubCategory;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ServiceSubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first available service category. You may customize this logic.
        $serviceCategory = ServiceCategory::first();

        if (!$serviceCategory) {
            // If no service category is found, exit the seeder.
            return;
        }

        // Create some sample service sub-categories
        ServiceSubCategory::create([
            'service_category_id' => $serviceCategory->id,
            'name'                => 'Basic Maintenance',
            'image'               => 'basic_maintenance.png',
            'created_at'          => Carbon::now(),
            'updated_at'          => Carbon::now(),
        ]);

        ServiceSubCategory::create([
            'service_category_id' => $serviceCategory->id,
            'name'                => 'Advanced Repair',
            'image'               => 'advanced_repair.png',
            'created_at'          => Carbon::now(),
            'updated_at'          => Carbon::now(),
        ]);

        ServiceSubCategory::create([
            'service_category_id' => $serviceCategory->id,
            'name'                => 'Emergency Services',
            'image'               => 'emergency_services.png',
            'created_at'          => Carbon::now(),
            'updated_at'          => Carbon::now(),
        ]);
    }
}
