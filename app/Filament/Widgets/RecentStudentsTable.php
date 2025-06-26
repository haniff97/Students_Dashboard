<?php

namespace App\Filament\Widgets;

use App\Models\Students;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentStudentsTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Students';
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Students::query()
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('form')
                    ->label('Form')
                    ->sortable(),
                    
                TextColumn::make('class')
                    ->label('Class')
                    ->sortable(),
                    
                TextColumn::make('subject')
                    ->label('Subject')
                    ->sortable(),
                    
                TextColumn::make('tov_m')
                    ->label('TOV Marks')
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('tov_g')
                    ->label('TOV Grade')
                    ->badge()
                    ->color(fn (string $state): string => match (strtoupper($state)) {
                        'A+', 'A', 'A-' => 'success',
                        'B+', 'B' => 'primary',
                        'C+', 'C' => 'warning',
                        'D', 'E', 'G' => 'danger',
                        'TH' => 'gray',
                        default => 'gray',
                    }),
                    
                TextColumn::make('year')
                    ->label('Year')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Students $record): string => route('filament.admin.resources.students.edit', $record))
                    ->icon('heroicon-m-eye')
                    ->label('View'),
            ])
            ->bulkActions([
                //
            ])
            ->striped()
            ->paginated(false);
    }
} 