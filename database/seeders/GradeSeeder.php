<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('grades')->insert([
            ['min_mark' => 0,  'grade' => 'G',  'rank' => 1],
            ['min_mark' => 40, 'grade' => 'E',  'rank' => 2],
            ['min_mark' => 45, 'grade' => 'D',  'rank' => 3],
            ['min_mark' => 50, 'grade' => 'C',  'rank' => 4],
            ['min_mark' => 55, 'grade' => 'C+', 'rank' => 5],
            ['min_mark' => 60, 'grade' => 'B',  'rank' => 6],
            ['min_mark' => 65, 'grade' => 'B+', 'rank' => 7],
            ['min_mark' => 70, 'grade' => 'A-', 'rank' => 8],
            ['min_mark' => 80, 'grade' => 'A',  'rank' => 9],
            ['min_mark' => 90, 'grade' => 'A+', 'rank' => 10],
        ]);
    }
}
