<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveryNotes';

    protected static ?string $title = 'Lieferschein-Historie';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('delivery_number')
                    ->label('Lieferscheinnummer')
                    ->required(),

                DatePicker::make('delivery_date')
                    ->label('Lieferdatum'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Entwurf',
                        'open' => 'Offen',
                        'delivered' => 'Geliefert',
                        'cancelled' => 'Storniert',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery_number')
                    ->label('Lieferscheinnr.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('delivery_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Fahrer')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Entwurf',
                        'open' => 'Offen',
                        'delivered' => 'Geliefert',
                        'cancelled' => 'Storniert',
                        default => $state,
                    }),

                TextColumn::make('items_count')
                    ->label('Positionen')
                    ->counts('items'),

                IconColumn::make('active')
                    ->label('Aktiv')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('print')
                    ->label('Drucken')
                    ->url(fn ($record) => route('delivery-notes.print', $record))
                    ->openUrlInNewTab(),

                EditAction::make(),

                DeleteAction::make(),
            ]);
    }
}