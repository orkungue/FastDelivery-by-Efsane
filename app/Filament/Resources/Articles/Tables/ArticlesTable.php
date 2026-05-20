<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('article_number')
                    ->label('Artikelnr.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Bezeichnung')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Einheit'),

                TextColumn::make('price')
                    ->label('Preis')
                    ->money('EUR')
                    ->sortable(),

                IconColumn::make('active')
                    ->label('Aktiv')
                    ->boolean(),
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