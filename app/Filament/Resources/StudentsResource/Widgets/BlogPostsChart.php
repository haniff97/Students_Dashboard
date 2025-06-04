<?php

namespace App\Filament\Resources\StudentsResource\Widgets;

use App\Models\Students;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Student Performance Chart';
    protected static ?string $maxHeight = '300px';

    public ?int $studentId = null;

    #[On('student-selected')]
    public function updateStudent($studentId)
    {
        $this->studentId = $studentId;
    }

    protected function getData(): array
    {
        $student = $this->studentId
            ? Students::find($this->studentId)
            : Students::orderByDesc('year')->first();

        if (!$student) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => $student->name . ' Marks',
                    'data' => [
                        $student->tov_m,
                        $student->pa1_m,
                        $student->ppt_m,
                        $student->uasa_m,
                        $student->etr_m,
                    ],
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f666',
                    'fill' => false,
                    'tension' => 0.3,
                ],
            ],
            'labels' => ['TOV','PA1', 'PPT', 'UASA', 'ETR'],
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

