<?php

namespace App\Filament\Imports;

use App\Models\Students;
use App\Models\Grade;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;

class StudentImporter extends Importer
{
    protected static ?string $model = Students::class;
    protected static bool $skipImportLogging = true;

    // Set chunk size for processing
    protected static int $chunkSize = 500;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('John Doe'),
                
            ImportColumn::make('class')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('5A'),
                
            ImportColumn::make('form')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'min:1', 'max:5'])
                ->example(5),
                
            ImportColumn::make('subject')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Mathematics'),
                
            ImportColumn::make('pa1_m')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100'])
                ->example(85),
                
            ImportColumn::make('pa1_g')
                ->rules(['nullable', 'max:2'])
                ->example('A'),
                
            ImportColumn::make('ppt_m')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100'])
                ->example(78),
                
            ImportColumn::make('ppt_g')
                ->rules(['nullable', 'max:2'])
                ->example('B+'),
                
            ImportColumn::make('uasa_m')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100'])
                ->example(92),
                
            ImportColumn::make('uasa_g')
                ->rules(['nullable', 'max:2'])
                ->example('A+'),
                
            ImportColumn::make('year')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:2000', 'max:2100'])
                ->example(2023),
        ];
    }

    public function resolveRecord(): ?Students
    {
        // Temporarily increase memory limit for this operation
        $originalMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        try {
            $student = Students::updateOrCreate(
                $this->getMatchAttributes(),
                $this->getUpdateAttributes()
            );

            $this->processStudentCalculations($student);

            return $student;
        } catch (\Exception $e) {
            Log::error("Failed to import student: {$e->getMessage()}");
            return null;
        } finally {
            ini_set('memory_limit', $originalMemoryLimit);
            gc_collect_cycles();
        }
    }

    protected function getMatchAttributes(): array
    {
        return [
            'name' => $this->data['name'],
            'class' => $this->data['class'],
            'form' => $this->data['form'],
            'subject' => $this->data['subject'],
            'year' => $this->data['year'] ?? null,
        ];
    }

    protected function getUpdateAttributes(): array
    {
        return [
            'pa1_m' => $this->data['pa1_m'] ?? null,
            'pa1_g' => $this->data['pa1_g'] ?? null,
            'ppt_m' => $this->data['ppt_m'] ?? null,
            'ppt_g' => $this->data['ppt_g'] ?? null,
            'uasa_m' => $this->data['uasa_m'] ?? null,
            'uasa_g' => $this->data['uasa_g'] ?? null,
        ];
    }

    protected function processStudentCalculations(Students $student): void
    {
        if (memory_get_usage(true) > 100 * 1024 * 1024) { // 100MB
            Log::warning('High memory usage during student calculations');
            gc_collect_cycles();
        }

        $this->calculateETR($student);
        $this->calculateTOV($student);
    }

    protected function calculateETR(Students $student): void
    {
        try {
            $grades = Grade::orderBy('min_mark')->get();
            $latestMark = null;
            $latestGrade = null;

            // Determine the latest valid assessment
            foreach (['uasa', 'ppt', 'pa1'] as $assessment) {
                $markField = "{$assessment}_m";
                $gradeField = "{$assessment}_g";

                if ($student->$markField && $student->$gradeField !== 'TH') {
                    $latestMark = $student->$markField;
                    $latestGrade = strtoupper(trim($student->$gradeField));
                    break;
                }
            }

            if (!$latestMark || !$latestGrade) {
                return;
            }

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
            Log::error("ETR calculation failed for student {$student->id}: {$e->getMessage()}");
        }
    }

    protected function calculateTOV(Students $student): void
    {
        try {
            if (!$student->year || !$student->form) {
                Log::warning("Missing year or form for student {$student->id}");
                return;
            }

            $previousYear = $student->year - 1;
            $previousForm = $student->form > 1 ? $student->form - 1 : null;

            if (!$previousForm) {
                return;
            }

            $previousStudent = Students::where('name', $student->name)
                ->where('class', $student->class)
                ->where('form', $previousForm)
                ->where('subject', $student->subject)
                ->where('year', $previousYear)
                ->first();

            if (!$previousStudent) {
                return;
            }

            // Get the latest valid assessment from previous year
            foreach (['uasa', 'ppt', 'pa1'] as $assessment) {
                $markField = "{$assessment}_m";
                $gradeField = "{$assessment}_g";

                if ($previousStudent->$markField) {
                    $student->tov_m = $previousStudent->$markField;
                    $student->tov_g = $previousStudent->$gradeField;
                    $student->save();
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error("TOV calculation failed for student {$student->id}: {$e->getMessage()}");
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Student import completed. ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getChunkSize(): int
    {
        return static::$chunkSize;
    }
}