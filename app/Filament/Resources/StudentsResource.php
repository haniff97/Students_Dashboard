<?php

namespace App\Filament\Resources;

use App\Filament\Imports\StudentImporter;
use App\Filament\Resources\StudentsResource\Pages;
use App\Models\Students;
use Filament\Actions\ImportAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentsResource extends Resource
{
    protected static ?string $model = Students::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    // THESE SHOULD BE IN YOUR StudentImporter CLASS, NOT HERE
    // protected static bool $skipImportLogging = true;  // <-- WRONG PLACE
    // protected static bool $ignoreRecordOnFailure = true;  // <-- WRONG PLACE

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('class')->required(),
                Select::make('subject')
                    ->label('Subject')
                    ->options(function () {
                        return Students::query()
                            ->select('subject')
                            ->distinct()
                            ->pluck('subject', 'subject')
                            ->toArray();
                    })
                    ->searchable()
                    ->required(),
                TextInput::make('tov_m')->label('TOV (M)')->numeric(),
                TextInput::make('tov_g')->label('TOV (G)'),
                TextInput::make('pa1_m')->label('PA1 (M)')->numeric(),
                TextInput::make('pa1_g')->label('PA1 (G)'),
                TextInput::make('ppt_m')->label('PPT (M)')->numeric(),
                TextInput::make('ppt_g')->label('PPT (G)'),
                TextInput::make('uasa_m')->label('UASA (M)')->numeric(),
                TextInput::make('uasa_g')->label('UASA (G)'),
                TextInput::make('etr_m')->label('ETR (M)')->numeric(),
                TextInput::make('etr_g')->label('ETR (G)'),
                TextInput::make('year')->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('class'),
                TextColumn::make('subject'),
                TextColumn::make('tov_m')->label('TOV (M)'),
                TextColumn::make('tov_g')->label('TOV (G)'),
                TextColumn::make('pa1_m')->label('PA1 (M)'),
                TextColumn::make('pa1_g')->label('PA1 (G)'),
                TextColumn::make('ppt_m')->label('PPT (M)'),
                TextColumn::make('ppt_g')->label('PPT (G)'),
                TextColumn::make('uasa_m')->label('UASA (M)'),
                TextColumn::make('uasa_g')->label('UASA (G)'),
                TextColumn::make('etr_m')->label('ETR (M)'),
                TextColumn::make('etr_g')->label('ETR (G)'),
                TextColumn::make('year')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudents::route('/create'),
            'edit' => Pages\EditStudents::route('/{record}/edit'),
            // No import route needed in Filament v3
        ];
    }

    public static function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(StudentImporter::class)
                ->label('Import Students')
                ->color('primary')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }
}
