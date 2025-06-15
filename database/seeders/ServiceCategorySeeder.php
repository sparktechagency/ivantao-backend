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
        $provider = User::where('role', 'provider')->first();

        // Create a few sample service categories
        ServiceCategory::create([
            'name'        => 'Plumbing Services',
            'icon'        => 'plumbing.png',
        ]);
        ServiceCategory::create([
            'name'        => 'Electrical Services',
            'icon'        => 'electrical.png',
        ]);
        ServiceCategory::create([
            'name'        => 'Cleaning Services',
            'icon'        => 'cleaning.png',
        ]);
    }
}
