<?php

namespace App\Filament\Resources\ClassesResource\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class ClassPerformanceCharts extends ChartWidget
{
    protected static ?string $heading = 'Grade Distribution';
    protected static ?string $pollingInterval = null; // Disable polling for immediate updates
    protected static ?string $maxHeight = '300px';

    public $filters = [];

    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    public function updateFilters($filters): void
    {
        $this->filters = $filters;
        $this->dispatch('updateChartData');
    }

    protected function getData(): array
    {
        // Build the query with applied filters
        $query = Students::query()
            ->when(
                isset($this->filters['year']['value']) && $this->filters['year']['value'],
                fn($q) => $q->where('year', $this->filters['year']['value'])
            )
            ->when(
                isset($this->filters['class']['value']) && $this->filters['class']['value'],
                fn($q) => $q->where('class', $this->filters['class']['value'])
            )
            ->when(
                isset($this->filters['form']['value']) && $this->filters['form']['value'],
                fn($q) => $q->where('form', $this->filters['form']['value'])
            )
            ->when(
                isset($this->filters['subject']['value']) && $this->filters['subject']['value'],
                fn($q) => $q->where('subject', $this->filters['subject']['value'])
            );

        // Count grades
        $gradeCounts = $this->countGrades($query);

        return [
            'labels' => ['A+', 'A', 'A-', 'B+', 'B', 'C+', 'C', 'D', 'E', 'G', 'TH'],
            'datasets' => [
                [
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
                        '#10B981', '#34D399', '#6EE7B7', // Greens for A+, A, A-
                        '#60A5FA', '#3B82F6',           // Blues for B+, B
                        '#F59E0B', '#FBBF24',           // Yellows for C+, C
                        '#EF4444', '#DC2626', '#991B1B', // Reds for D, E, G
                        '#9CA3AF',                      // Gray for TH
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    protected function countGrades(Builder $query): array
    {
        // Cache the query results to improve performance
        $cacheKey = 'grade_counts_' . md5($query->toSql() . serialize($query->getBindings()));
        return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($query) {
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
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}