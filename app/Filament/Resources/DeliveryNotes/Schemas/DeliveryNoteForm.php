<?php

namespace App\Filament\Resources\DeliveryNotes\Schemas;

use App\Models\Article;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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
                            ->unique(ignoreRecord: true)
                            ->readOnly(fn () => ! Auth::user()?->isAdmin()),

                        Select::make('customer_id')
                            ->label('Kunde')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn () => ! Auth::user()?->isAdmin()),

                        Select::make('user_id')
                            ->label('Fahrer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn () => Auth::user()?->isAdmin()),

                        Hidden::make('user_id')
                            ->default(fn () => Auth::id())
                            ->visible(fn () => ! Auth::user()?->isAdmin()),

                        DatePicker::make('delivery_date')
                            ->label('Planungsdatum')
                            ->required()
                            ->disabled(fn () => ! Auth::user()?->isAdmin()),
                    ])
                    ->columns(2),

                Section::make('Positionen')
                    ->schema([
                        Repeater::make('items')
                            ->label('Artikel')
                            ->default(fn () => Article::where('active', true)
                                ->orderBy('name')
                                ->get()
                                ->map(fn (Article $article) => [
                                    'article_id' => $article->id,
                                    'quantity' => 0,
                                    'delivered_quantity' => 0,
                                    'return_quantity' => 0,
                                ])
                                ->toArray())
                            ->schema([
                                Hidden::make('article_id')
                                    ->required(),

                                Placeholder::make('article_name')
                                    ->label('Artikel')
                                    ->content(function (Get $get): string {
                                        $article = Article::find($get('article_id'));

                                        return $article
                                            ? "{$article->article_number} - {$article->name}"
                                            : '-';
                                    }),

                                TextInput::make('quantity')
                                    ->label('Geplant')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->readOnly(fn () => ! Auth::user()?->isAdmin()),

                                TextInput::make('delivered_quantity')
                                    ->label('Geliefert')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->default(0),

                                TextInput::make('return_quantity')
                                    ->label('Retoure')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->default(0),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),

                Section::make('Kunden-Unterschrift')
                    ->schema([
                        ViewField::make('customer_signature')
                            ->label('Kunden-Unterschrift')
                            ->view('filament.forms.signature-pad')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}