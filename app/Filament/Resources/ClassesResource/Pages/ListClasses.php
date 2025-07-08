<?php

namespace App\Filament\Resources\ClassesResource\Pages;

use App\Filament\Resources\ClassesResource;
use Filament\Actions\Action;
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

    protected function getActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Data')
                ->action(function () {
                    $this->dispatch('filtersUpdated', $this->tableFilters);
                }),
        ];
    }

    public function updatedTableFilters(): void
    {
        $this->dispatch('filtersUpdated', $this->tableFilters);
    }
}