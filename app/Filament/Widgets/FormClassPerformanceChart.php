<?php

namespace App\Filament\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;

class FormClassPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Form & Class Performance';
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get performance data by form and class
        $formClassData = Students::selectRaw('
            CONCAT(form, "-", class) as form_class,
            AVG(tov_m) as avg_marks,
            COUNT(CASE WHEN UPPER(tov_g) IN ("A+", "A", "A-", "B+", "B", "C+", "C", "D") THEN 1 END) as passed_students,
            COUNT(CASE WHEN UPPER(tov_g) IS NOT NULL AND UPPER(tov_g) NOT IN ("TH") THEN 1 END) as total_students
        ')
        ->whereNotNull('tov_m')
        ->groupBy('form', 'class')
        ->orderBy('form')
        ->orderBy('class')
        ->limit(15) // Limit to top 15 for better visualization
        ->get();

        $labels = $formClassData->pluck('form_class')->toArray();
        $avgMarks = $formClassData->pluck('avg_marks')->toArray();
        $passRates = $formClassData->map(function ($item) {
            return $item->total_students > 0 
                ? round(($item->passed_students / $item->total_students) * 100, 1)
                : 0;
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Average Marks',
                    'data' => $avgMarks,
                    'backgroundColor' => '#8b5cf666',
                    'borderColor' => '#8b5cf6',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Pass Rate (%)',
                    'data' => $passRates,
                    'backgroundColor' => '#f59e0b66',
                    'borderColor' => '#f59e0b',
                    'borderWidth' => 2,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 0,
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Average Marks',
                    ],
                    'min' => 0,
                    'max' => 100,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Pass Rate (%)',
                    ],
                    'min' => 0,
                    'max' => 100,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    public function getColumnSpan(): int | string | array
    {
        return 2;
    }
} 