<?php

namespace App\Filament\Resources\ClassesResource\Widgets;

use App\Models\Students;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GpmpOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null; // Disable polling for immediate updates

    public $filters = [];

    protected $listeners = ['filtersUpdated' => 'updateFilters'];

    public function updateFilters($filters): void
    {
        $this->filters = $filters;
    }

    protected function getStats(): array
    {
        // Build the query with applied filters
        $query = Students::query()
            ->selectRaw('
                SUM(CASE UPPER(tov_g)
                    WHEN "A+" THEN 0
                    WHEN "A" THEN 1
                    WHEN "A-" THEN 2
                    WHEN "B+" THEN 3
                    WHEN "B" THEN 4
                    WHEN "C+" THEN 5
                    WHEN "C" THEN 6
                    WHEN "D" THEN 7
                    WHEN "E" THEN 8
                    WHEN "F" THEN 9
                    ELSE NULL
                END) as total_gp,
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
        $cacheKey = 'gpmp_stats_' . md5($query->toSql() . serialize($query->getBindings()));
        $result = cache()->remember($cacheKey, now()->addMinutes(5), fn() => $query->first());

        $gpmp = $result && $result->attended_students > 0 
            ? number_format($result->total_gp / $result->attended_students, 2)
            : 0;

        return [
            Stat::make('GPMP', $gpmp)
                ->description('Grade Point Mean Percentage')
                ->color($this->getGpmpColor((float)$gpmp))
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Sample chart data, replace if needed
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 transition-colors',
                    'title' => 'Lower scores indicate better performance'
                ]),
        ];
    }

    protected function getGpmpColor(float $gpmp): string
    {
        return match(true) {
            $gpmp <= 1.0 => 'success',
            $gpmp <= 2.0 => 'primary',
            $gpmp <= 3.0 => 'warning',
            default => 'danger',
        };
    }
}