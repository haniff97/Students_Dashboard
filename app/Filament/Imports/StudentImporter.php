<?php

namespace App\Filament\Imports;

use App\Models\Students;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Artisan;

class StudentImporter extends Importer
{
    protected static ?string $model = Students::class;
    
    protected static bool $skipImportLogging = true;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('class')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('subject')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('pa1_m')
                ->requiredMapping()
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

            ImportColumn::make('pa1_g')
                ->requiredMapping()
                ->rules(['nullable', 'max:11']),

            ImportColumn::make('ppt_m')
                ->requiredMapping()
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

            ImportColumn::make('ppt_g')
                ->requiredMapping()
                ->rules(['nullable', 'max:11']),

            ImportColumn::make('uasa_m')
                ->requiredMapping()
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),

            ImportColumn::make('uasa_g')
                ->requiredMapping()
                ->rules(['nullable', 'max:11']),

            ImportColumn::make('year')
                ->requiredMapping()
                ->numeric()
                ->rules(['nullable', 'integer']),
        ];
    }

    public function resolveRecord(): ?Students
    {
        \Log::info('Importing student data:', $this->data);

        try {
            return Students::updateOrCreate(
                [
                    'name' => $this->data['name'],
                    'class' => $this->data['class'],
                    'subject' => $this->data['subject'],
                    'year' => $this->data['year'],
                ],
                [
                    'pa1_m' => $this->data['pa1_m'] ?? null,
                    'pa1_g' => $this->data['pa1_g'] ?? null,
                    'ppt_m' => $this->data['ppt_m'] ?? null,
                    'ppt_g' => $this->data['ppt_g'] ?? null,
                    'uasa_m' => $this->data['uasa_m'] ?? null,
                    'uasa_g' => $this->data['uasa_g'] ?? null,
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Failed importing student: ' . $e->getMessage());
            throw $e; // Let the import job fail visibly
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function afterImport(): void
    {
        \Log::info('Running ETR calculation after import');
        Artisan::call('app:calculate-e-t-r');
    }

}

