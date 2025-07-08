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
        // Get the current year and previous two years
        $currentYear = now()->year; // e.g., 2025
        $years = [$currentYear, $currentYear - 1, $currentYear - 2]; // e.g., [2025, 2024, 2023]

        $stats = [];

        foreach ($years as $year) {
            // Build the query for each year with applied filters (except year)
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
                ->where('year', $year)
                ->when(
                    isset($this->filters['subject']['value']) && $this->filters['subject']['value'],
                    fn($q) => $q->where('subject', $this->filters['subject']['value'])
                )
                ->when(
                    isset($this->filters['form']['value']) && $this->filters['form']['value'],
                    fn($q) => $q->where('form', $this->filters['form']['value'])
                )
                ->when(
                    isset($this->filters['class']['value']) && $this->filters['class']['value'],
                    fn($q) => $q->where('class', $this->filters['class']['value'])
                );

            // Cache the query result
            $cacheKey = 'gpmp_stats_' . $year . '_' . md5($query->toSql() . serialize($query->getBindings()));
            $result = cache()->remember($cacheKey, now()->addMinutes(5), fn() => $query->first());

            $gpmp = $result && $result->attended_students > 0 
                ? number_format($result->total_gp / $result->attended_students, 2)
                : 0;

            $stats[] = Stat::make("GPMP $year", $gpmp)
                ->description("Grade Point Mean Percentage ($year)")
                ->color($this->getGpmpColor((float)$gpmp))
                ->chart($this->getHistoricalGpmp($year))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 transition-colors',
                    'title' => 'Lower scores indicate better performance'
                ]);
        }

        return $stats;
    }

    protected function getHistoricalGpmp(int $year): array
    {
        $startYear = $year - 6; // Get 7 years of data (including the target year)
        $historicalGpmp = Students::query()
            ->selectRaw('year, AVG(CASE UPPER(tov_g)
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
            END) as avg_gp')
            ->when(
                isset($this->filters['subject']['value']) && $this->filters['subject']['value'],
                fn($q) => $q->where('subject', $this->filters['subject']['value'])
            )
            ->when(
                isset($this->filters['form']['value']) && $this->filters['form']['value'],
                fn($q) => $q->where('form', $this->filters['form']['value'])
            )
            ->when(
                isset($this->filters['class']['value']) && $this->filters['class']['value'],
                fn($q) => $q->where('class', $this->filters['class']['value'])
            )
            ->whereBetween('year', [$startYear, $year])
            ->groupBy('year')
            ->orderBy('year')
            ->pluck('avg_gp')
            ->map(fn($gp) => round((float)$gp, 2))
            ->take(7)
            ->toArray();

        // Pad with zeros if fewer than 7 data points
        return array_pad($historicalGpmp, -7, 0);
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