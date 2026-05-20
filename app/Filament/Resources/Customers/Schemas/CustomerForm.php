<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kundendaten')
                    ->schema([
                        TextInput::make('customer_number')
                            ->label('Kundennummer')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('name')
                            ->label('Name')
                            ->required(),

                        TextInput::make('contact_person')
                            ->label('Ansprechpartner'),

                        TextInput::make('phone')
                            ->label('Telefon'),

                        TextInput::make('email')
                            ->label('E-Mail')
                            ->email(),

                        Toggle::make('active')
                            ->label('Aktiv')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Adresse')
                    ->schema([
                        TextInput::make('street')
                            ->label('Straße'),

                        TextInput::make('postal_code')
                            ->label('PLZ'),

                        TextInput::make('city')
                            ->label('Ort'),

                        Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}