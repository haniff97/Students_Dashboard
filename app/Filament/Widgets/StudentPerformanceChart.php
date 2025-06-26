<?php

namespace App\Filament\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;

class StudentPerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Student Performance Trends';
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        // Get average marks for each exam type
        $examData = Students::selectRaw('
            AVG(tov_m) as avg_tov,
            AVG(pa1_m) as avg_pa1,
            AVG(ppt_m) as avg_ppt,
            AVG(uasa_m) as avg_uasa,
            AVG(etr_m) as avg_etr
        ')->first();

        // Get pass rates for each exam type
        $passRates = Students::selectRaw('
            ROUND((COUNT(CASE WHEN UPPER(tov_g) IN ("A+", "A", "A-", "B+", "B", "C+", "C", "D") THEN 1 END) * 100.0) / 
                  NULLIF(COUNT(CASE WHEN UPPER(tov_g) IS NOT NULL AND UPPER(tov_g) NOT IN ("TH") THEN 1 END), 0), 1) as tov_pass_rate,
            ROUND((COUNT(CASE WHEN UPPER(pa1_g) IN ("A+", "A", "A-", "B+", "B", "C+", "C", "D") THEN 1 END) * 100.0) / 
                  NULLIF(COUNT(CASE WHEN UPPER(pa1_g) IS NOT NULL AND UPPER(pa1_g) NOT IN ("TH") THEN 1 END), 0), 1) as pa1_pass_rate,
            ROUND((COUNT(CASE WHEN UPPER(ppt_g) IN ("A+", "A", "A-", "B+", "B", "C+", "C", "D") THEN 1 END) * 100.0) / 
                  NULLIF(COUNT(CASE WHEN UPPER(ppt_g) IS NOT NULL AND UPPER(ppt_g) NOT IN ("TH") THEN 1 END), 0), 1) as ppt_pass_rate,
            ROUND((COUNT(CASE WHEN UPPER(uasa_g) IN ("A+", "A", "A-", "B+", "B", "C+", "C", "D") THEN 1 END) * 100.0) / 
                  NULLIF(COUNT(CASE WHEN UPPER(uasa_g) IS NOT NULL AND UPPER(uasa_g) NOT IN ("TH") THEN 1 END), 0), 1) as uasa_pass_rate,
            ROUND((COUNT(CASE WHEN UPPER(etr_g) IN ("A+", "A", "A-", "B+", "B", "C+", "C", "D") THEN 1 END) * 100.0) / 
                  NULLIF(COUNT(CASE WHEN UPPER(etr_g) IS NOT NULL AND UPPER(etr_g) NOT IN ("TH") THEN 1 END), 0), 1) as etr_pass_rate
        ')->first();

        return [
            'datasets' => [
                [
                    'label' => 'Average Marks',
                    'data' => [
                        round($examData->avg_tov ?? 0, 1),
                        round($examData->avg_pa1 ?? 0, 1),
                        round($examData->avg_ppt ?? 0, 1),
                        round($examData->avg_uasa ?? 0, 1),
                        round($examData->avg_etr ?? 0, 1),
                    ],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f666',
                    'fill' => false,
                    'tension' => 0.3,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Pass Rate (%)',
                    'data' => [
                        $passRates->tov_pass_rate ?? 0,
                        $passRates->pa1_pass_rate ?? 0,
                        $passRates->ppt_pass_rate ?? 0,
                        $passRates->uasa_pass_rate ?? 0,
                        $passRates->etr_pass_rate ?? 0,
                    ],
                    'borderColor' => '#10b981',
                    'backgroundColor' => '#10b98166',
                    'fill' => false,
                    'tension' => 0.3,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => ['TOV', 'PA1', 'PPT', 'UASA', 'ETR'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
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
        ];
    }

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
} 