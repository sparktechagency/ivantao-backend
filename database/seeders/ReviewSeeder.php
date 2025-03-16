<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Services;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get any existing users and services
        $user = User::first(); // Get the first user
        $service = Services::first(); // Get the first service

        if (!$user || !$service) {
            return;
        }

        Review::create([
            'user_id'    => $user->id,
            'service_id' => $service->id,
            'rating'     => 5,
            'comment'    => 'Excellent service, highly recommended!',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Review::create([
            'user_id'    => $user->id,
            'service_id' => $service->id,
            'rating'     => 4,
            'comment'    => 'Good service but could be improved.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
