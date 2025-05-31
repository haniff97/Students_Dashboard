<?php

namespace App\Filament\Resources\StudentsResource\Pages;

use App\Filament\Exports\StudentsExporter;
use App\Filament\Imports\StudentImporter;
use App\Filament\Resources\StudentsResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;
use Filament\Actions\Imports\Enums\ImportFormat;
use Filament\Resources\Pages\ListRecords;


class ListStudents extends ListRecords
{
    protected static string $resource = StudentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            ExportAction::make()
                ->button()
                ->color('primary')
                ->outlined()
                ->exporter(StudentsExporter::class)
                ->formats([
                    ExportFormat::Csv,
                ]),

            ImportAction::make()
                ->button()
                ->color('primary')
                ->outlined()
                ->importer(StudentImporter::class)
               
        ];
    }
}
