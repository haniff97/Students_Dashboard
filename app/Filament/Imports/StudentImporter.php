<?php

namespace App\Filament\Imports;

use App\Models\Students;
use App\Models\Grade;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class StudentImporter extends Importer
{
    protected static ?string $model = Students::class;

    protected static bool $skipImportLogging = true;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('class')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('form')->requiredMapping()->numeric()->rules(['required', 'integer', 'min:1', 'max:5']),
            ImportColumn::make('subject')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('pa1_m')->requiredMapping()->numeric()->rules(['nullable', 'numeric', 'min:0', 'max:100']),
            ImportColumn::make('pa1_g')->requiredMapping()->rules(['nullable', 'max:11']),
            ImportColumn::make('ppt_m')->requiredMapping()->numeric()->rules(['nullable', 'numeric', 'min:0', 'max:100']),
            ImportColumn::make('ppt_g')->requiredMapping()->rules(['nullable', 'max:11']),
            ImportColumn::make('uasa_m')->requiredMapping()->numeric()->rules(['nullable', 'numeric', 'min:0', 'max:100']),
            ImportColumn::make('uasa_g')->requiredMapping()->rules(['nullable', 'max:11']),
            ImportColumn::make('year')->requiredMapping()->numeric()->rules(['nullable', 'integer']),
        ];
    }

    public function resolveRecord(): ?Students
    {
        try {
            $student = Students::updateOrCreate(
                [
                    'name' => $this->data['name'],
                    'class' => $this->data['class'],
                    'form' => $this->data['form'],
                    'subject' => $this->data['subject'],
                    'year' => $this->data['year'],
                ],
                [
                    'pa1_m' => $this->data['pa1_m'] ?? null,
                    'pa1_g' => $this->data['pa1_g'] ?? null,
                    'ppt_m' => $this->data['ppt_m'] ?? null,
                    'ppt_g' => $this->data['ppt_g'] ?? null,
                    'uasa_m' => $this->data['uasa_m'] ?? null,
                    'uasa_g' => $this->data['uasa_g'] ?? null,
                ]
            );

            $this->calculateETR($student);
            $this->calculateTOV($student);

            return $student;
        } catch (\Exception $e) {
            \Log::error('Failed importing student: ' . $e->getMessage());
            throw $e;
        }
    }

    private function calculateETR(Students $student): void
    {
        $grades = Grade::orderBy('min_mark')->get();

        $latestMark = null;
        $latestGrade = null;

        if (!empty($student->uasa_m) && strtoupper(trim($student->uasa_g)) !== 'TH') {
            $latestMark = $student->uasa_m;
            $latestGrade = strtoupper(trim($student->uasa_g));
        } elseif (!empty($student->ppt_m) && strtoupper(trim($student->ppt_g)) !== 'TH') {
            $latestMark = $student->ppt_m;
            $latestGrade = strtoupper(trim($student->ppt_g));
        } elseif (!empty($student->pa1_m) && strtoupper(trim($student->pa1_g)) !== 'TH') {
            $latestMark = $student->pa1_m;
            $latestGrade = strtoupper(trim($student->pa1_g));
        }

        if ($latestMark === null || !$latestGrade) {
            return;
        }

        $current = $grades->firstWhere('grade', $latestGrade);
        if (!$current) {
            return;
        }

        $next = $grades->filter(fn($g) => $g->min_mark > $current->min_mark)->first();

        if (!$next) {
            $student->etr_m = $latestMark;
            $student->etr_g = $latestGrade;
        } else {
            $student->etr_m = $next->min_mark;
            $student->etr_g = $next->grade;
        }

        $student->save();
    }

    private function calculateTOV(Students $student): void
    {
        $previousYear = $student->year - 1;
        $previousForm = is_numeric($student->form) && $student->form > 1 ? $student->form - 1 : null;

        if ($previousForm === null) {
            \Log::info("Invalid form for student: {$student->name}, form: {$student->form}");
            return;
        }

        $lastYear = Students::whereRaw('LOWER(name) = ?', [strtolower(trim($student->name))])
            ->whereRaw('LOWER(class) = ?', [strtolower(trim($student->class))])
            ->where('form', $previousForm)
            ->whereRaw('LOWER(subject) = ?', [strtolower(trim($student->subject))])
            ->where('year', $previousYear)
            ->first();

        if (!$lastYear) {
            \Log::info("No previous year record for student: {$student->name}, class: {$student->class}, form: {$previousForm}, subject: {$student->subject}, year: {$previousYear}");
            return;
        }

        // Prioritize uasa_m and uasa_g, even if uasa_g is TH
        $latestMark = $lastYear->uasa_m; // Use uasa_m if it exists
        $latestGrade = strtoupper(trim($lastYear->uasa_g)); // Use uasa_g regardless of value

        // Fall back to ppt_m/ppt_g or pa1_m/pa1_g only if uasa_m is null
        if ($latestMark === null) {
            if (!empty($lastYear->ppt_m)) {
                $latestMark = $lastYear->ppt_m;
                $latestGrade = strtoupper(trim($lastYear->ppt_g));
            } elseif (!empty($lastYear->pa1_m)) {
                $latestMark = $lastYear->pa1_m;
                $latestGrade = strtoupper(trim($lastYear->pa1_g));
            }
        }

        if ($latestMark === null || !$latestGrade) {
            \Log::info("No valid mark/grade for student: {$student->name}, uasa_m={$lastYear->uasa_m}, uasa_g={$lastYear->uasa_g}, ppt_m={$lastYear->ppt_m}, ppt_g={$lastYear->ppt_g}, pa1_m={$lastYear->pa1_m}, pa1_g={$lastYear->pa1_g}");
            return;
        }

        $student->tov_m = $latestMark;
        $student->tov_g = $latestGrade;
        if (!$student->save()) {
            \Log::error("Failed to save TOV for student: {$student->name}, tov_m: {$latestMark}, tov_g: {$latestGrade}");
        } else {
            \Log::info("Saved TOV for student: {$student->name}, tov_m: {$latestMark}, tov_g: {$latestGrade}");
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}