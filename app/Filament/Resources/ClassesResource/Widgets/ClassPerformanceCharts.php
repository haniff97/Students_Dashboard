<?php

namespace App\Filament\Resources\ClassesResource\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ClassPerformanceCharts extends ChartWidget
{
    protected static ?string $heading = 'Grade Distribution';
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get the current table filters from the request
        $filters = $this->getTableFilters();
        
        $query = Students::query()
            ->selectRaw('
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "A+" THEN 1 ELSE 0 END), 0) as a_plus,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "A" THEN 1 ELSE 0 END), 0) as a,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "A-" THEN 1 ELSE 0 END), 0) as a_minus,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "B+" THEN 1 ELSE 0 END), 0) as b_plus,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "B" THEN 1 ELSE 0 END), 0) as b,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "C+" THEN 1 ELSE 0 END), 0) as c_plus,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "C" THEN 1 ELSE 0 END), 0) as c,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "D" THEN 1 ELSE 0 END), 0) as d,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "E" THEN 1 ELSE 0 END), 0) as e,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "G" THEN 1 ELSE 0 END), 0) as g,
                COALESCE(SUM(CASE WHEN UPPER(tov_g) = "TH" THEN 1 ELSE 0 END), 0) as th
            ')
            ->when(!empty($filters['year']), fn($q) => $q->where('year', $filters['year']))
            ->when(!empty($filters['class']), fn($q) => $q->where('class', $filters['class']))
            ->when(!empty($filters['form']), fn($q) => $q->where('form', $filters['form']))
            ->when(!empty($filters['subject']), fn($q) => $q->where('subject', $filters['subject']));

        $data = $query->first();

        return [
            'labels' => ['A+', 'A', 'A-', 'B+', 'B', 'C+', 'C', 'D', 'E', 'G', 'TH'],
            'datasets' => [
                [
                    'label' => 'Grade Distribution',
                    'data' => [
                        $data->a_plus,
                        $data->a,
                        $data->a_minus,
                        $data->b_plus,
                        $data->b,
                        $data->c_plus,
                        $data->c,
                        $data->d,
                        $data->e,
                        $data->g,
                        $data->th
                    ],
                    'backgroundColor' => [
                        '#10B981', '#34D399', '#6EE7B7', // A grades (green)
                        '#60A5FA', '#3B82F6',           // B grades (blue)
                        '#F59E0B', '#FBBF24',           // C grades (yellow)
                        '#EF4444',                      // D (red)
                        '#DC2626',                      // E (darker red)
                        '#991B1B',                      // G (darkest red)
                        '#9CA3AF',                      // TH (gray)
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 1
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getTableFilters(): array
    {
        return [
            'year' => request()->input('tableFilters.year.value'),
            'class' => request()->input('tableFilters.class.value'),
            'form' => request()->input('tableFilters.form.value'),
            'subject' => request()->input('tableFilters.subject.value'),
        ];
    }

    public function getDescription(): ?string
    {
        return 'Interactive chart that updates based on table filters';
    }
}