<?php

namespace App\Filament\Resources\ClassesResource\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class ClassPerformanceCharts extends ChartWidget
{
    protected static ?string $heading = 'Grade Distribution';
    protected static ?string $pollingInterval = '15s';
    protected static ?string $maxHeight = '300px';

    protected $listeners = ['tableFilterUpdated' => 'updateChart'];

    public function updateChart(): void
    {
        $this->emitSelf('updateChartData');
    }

    protected function getData(): array
    {
        // Get active filters from the page
        $activeFilters = $this->getActiveFilters();
        
        $query = Students::query()
            ->when($activeFilters['year'] ?? null, fn($q, $year) => $q->where('year', $year))
            ->when($activeFilters['class'] ?? null, fn($q, $class) => $q->where('class', $class))
            ->when($activeFilters['form'] ?? null, fn($q, $form) => $q->where('form', $form))
            ->when($activeFilters['subject'] ?? null, fn($q, $subject) => $q->where('subject', $subject));

        // For large datasets, use optimized counting
        $gradeCounts = $this->countGrades($query);

        return [
            'labels' => ['A+', 'A', 'A-', 'B+', 'B', 'C+', 'C', 'D', 'E', 'G', 'TH'],
            'datasets' => [[
                'label' => 'Grade Distribution',
                'data' => [
                    $gradeCounts['A+'] ?? 0,
                    $gradeCounts['A'] ?? 0,
                    $gradeCounts['A-'] ?? 0,
                    $gradeCounts['B+'] ?? 0,
                    $gradeCounts['B'] ?? 0,
                    $gradeCounts['C+'] ?? 0,
                    $gradeCounts['C'] ?? 0,
                    $gradeCounts['D'] ?? 0,
                    $gradeCounts['E'] ?? 0,
                    $gradeCounts['G'] ?? 0,
                    $gradeCounts['TH'] ?? 0,
                ],
                'backgroundColor' => [
                    '#10B981', '#34D399', '#6EE7B7', // Greens
                    '#60A5FA', '#3B82F6',           // Blues
                    '#F59E0B', '#FBBF24',           // Yellows
                    '#EF4444', '#DC2626', '#991B1B', // Reds
                    '#9CA3AF',                      // Gray
                ],
                'borderColor' => '#ffffff',
                'borderWidth' => 1
            ]]
        ];
    }

    protected function countGrades(Builder $query): array
    {
        // For small datasets (<5000 records)
        if ($query->count() < 5000) {
            return $query->selectRaw('
                COUNT(CASE WHEN UPPER(tov_g) = "A+" THEN 1 END) as "A+",
                COUNT(CASE WHEN UPPER(tov_g) = "A" THEN 1 END) as "A",
                COUNT(CASE WHEN UPPER(tov_g) = "A-" THEN 1 END) as "A-",
                COUNT(CASE WHEN UPPER(tov_g) = "B+" THEN 1 END) as "B+",
                COUNT(CASE WHEN UPPER(tov_g) = "B" THEN 1 END) as "B",
                COUNT(CASE WHEN UPPER(tov_g) = "C+" THEN 1 END) as "C+",
                COUNT(CASE WHEN UPPER(tov_g) = "C" THEN 1 END) as "C",
                COUNT(CASE WHEN UPPER(tov_g) = "D" THEN 1 END) as "D",
                COUNT(CASE WHEN UPPER(tov_g) = "E" THEN 1 END) as "E",
                COUNT(CASE WHEN UPPER(tov_g) = "G" THEN 1 END) as "G",
                COUNT(CASE WHEN UPPER(tov_g) = "TH" THEN 1 END) as "TH"
            ')->first()->toArray();
        }

        // For large datasets - process in chunks
        $grades = [
            'A+' => 0, 'A' => 0, 'A-' => 0,
            'B+' => 0, 'B' => 0,
            'C+' => 0, 'C' => 0,
            'D' => 0, 'E' => 0, 'G' => 0,
            'TH' => 0
        ];

        $query->select('tov_g')->chunk(2000, function ($students) use (&$grades) {
            foreach ($students as $student) {
                $grade = strtoupper($student->tov_g);
                if (isset($grades[$grade])) {
                    $grades[$grade]++;
                }
            }
        });

        return $grades;
    }

    protected function getActiveFilters(): array
    {
        return [
            'year' => request()->input('tableFilters.year.value'),
            'class' => request()->input('tableFilters.class.value'),
            'form' => request()->input('tableFilters.form.value'),
            'subject' => request()->input('tableFilters.subject.value'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}