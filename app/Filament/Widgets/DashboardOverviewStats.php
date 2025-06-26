<?php

namespace App\Filament\Widgets;

use App\Models\Students;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverviewStats extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Get total students
        $totalStudents = Students::count();
        
        // Get students with grades (attended)
        $attendedStudents = Students::whereNotNull('tov_g')
            ->where('tov_g', '!=', 'TH')
            ->count();
        
        // Get pass rate (A+ to D grades)
        $passedStudents = Students::whereIn('tov_g', ['A+', 'A', 'A-', 'B+', 'B', 'C+', 'C', 'D'])
            ->count();
        
        $passRate = $attendedStudents > 0 ? round(($passedStudents / $attendedStudents) * 100, 1) : 0;
        
        // Get average GPMP
        $gpmpData = Students::selectRaw('
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
        ')->first();
        
        $gpmp = $gpmpData && $gpmpData->attended_students > 0 
            ? number_format($gpmpData->total_gp / $gpmpData->attended_students, 2)
            : '0.00';
        
        // Get unique classes
        $uniqueClasses = Students::distinct()->count(['form', 'class', 'subject']);
        
        // Get unique subjects
        $uniqueSubjects = Students::distinct()->count('subject');

        return [
            Stat::make('Total Students', $totalStudents)
                ->description('All registered students')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
                
            Stat::make('Attended Students', $attendedStudents)
                ->description('Students who took exams')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
                
            Stat::make('Pass Rate', $passRate . '%')
                ->description('Students with A+ to D grades')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($this->getPassRateColor($passRate)),
                
            Stat::make('Average GPMP', $gpmp)
                ->description('Grade Point Mean Percentage')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($this->getGpmpColor((float)$gpmp)),
                
            Stat::make('Active Classes', $uniqueClasses)
                ->description('Form-Class-Subject combinations')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),
                
            Stat::make('Subjects Offered', $uniqueSubjects)
                ->description('Different subjects available')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),
        ];
    }

    protected function getPassRateColor(float $passRate): string
    {
        return match(true) {
            $passRate >= 90 => 'success',
            $passRate >= 70 => 'primary',
            $passRate >= 50 => 'warning',
            default => 'danger',
        };
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