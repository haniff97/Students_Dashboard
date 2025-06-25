<?php

namespace App\Filament\Resources\StudentsResource\Pages;

use App\Filament\Resources\StudentsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\StudentImporter::class)
                ->chunkSize(500), // Process imports in chunks
            Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\StudentsExporter::class)
                ->chunkSize(500), // Process exports in chunks
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\StudentsResource\Widgets\BlogPostsChart::class,
        ];
    }
}