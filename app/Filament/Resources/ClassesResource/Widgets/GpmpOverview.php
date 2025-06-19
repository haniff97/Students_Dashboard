<?php

namespace App\Filament\Resources\ClassesResource\Widgets;

use App\Models\Students;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GpmpOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $filters = $this->getFilters();
        
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
            COUNT(*) as total_students
        ')
        ->when($filters, function ($query) use ($filters) {
            if (!empty($filters['year'])) {
                $query->where('year', $filters['year']);
            }
            if (!empty($filters['class'])) {
                $query->where('class', $filters['class']);
            }
            if (!empty($filters['form'])) { // Changed from 'tingkatan' to 'form'
                $query->where('form', $filters['form']);
            }
            if (!empty($filters['subject'])) {
                $query->where('subject', $filters['subject']);
            }
        })
        ->first();

        $gpmp = $query && $query->total_students > 0 
            ? number_format($query->total_gp / $query->total_students, 2)
            : 0;

        return [
            Stat::make('GPMP', $gpmp)
                ->description('Grade Point Mean Percentage')
                ->color($this->getGpmpColor((float)$gpmp))
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->extraAttributes([
                    'class' => 'text-2xl font-bold',
                ]),
        ];
    }

    protected function getGpmpColor(float $gpmp): string
    {
        return match(true) {
            $gpmp >= 3.5 => 'success',
            $gpmp >= 2.5 => 'primary',
            $gpmp >= 1.5 => 'warning',
            default => 'danger',
        };
    }

    protected function getFilters(): ?array
    {
        return request()->query('tableFilters');
    }
}