<?php

namespace App\Filament\Resources\StudentsResource\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Student Performance Chart';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Replace this with dynamic logic if needed
        $student = Students::where('name', 'ADAM HAFIZ BIN MOHAMAD SOFI')
            ->orderByDesc('year')
            ->first();

        if (!$student) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Marks',
                    'data' => [
                        $student->pa1_m,
                        $student->ppt_m,
                        $student->uasa_m,
                        $student->tov_m,
                        $student->etr_m,
                    ],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f666',
                    'fill' => false,
                    'tension' => 0.3,
                ],
            ],
            'labels' => ['PA1', 'PPT', 'UASA', 'TOV', 'ETR'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    public function getColumnSpan(): int | string | array
    {
        return 'full'; 
    }

}
