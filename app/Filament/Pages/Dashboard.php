<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardOverviewStats;
use App\Filament\Widgets\StudentPerformanceChart;
use App\Filament\Widgets\ClassPerformanceChart;
use App\Filament\Widgets\SubjectPerformanceChart;
use App\Filament\Widgets\FormClassPerformanceChart;
use App\Filament\Widgets\RecentStudentsTable;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static string $view = 'filament.pages.dashboard';
    
    public function getWidgets(): array
    {
        return [
            DashboardOverviewStats::class,
            StudentPerformanceChart::class,
            ClassPerformanceChart::class,
            SubjectPerformanceChart::class,
            FormClassPerformanceChart::class,
            RecentStudentsTable::class,
        ];
    }
}