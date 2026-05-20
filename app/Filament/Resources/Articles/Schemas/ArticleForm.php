<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Artikeldaten')
                    ->schema([
                        TextInput::make('article_number')
                            ->label('Artikelnummer')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('name')
                            ->label('Bezeichnung')
                            ->required(),

                        TextInput::make('unit')
                            ->label('Einheit')
                            ->required()
                            ->default('Stück'),

                        TextInput::make('price')
                            ->label('Preis')
                            ->numeric()
                            ->prefix('€'),

                        Toggle::make('active')
                            ->label('Aktiv')
                            ->default(true),

                        Textarea::make('description')
                            ->label('Beschreibung')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}