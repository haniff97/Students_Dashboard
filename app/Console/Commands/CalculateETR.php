<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CalculateETR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-e-t-r';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
public function handle()
{
    $students = \App\Models\Students::all();
    $grades = \App\Models\Grade::orderBy('min_mark')->get();

    foreach ($students as $student) {
        $latestMark = null;
        $latestGrade = null;

        if ($student->uasa_m && $student->uasa_g !== 'TH') {
            $latestMark = $student->uasa_m;
            $latestGrade = $student->uasa_g;
        } elseif ($student->ppt_m && $student->ppt_g !== 'TH') {
            $latestMark = $student->ppt_m;
            $latestGrade = $student->ppt_g;
        } elseif ($student->pa1_m && $student->pa1_g !== 'TH') {
            $latestMark = $student->pa1_m;
            $latestGrade = $student->pa1_g;
        }

        if (!$latestMark || !$latestGrade) {
            $this->warn("Skipping {$student->name} — No latest mark.");
            continue;
        }

        // Sanitize grade
        $latestGrade = strtoupper(trim($latestGrade));

        // Find current grade
        $current = $grades->firstWhere('grade', $latestGrade);

        if (!$current) {
            $this->warn("Grade '{$latestGrade}' not found for {$student->name}");
            continue;
        }

        // Find next higher grade
        $next = $grades->filter(fn($g) => $g->min_mark > $current->min_mark)->first();



        if (!$next) {
            $student->etr_m = $latestMark;
            $student->etr_g = $current->grade;
            $student->save();

            $this->info("{$student->name} already has the highest grade ({$current->grade}), ETR remains same.");
            continue;
        }


        // Set ETR
        $student->etr_m = $next->min_mark;
        $student->etr_g = $next->grade;
        $student->save();

        $this->info("ETR set for {$student->name}: {$next->mark} ({$next->grade})");
    }

    $this->info('ETR calculation complete.');
}


}
