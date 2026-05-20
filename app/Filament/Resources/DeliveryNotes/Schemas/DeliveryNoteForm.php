<?php

namespace App\Filament\Resources\DeliveryNotes\Schemas;

use App\Models\Article;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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

                Section::make('Positionen')
                    ->schema([
                        Repeater::make('items')
                            ->label('Positionen')
                            ->relationship()
                            ->schema([
                                Select::make('article_id')
                                    ->label('Artikel')
                                    ->relationship('article', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        $article = Article::find($state);

                                        if (! $article) {
                                            return;
                                        }

                                        $set('unit', $article->unit);
                                    }),

                                TextInput::make('quantity')
                                    ->label('Menge')
                                    ->numeric()
                                    ->required()
                                    ->default(1),

                                TextInput::make('unit')
                                    ->label('Einheit'),

                                Textarea::make('description')
                                    ->label('Beschreibung')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Position hinzufügen')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}