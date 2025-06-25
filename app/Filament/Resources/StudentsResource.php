<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentsResource\Pages;
use App\Filament\Resources\StudentsResource\Widgets\BlogPostsChart;
use App\Models\Students;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentsResource extends Resource
{
    protected static ?string $model = Students::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    
    public static function getNavigationLabel(): string
    {
        return 'Student Performances'; 
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('class')->required(),
                TextInput::make('form')->required(),
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

    public static function getWidgets(): array
    {
        return [
            BlogPostsChart::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                            TextColumn::make('name')
                ->label('Student')
                ->action(function ($record, $livewire) {
                    $livewire->dispatch('student-selected', studentId: $record->id);
                })
                ->extraAttributes([
                    'class' => 'cursor-pointer text-primary-600 hover:underline',
                ]),
                TextColumn::make('class')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('form')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable(),
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
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->options(fn() => Students::query()
                        ->select('year')
                        ->distinct()
                        ->orderBy('year', 'desc')
                        ->pluck('year', 'year')
                        ->toArray())
                    ->searchable(),
                SelectFilter::make('subject')
                    ->options(fn() => Students::query()
                        ->select('subject')
                        ->distinct()
                        ->orderBy('subject')
                        ->pluck('subject', 'subject')
                        ->toArray())
                    ->searchable(),
                Filter::make('form_and_class')
                    ->form([
                        Select::make('form')
                            ->options(fn() => Students::query()
                                ->select('form')
                                ->distinct()
                                ->orderBy('form')
                                ->pluck('form', 'form')
                                ->toArray()),
                        Select::make('class')
                            ->options(function (callable $get) {
                                $form = $get('form');
                                return $form ? Students::query()
                                    ->where('form', $form)
                                    ->select('class')
                                    ->distinct()
                                    ->orderBy('class')
                                    ->pluck('class', 'class')
                                    ->toArray() : [];
                            }),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['form'], fn($q) => $q->where('form', $data['form']))
                            ->when($data['class'], fn($q) => $q->where('class', $data['class']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->deferLoading()
            ->paginated([10, 25, 50, 100,1500]);
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