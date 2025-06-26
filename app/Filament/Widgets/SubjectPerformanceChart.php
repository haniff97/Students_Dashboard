<?php

namespace App\Filament\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;

class SubjectPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Subject Performance Comparison';
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get performance data by subject
        $subjectData = Students::selectRaw('
            subject,
            AVG(tov_m) as avg_marks,
            COUNT(CASE WHEN UPPER(tov_g) IN ("A+", "A", "A-", "B+", "B", "C+", "C", "D") THEN 1 END) as passed_students,
            COUNT(CASE WHEN UPPER(tov_g) IS NOT NULL AND UPPER(tov_g) NOT IN ("TH") THEN 1 END) as total_students
        ')
        ->whereNotNull('tov_m')
        ->groupBy('subject')
        ->orderBy('avg_marks', 'desc')
        ->get();

        $labels = $subjectData->pluck('subject')->toArray();
        $avgMarks = $subjectData->pluck('avg_marks')->toArray();
        $passRates = $subjectData->map(function ($item) {
            return $item->total_students > 0 
                ? round(($item->passed_students / $item->total_students) * 100, 1)
                : 0;
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Average Marks',
                    'data' => $avgMarks,
                    'backgroundColor' => '#3b82f666',
                    'borderColor' => '#3b82f6',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Pass Rate (%)',
                    'data' => $passRates,
                    'backgroundColor' => '#10b98166',
                    'borderColor' => '#10b981',
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