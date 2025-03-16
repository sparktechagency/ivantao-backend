<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Services;
use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\ServiceSubCategory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a provider user (with role 'provider'); if not found, set provider_id to null
        $provider = User::where('role', 'provider')->first();
        $providerId = $provider ? $provider->id : null;

        // Get a service category; if not available, exit seeder
        $serviceCategory = ServiceCategory::first();
        if (!$serviceCategory) {
            return;
        }

        // Get a service sub-category belonging to the fetched service category; exit if not available
        $serviceSubCategory = ServiceSubCategory::where('service_category_id', $serviceCategory->id)->first();
        if (!$serviceSubCategory) {
            return;
        }

        // Create sample service records
        Services::create([
            'provider_id' => $providerId,
            'service_category_id' => $serviceCategory->id,
            'service_sub_categories_id' => $serviceSubCategory->id,
            'title'       => 'Premium Plumbing Service',
            'description' => 'High quality plumbing services for residential and commercial needs.',
            'price'       => '150',
            'image'       => 'plumbing_service.png',
            'service_type'=> 'in-person',
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        Services::create([
            'provider_id' => $providerId,
            'service_category_id' => $serviceCategory->id,
            'service_sub_categories_id' => $serviceSubCategory->id,
            'title'       => 'Online Electrical Consultation',
            'description' => 'Expert electrical consultation available virtually.',
            'price'       => '50',
            'image'       => 'electrical_consultation.png',
            'service_type'=> 'virtual',
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);
    }
}
