<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_number')
                    ->label('Kundennr.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_person')
                    ->label('Ansprechpartner')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable(),

                TextColumn::make('city')
                    ->label('Ort')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('active')
                    ->label('Aktiv')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('city')
                    ->label('Ort')
                    ->options(fn () => \App\Models\Customer::query()
                        ->whereNotNull('city')
                        ->where('city', '!=', '')
                        ->orderBy('city')
                        ->pluck('city', 'city')),

                Filter::make('active')
                    ->label('Nur aktive Kunden')
                    ->query(fn (Builder $query): Builder => $query->where('active', true))
                    ->default(),
            ])
            ->defaultSort('name')
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