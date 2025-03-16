<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CareerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('careers')->insert([
            [
                'job_role'     => 'Software Engineer',
                'job_category' => 'IT & Software',
                'description'  => 'Responsible for developing and maintaining web applications.',
                'job_type'     => 'full_time_remote',
                'address'      => 'New York, USA',
                'deadline'     => Carbon::now()->addDays(30),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'job_role'     => 'Graphic Designer',
                'job_category' => 'Design & Multimedia',
                'description'  => 'Creating visual content for various digital platforms.',
                'job_type'     => 'part_time_on_site',
                'address'      => 'Los Angeles, USA',
                'deadline'     => Carbon::now()->addDays(45),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'job_role'     => 'Data Analyst',
                'job_category' => 'Data Science',
                'description'  => 'Analyzing and interpreting complex data sets.',
                'job_type'     => 'full_time_on_site',
                'address'      => 'San Francisco, USA',
                'deadline'     => Carbon::now()->addDays(60),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
