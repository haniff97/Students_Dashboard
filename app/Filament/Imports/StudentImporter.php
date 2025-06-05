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

        $lastYear = Students::where('name', $student->name)
            ->where('class', $student->class)
            ->where('form', $student->class)
            ->where('subject', $student->subject)
            ->where('year', $previousYear)
            ->first();

        if (!$lastYear) {
            return;
        }

        $latestMark = null;
        $latestGrade = null;

        if (!empty($lastYear->uasa_m) && strtoupper(trim($lastYear->uasa_g)) !== 'TH') {
            $latestMark = $lastYear->uasa_m;
            $latestGrade = strtoupper(trim($lastYear->uasa_g));
        } elseif (!empty($lastYear->ppt_m) && strtoupper(trim($lastYear->ppt_g)) !== 'TH') {
            $latestMark = $lastYear->ppt_m;
            $latestGrade = strtoupper(trim($lastYear->ppt_g));
        } elseif (!empty($lastYear->pa1_m) && strtoupper(trim($lastYear->pa1_g)) !== 'TH') {
            $latestMark = $lastYear->pa1_m;
            $latestGrade = strtoupper(trim($lastYear->pa1_g));
        }

        if ($latestMark === null || !$latestGrade) {
            return;
        }

        $student->tov_m = $latestMark;
        $student->tov_g = $latestGrade;
        $student->save();
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
