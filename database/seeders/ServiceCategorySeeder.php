<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Attempt to fetch a provider user; if not found, provider_id will be null.
        $provider = User::where('role', 'provider')->first();

        // Create a few sample service categories
        ServiceCategory::create([
            'provider_id' => $provider ? $provider->id : null,
            'name'        => 'Plumbing Services',
            'icon'        => 'plumbing.png',
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        ServiceCategory::create([
            'provider_id' => $provider ? $provider->id : null,
            'name'        => 'Electrical Services',
            'icon'        => 'electrical.png',
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);

        ServiceCategory::create([
            'provider_id' => $provider ? $provider->id : null,
            'name'        => 'Cleaning Services',
            'icon'        => 'cleaning.png',
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ]);
    }
}
