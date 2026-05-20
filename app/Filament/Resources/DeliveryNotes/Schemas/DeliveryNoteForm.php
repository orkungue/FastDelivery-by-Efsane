<?php

namespace App\Filament\Resources\DeliveryNotes\Schemas;

use App\Models\Article;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                            ->label('Verfügbare Artikel')
                            ->default(fn () => Article::where('active', true)
                                ->orderBy('name')
                                ->get()
                                ->map(fn (Article $article) => [
                                    'article_id' => $article->id,
                                    'quantity' => 0,
                                    'unit' => $article->unit,
                                    'description' => null,
                                ])
                                ->toArray())
                            ->schema([
                                Hidden::make('article_id')
                                    ->required(),

                                Placeholder::make('article_name')
                                    ->label('Artikel')
                                    ->content(function (Get $get): string {
                                        $article = Article::find($get('article_id'));

                                        return $article?->name ?? '-';
                                    }),

                                TextInput::make('quantity')
                                    ->label('Menge')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->default(0),

                                TextInput::make('unit')
                                    ->label('Einheit')
                                    ->readOnly(),

                                Textarea::make('description')
                                    ->label('Beschreibung')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}