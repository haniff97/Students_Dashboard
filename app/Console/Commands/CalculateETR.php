<?php

namespace App\Console\Commands;

use App\Models\Grade;
use App\Models\Students;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateETR extends Command
{
    protected $signature = 'app:calculate-e-t-r 
                            {--chunk=1000 : Number of records to process at a time}
                            {--memory=256 : Memory limit in MB}';
    
    protected $description = 'Calculate ETR for all students in chunks';

    public function handle()
    {
        $startMemory = memory_get_usage(true);
        $this->info("Starting ETR calculation...");
        
        // Set memory limit
        ini_set('memory_limit', $this->option('memory') . 'M');
        
        $grades = Grade::orderBy('min_mark')->get();
        $processed = 0;
        $chunkSize = (int)$this->option('chunk');

        Students::chunk($chunkSize, function ($students) use ($grades, &$processed) {
            foreach ($students as $student) {
                $this->processStudent($student, $grades);
                $processed++;
                
                if ($processed % 100 === 0) {
                    $this->logMemoryUsage($processed);
                }
            }
            
            gc_collect_cycles();
        });

        $this->info("\nETR calculation completed. Processed {$processed} students.");
        $this->info("Peak memory usage: " . 
            round(memory_get_peak_usage(true) / 1024 / 1024, 2) . "MB");
    }

    protected function processStudent(Students $student, $grades): void
    {
        try {
            $latestMark = null;
            $latestGrade = null;

            // Determine latest valid mark and grade
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
                return;
            }

            $latestGrade = strtoupper(trim($latestGrade));
            $currentGrade = $grades->firstWhere('grade', $latestGrade);

            if (!$currentGrade) {
                Log::warning("Invalid grade '{$latestGrade}' for student {$student->id}");
                return;
            }

            $nextGrade = $grades->firstWhere('min_mark', '>', $currentGrade->min_mark);

            if ($nextGrade) {
                $student->etr_m = $nextGrade->min_mark;
                $student->etr_g = $nextGrade->grade;
            } else {
                $student->etr_m = $latestMark;
                $student->etr_g = $currentGrade->grade;
            }

            $student->save();
        } catch (\Exception $e) {
            Log::error("Error processing student {$student->id}: {$e->getMessage()}");
        }
    }

    protected function logMemoryUsage(int $processed): void
    {
        $memory = round(memory_get_usage(true) / 1024 / 1024, 2);
        $this->output->write("\rProcessed: {$processed} | Memory: {$memory}MB");
    }
}