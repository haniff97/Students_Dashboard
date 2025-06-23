<?php

namespace App\Filament\Resources\ClassesResource\Pages;

use App\Filament\Resources\ClassesResource;
use Filament\Resources\Pages\ListRecords;

class ListClasses extends ListRecords
{
    protected static string $resource = ClassesResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ClassesResource\Widgets\GpmpOverview::class,
            ClassesResource\Widgets\ClassPerformanceCharts::class,
        ];
    }
}