<?php

namespace App\Filament\Imports;

use App\Models\Students;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class StudentImporter extends Importer
{
    protected static ?string $model = Students::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('NAMA')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('subject')
                ->label('SUBJECT')
                ->requiredMapping(),
                
            ImportColumn::make('class')
                ->label('CLASS')
                ->requiredMapping(),

            ImportColumn::make('pa1_m')
                ->label('PA1 (M)')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

            ImportColumn::make('pa1_g')
                ->label('PA1 (G)')
                ->rules(['nullable', 'string', 'max:2']),

            ImportColumn::make('ppt_m')
                ->label('PPT (M)')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

            ImportColumn::make('ppt_g')
                ->label('PPT (G)')
                ->rules(['nullable', 'string', 'max:2']),

            ImportColumn::make('uasa_m')
                ->label('UASA (M)')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

            ImportColumn::make('uasa_g')
                ->label('UASA (G)')
                ->rules(['nullable', 'string', 'max:2']),


        ];
    }

    public function resolveRecord(): ?Students
    {
        // return Student::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Students();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
