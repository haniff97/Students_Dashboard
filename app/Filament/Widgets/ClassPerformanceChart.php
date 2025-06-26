<?php

namespace App\Filament\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;

class ClassPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Overall Grade Distribution';
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Count grades across all students
        $gradeCounts = Students::selectRaw('
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
        ')->first();

        return [
            'labels' => ['A+', 'A', 'A-', 'B+', 'B', 'C+', 'C', 'D', 'E', 'G', 'TH'],
            'datasets' => [[
                'label' => 'Grade Distribution',
                'data' => [
                    $gradeCounts->{'A+'} ?? 0,
                    $gradeCounts->{'A'} ?? 0,
                    $gradeCounts->{'A-'} ?? 0,
                    $gradeCounts->{'B+'} ?? 0,
                    $gradeCounts->{'B'} ?? 0,
                    $gradeCounts->{'C+'} ?? 0,
                    $gradeCounts->{'C'} ?? 0,
                    $gradeCounts->{'D'} ?? 0,
                    $gradeCounts->{'E'} ?? 0,
                    $gradeCounts->{'G'} ?? 0,
                    $gradeCounts->{'TH'} ?? 0,
                ],
                'backgroundColor' => [
                    '#10B981', '#34D399', '#6EE7B7', // Greens (A+, A, A-)
                    '#60A5FA', '#3B82F6',           // Blues (B+, B)
                    '#F59E0B', '#FBBF24',           // Yellows (C+, C)
                    '#EF4444', '#DC2626', '#991B1B', // Reds (D, E, G)
                    '#9CA3AF',                      // Gray (TH)
                ],
                'borderColor' => '#ffffff',
                'borderWidth' => 1
            ]]
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ": " + context.parsed + " (" + percentage + "%)";
                        }'
                    ]
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