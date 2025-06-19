<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassesResource\Pages;
use App\Filament\Resources\ClassesResource\Widgets\GpmpOverview;
use App\Models\Students;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ClassesResource extends Resource
{
    protected static ?string $model = Students::class;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Class Performances';
    protected static ?string $modelLabel = 'Class Performance';
    protected static ?string $navigationGroup = 'Academic';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('form')
                    ->label('Form')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('class')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('subject')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('year')
                    ->label('Year')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('total_students')
                    ->label('Total Students')
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('attended_students')
                    ->label('Attend')
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('didnt_take_students')
                    ->label('Did Not Attend')
                    ->numeric()
                    ->sortable(),
                    
                // Grade columns
                self::gradeColumn('a_plus_count', 'A+'),
                self::gradeColumn('a_count', 'A'),
                self::gradeColumn('a_minus_count', 'A-'),
                self::gradeColumn('b_plus_count', 'B+'),
                self::gradeColumn('b_count', 'B'),
                self::gradeColumn('c_plus_count', 'C+'),
                self::gradeColumn('c_count', 'C'),
                self::gradeColumn('d_count', 'D'),
                self::gradeColumn('e_count', 'E'),
                self::gradeColumn('g_count', 'G'),
                self::gradeColumn('th_count', 'TH'),
                
                TextColumn::make('avg_gp')
                    ->label('Avg GP')
                    ->numeric(decimalPlaces: 2)
                    ->state(fn ($record) => 
                        $record->attended_students > 0 
                            ? $record->gp / $record->attended_students 
                            : null
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label('Year')
                    ->options(
                        Students::query()
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                            ->toArray()
                    )
                    ->searchable(),
                    
                SelectFilter::make('class')
                    ->label('Class')
                    ->options(
                        Students::query()
                            ->distinct()
                            ->orderBy('class')
                            ->pluck('class', 'class')
                            ->toArray()
                    )
                    ->searchable(),
                    
                SelectFilter::make('form')
                    ->label('Form')
                    ->options(
                        Students::query()
                            ->distinct()
                            ->orderBy('form')
                            ->pluck('form', 'form')
                            ->toArray()
                    )
                    ->searchable(),
                    
                SelectFilter::make('subject')
                    ->label('Subject')
                    ->options(
                        Students::query()
                            ->distinct()
                            ->orderBy('subject')
                            ->pluck('subject', 'subject')
                            ->toArray()
                    )
                    ->searchable(),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('year', 'desc')
            ->deferLoading()
            ->persistFiltersInSession()
            ->striped();
    }

    public static function getWidgets(): array
    {
        return [
            GpmpOverview::class,
        ];
    }

    protected static function gradeColumn(string $field, string $label): TextColumn
    {
        return TextColumn::make($field)
            ->label($label)
            ->numeric()
            ->color(fn ($state) => self::getGradeColor($label))
            ->sortable();
    }

    protected static function getGradeColor(string $grade): ?string
    {
        return match($grade) {
            'A+', 'A', 'A-' => 'success',
            'B+', 'B' => 'primary',
            'C+', 'C' => 'warning',
            'D', 'E', 'G' => 'danger',
            default => null,
        };
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->selectRaw('
                CONCAT(form, "-", class, "-", subject, "-", year) as id,
                class,
                form,
                subject,
                year,
                COUNT(*) as total_students,
                COUNT(CASE WHEN UPPER(tov_g) IS NOT NULL AND UPPER(tov_g) NOT IN ("TH") THEN 1 END) as attended_students,
                COUNT(CASE WHEN UPPER(tov_g) IN ("TH") OR UPPER(tov_g) IS NULL THEN 1 END) as didnt_take_students,
                SUM(CASE UPPER(tov_g)
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
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "A+" THEN 1 END), 0) as a_plus_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "A" THEN 1 END), 0) as a_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "A-" THEN 1 END), 0) as a_minus_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "B+" THEN 1 END), 0) as b_plus_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "B" THEN 1 END), 0) as b_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "C+" THEN 1 END), 0) as c_plus_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "C" THEN 1 END), 0) as c_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "D" THEN 1 END), 0) as d_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "E" THEN 1 END), 0) as e_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "G" THEN 1 END), 0) as g_count,
                COALESCE(COUNT(CASE WHEN UPPER(tov_g) = "TH" THEN 1 END), 0) as th_count
            ')
            ->groupBy('form', 'class', 'subject', 'year');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClasses::route('/'),
        ];
    }
}