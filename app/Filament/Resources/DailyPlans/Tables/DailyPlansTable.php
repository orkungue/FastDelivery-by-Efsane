<?php

namespace App\Filament\Resources\DailyPlans\Tables;

use App\Models\Customer;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery_date')
                    ->label('Tag')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('delivery_number')
                    ->label('Lieferscheinnr.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Fahrer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => 'Geplant',
                        'delivered' => 'Geliefert',
                        'cancelled' => 'Storniert',
                        default => $state,
                    }),

                TextColumn::make('items_count')
                    ->label('Positionen')
                    ->counts('items'),
            ])
            ->filters([
                Filter::make('today')
                    ->label('Heute')
                    ->query(fn (Builder $query): Builder => $query->whereDate('delivery_date', today())),

                SelectFilter::make('customer_id')
                    ->label('Kunde')
                    ->options(fn () => Customer::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('user_id')
                    ->label('Fahrer')
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planned' => 'Geplant',
                        'delivered' => 'Geliefert',
                        'cancelled' => 'Storniert',
                    ]),
            ])
            ->defaultSort('delivery_date', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}