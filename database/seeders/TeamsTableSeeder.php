<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Liverpool',
                'strength' => rand(100, 10000) / 100,
                'country' => 'England',
            ],
            [
                'name' => 'Manchester City',
                'strength' => rand(100, 10000) / 100,
                'country' => 'England',
            ],
            [
                'name' => 'Chelsea',
                'strength' => rand(100, 10000) / 100,
                'country' => 'England',
            ],
            [
                'name' => 'Arsenal',
                'strength' => rand(100, 10000) / 100,
                'country' => 'England',
            ],
            [
                'name' => 'Real Madrid',
                'strength' => rand(100, 10000) / 100,
                'country' => 'Spain',
            ],
            [
                'name' => 'Barcelona',
                'strength' => rand(100, 10000) / 100,
                'country' => 'Spain',
            ],
            [
                'name' => 'Valencia',
                'strength' => rand(100, 10000) / 100,
                'country' => 'Spain',
            ],
            [
                'name' => 'Milan',
                'strength' => rand(100, 10000) / 100,
                'country' => 'Italy',
            ],
        ];

        DB::table('teams')->insert($teams);
    }
}
