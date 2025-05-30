<?php

namespace App\Filament\Imports;

use App\Models\Students;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class StudentImporter extends Importer
{
    protected static ?string $model = Students::class;
    
    protected static bool $skipImportLogging = true;
    
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nama')
                ->label('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
                
            ImportColumn::make('class')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
                
            ImportColumn::make('subject')
                ->rules(['max:255']),
                
            ImportColumn::make('pa1 (m)')
                ->label('pat_m') // Mapping to your database column
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),
                
            ImportColumn::make('pa1 (g)')
                ->label('pat_g')
                ->rules(['max:11']),
                
            ImportColumn::make('ppt (m)')
                ->label('ppt_m')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),
                
            ImportColumn::make('ppt (g)')
                ->label('ppt_g')
                ->rules(['max:11']),
                
            ImportColumn::make('uasa (m)')
                ->label('uasa_m')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:100']),
                
            ImportColumn::make('uasa (g)')
                ->label('uasa_g')
                ->rules(['max:11']),
                
            ImportColumn::make('year')
                ->numeric()
                ->rules(['nullable', 'integer'])
        ];
    }

    public function resolveRecord(): ?Students
    {
        // Handle empty values
        $pat_m = empty($this->data['pa1 (m)']) ? null : $this->data['pa1 (m)'];
        $ppt_m = empty($this->data['ppt (m)']) ? null : $this->data['ppt (m)'];
        $uasa_m = empty($this->data['uasa (m)']) ? null : $this->data['uasa (m)'];
        
        return Students::updateOrCreate(
            [
                'name' => $this->data['nama'],
                'class' => $this->data['class'],
                'subject' => $this->data['subject'],
                'year' => $this->data['year']
            ],
            [
                'pat_m' => $pat_m,
                'pat_g' => $this->data['pa1 (g)'],
                'ppt_m' => $ppt_m,
                'ppt_g' => $this->data['ppt (g)'],
                'uasa_m' => $uasa_m,
                'uasa_g' => $this->data['uasa (g)']
            ]
        );
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
