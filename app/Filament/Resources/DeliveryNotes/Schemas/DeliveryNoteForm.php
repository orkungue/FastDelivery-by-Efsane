<?php

namespace App\Filament\Resources\DeliveryNotes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliveryNoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lieferschein')
                    ->schema([
                        TextInput::make('delivery_number')
                            ->label('Lieferscheinnummer')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Select::make('customer_id')
                            ->label('Kunde')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        DatePicker::make('delivery_date')
                            ->label('Lieferdatum')
                            ->default(now()),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'draft' => 'Entwurf',
                                'open' => 'Offen',
                                'delivered' => 'Geliefert',
                                'cancelled' => 'Storniert',
                            ])
                            ->default('draft'),

                        Toggle::make('active')
                            ->label('Aktiv')
                            ->default(true),

                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}