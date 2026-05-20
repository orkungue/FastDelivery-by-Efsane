<?php

namespace App\Filament\Resources\DeliveryNotes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryNotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery_number')
                    ->label('Lieferscheinnr.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('delivery_date')
                    ->label('Lieferdatum')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Entwurf',
                        'open' => 'Offen',
                        'delivered' => 'Geliefert',
                        'cancelled' => 'Storniert',
                        default => $state,
                    })
                    ->sortable(),

                IconColumn::make('active')
                    ->label('Aktiv')
                    ->boolean(),
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