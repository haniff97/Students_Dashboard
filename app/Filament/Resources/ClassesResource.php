<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassesResource\Pages;
use App\Models\Students;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClassesResource extends Resource
{
    protected static ?string $model = Students::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    public static function getNavigationLabel(): string
    {
        return 'Class Performances';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tingkatan')->label('Form'),
                TextColumn::make('class'),
                TextColumn::make('subject'),
                TextColumn::make('total_students')->label('Total Students'),
                TextColumn::make('attended_students')->label('Total Students Who Took Exam'),
                TextColumn::make('didnt_take_students')->label('Total Students Who Didn\'t Take Exam'),
                TextColumn::make('gp')
                    ->label('GP (%)')
                    ->formatStateUsing(fn ($record) =>
                        $record->attended_students > 0
                            ? number_format(($record->gp / ($record->attended_students * 9)) * 100, 2)
                            : '-'
                    ),
                TextColumn::make('gpmp')
                    ->label('GPMP')
                    ->formatStateUsing(fn ($record) =>
                        $record->total_students > 0
                            ? number_format($record->gp / $record->total_students, 2)
                            : '-'
                    ),
            ])
            ->query(function (): Builder {
                return Students::query()
                    ->selectRaw('
                        CONCAT(form, "-", class, "-", subject) as id,
                        class,
                        form as tingkatan,
                        subject,
                        COUNT(*) as total_students,
                        COUNT(CASE WHEN tov_g IS NOT NULL AND tov_g NOT IN ("TH") THEN 1 END) as attended_students,
                        COUNT(CASE WHEN tov_g IN ("TH") OR tov_g IS NULL THEN 1 END) as didnt_take_students,
                        SUM(CASE tov_g
                            WHEN "A+" THEN 0
                            WHEN "A" THEN 1
                            WHEN "A-" THEN 2
                            WHEN "B+" THEN 3
                            WHEN "B" THEN 4
                            WHEN "C+" THEN 5
                            WHEN "C" THEN 6
                            WHEN "D" THEN 7
                            WHEN "E" THEN 8
                            WHEN "F" THEN 9
                            ELSE NULL
                        END) as gp
                    ')
                    ->groupBy('form', 'class', 'subject');
            })
            ->filters([
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(
                        Students::query()->distinct()->pluck('year', 'year')->toArray()
                    ),
                SelectFilter::make('subject')
                    ->label('Subject')
                    ->options(
                        Students::query()->distinct()->pluck('subject', 'subject')->toArray()
                    ),
            ])
            ->actions([]) // read-only table
            ->bulkActions([]); // disable bulk actions
    }

    /**
     * Override the record key for the table.
     */
    public static function getTableRecordKey($record): string
    {
        return $record->id; // Use the synthetic 'id' from the query
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClasses::route('/'),
            'create' => Pages\CreateClasses::route('/create'),
            'edit' => Pages\EditClasses::route('/{record}/edit'),
        ];
    }
}