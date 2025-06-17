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
                TextColumn::make('year')->label('Year'),
                TextColumn::make('total_students')->label('Total Students'),
                TextColumn::make('attended_students')->label('Attend'),
                TextColumn::make('didnt_take_students')->label('Did Not Attend'),
                TextColumn::make('a_plus_count')->label('A+'),
                TextColumn::make('a_count')->label('A'),
                TextColumn::make('a_minus_count')->label('A-'),
                TextColumn::make('b_plus_count')->label('B+'),
                TextColumn::make('b_count')->label('B'),
                TextColumn::make('c_plus_count')->label('C+'),
                TextColumn::make('c_count')->label('C'),
                TextColumn::make('d_count')->label('D'),
                TextColumn::make('e_count')->label('E'),
                TextColumn::make('g_count')->label('G'),
                TextColumn::make('th_count')->label('TH'),
                TextColumn::make('gp')
                    ->label('GP (%)')
                    ->formatStateUsing(fn ($record) =>
                        $record->attended_students > 0
                            ? number_format(($record->gp / $record->attended_students), 2)
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
                        CONCAT(form, "-", class, "-", subject, "-", year) as id,
                        class,
                        form as tingkatan,
                        subject,
                        year,
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
                        END) as gp,
                        COALESCE(COUNT(CASE WHEN tov_g = \'A+\' THEN 1 END), 0) as a_plus_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'A\' THEN 1 END), 0) as a_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'A-\' THEN 1 END), 0) as a_minus_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'B+\' THEN 1 END), 0) as b_plus_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'B\' THEN 1 END), 0) as b_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'C+\' THEN 1 END), 0) as c_plus_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'C\' THEN 1 END), 0) as c_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'D\' THEN 1 END), 0) as d_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'E\' THEN 1 END), 0) as e_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'G\' THEN 1 END), 0) as g_count,
                        COALESCE(COUNT(CASE WHEN tov_g = \'TH\' THEN 1 END), 0) as th_count
                    ')
                    ->groupBy('form', 'class', 'subject', 'year');
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