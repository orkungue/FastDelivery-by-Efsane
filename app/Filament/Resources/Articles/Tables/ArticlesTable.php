<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->filters([
                SelectFilter::make('unit')
                    ->label('Einheit')
                    ->options(fn () => Article::query()
                        ->whereNotNull('unit')
                        ->where('unit', '!=', '')
                        ->orderBy('unit')
                        ->pluck('unit', 'unit')),

                Filter::make('active')
                    ->label('Nur aktive Artikel')
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