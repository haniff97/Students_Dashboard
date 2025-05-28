<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentsResource\Pages;
use App\Filament\Resources\StudentsResource\RelationManagers;
use App\Models\Students;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
class StudentsResource extends Resource
{
    protected static ?string $model = Students::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

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
                TextColumn::MAKE('name'),
                TextColumn::MAKE('class'),
                TextColumn::MAKE('subject'),
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
                TextColumn::MAKE('year')
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
        ];
    }
}
