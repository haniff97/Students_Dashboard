<?php

namespace App\Filament\Resources\ClassesResource\Widgets;

use App\Models\Students;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Session;

class GpmpOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $filters = Session::get('table-filters.' . Students::class, []);
        
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
            ->when(!empty($filters['year']['value']), function ($query) use ($filters) {
                $query->where('year', $filters['year']['value']);
            })
            ->when(!empty($filters['class']['value']), function ($query) use ($filters) {
                $query->where('class', $filters['class']['value']);
            })
            ->when(!empty($filters['form']['value']), function ($query) use ($filters) {
                $query->where('form', $filters['form']['value']);
            })
            ->when(!empty($filters['subject']['value']), function ($query) use ($filters) {
                $query->where('subject', $filters['subject']['value']);
            })
            ->first();

        $gpmp = $query && $query->attended_students > 0 
            ? number_format($query->total_gp / $query->attended_students, 2)
            : 0;

        return [
            Stat::make('GPMP', $gpmp)
                ->description('Grade Point Mean Percentage')
                ->color($this->getGpmpColor((float)$gpmp))
                ->chart([7, 2, 10, 3, 15, 4, 17])
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