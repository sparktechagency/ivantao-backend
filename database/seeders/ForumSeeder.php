<?php

namespace Database\Seeders;

use App\Models\CommunityForum;
use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ForumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a user; if not found, set user_id to null
        $user = User::first();
        $userId = $user ? $user->id : null;

        // Get a service category; if not available, exit seeder
        $serviceCategory = ServiceCategory::first();
        if (!$serviceCategory) {
            return;
        }

        // Create sample community forum posts
        CommunityForum::create([
            'user_id'       => $userId,
            'categories_id' => $serviceCategory->id,
            'title'         => 'Best Practices for Plumbing',
            'comment'       => 'What are the best ways to maintain home plumbing systems?',
            'image'         => 'plumbing_tips.png',
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);

        CommunityForum::create([
            'user_id'       => $userId,
            'categories_id' => $serviceCategory->id,
            'title'         => 'Choosing the Right Electrical Wiring',
            'comment'       => 'Which wiring type is best for residential use?',
            'image'         => 'electrical_wiring.png',
            'created_at'    => Carbon::now(),
            'updated_at'    => Carbon::now(),
        ]);
    }
}
