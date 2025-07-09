<?php

namespace App\Filament\Resources\ClassesResource\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class PassPercentageChart extends ChartWidget
{
    protected static ?string $heading = 'Pass Percentage';
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
            ->selectRaw('
                COUNT(CASE WHEN UPPER(tov_g) IN ("A+", "A", "A-", "B+", "B", "C+", "C", "D") THEN 1 END) as passed_students,
                COUNT(CASE WHEN UPPER(tov_g) IS NOT NULL AND UPPER(tov_g) NOT IN ("TH") THEN 1 END) as attended_students
            ')
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

        // Cache the query result
        $cacheKey = 'pass_percentage_' . md5($query->toSql() . serialize($query->getBindings()));
        $result = cache()->remember($cacheKey, now()->addMinutes(5), fn() => $query->first());

        $passPercentage = $result && $result->attended_students > 0
            ? round(($result->passed_students / $result->attended_students) * 100, 1)
            : 0;
        $failPercentage = 100 - $passPercentage;

        return [
            'labels' => ['Pass', 'Fail'],
            'datasets' => [
                [
                    'label' => 'Pass Percentage',
                    'data' => [$passPercentage, $failPercentage],
                    'backgroundColor' => [
                        '#10B981', // Green for Pass
                        '#EF4444', // Red for Fail
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}