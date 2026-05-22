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
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class DeliveryNoteForm
{
    public static function configure(Schema $schema): Schema
    {
        $lockedForDriver = fn ($record): bool =>
            ! Auth::user()?->isAdmin()
            && filled($record?->customer_signature);

        return $schema
            ->components([
                Section::make('Lieferschein')
                    ->schema([
                        Placeholder::make('delivery_info')
                            ->label('')
                            ->content(fn ($record): string =>
                                'Lieferschein: ' . ($record?->delivery_number ?? '-') .
                                ' | Planungsdatum: ' . ($record?->delivery_date?->format('d.m.Y') ?? '-')
                            )
                            ->visible(fn () => ! Auth::user()?->isAdmin())
                            ->columnSpanFull(),

                        TextInput::make('delivery_number')
                            ->label('Lieferscheinnummer')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->visible(fn () => Auth::user()?->isAdmin()),

                        DatePicker::make('delivery_date')
                            ->label('Planungsdatum')
                            ->required()
                            ->displayFormat('d.m.Y')
                            ->native(false)
                            ->visible(fn () => Auth::user()?->isAdmin()),

                        Select::make('customer_id')
                            ->label('Kunde')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->visible(fn () => Auth::user()?->isAdmin()),

                        Select::make('user_id')
                            ->label('Fahrer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn () => Auth::user()?->isAdmin()),

                        Placeholder::make('Link:')
                            ->label('')
                            ->content(function ($record) {
                                if (! $record?->customer) {
                                    return '';
                                }

                                $customer = $record->customer;

                                $address = trim(collect([
                                    $customer->street,
                                    trim(($customer->postal_code ?? '') . ' ' . ($customer->city ?? '')),
                                ])->filter()->implode(', '));

                                if (! $address) {
                                    return '';
                                }

                                $mapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($address);

                                return new HtmlString(
                                    '<a href="' . e($mapsUrl) . '" target="_blank" class="text-primary-600 underline">Adresse in Maps öffnen</a>'
                                );
                            })
                            ->visible(fn ($record) => filled($record?->customer_id))
                            ->columnSpanFull(),

                        Hidden::make('user_id')
                            ->default(fn () => Auth::id())
                            ->visible(fn () => ! Auth::user()?->isAdmin()),

                        Textarea::make('notes')
                            ->label('Notiz')
                            ->rows(3)
                            ->disabled($lockedForDriver)
                            ->columnSpanFull(),
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
                                    ->disabled($lockedForDriver),

                                TextInput::make('return_quantity')
                                    ->label('Retoure')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->disabled($lockedForDriver),
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
                            ->visible(fn ($record) => ! $lockedForDriver($record))
                            ->columnSpanFull(),

                        Placeholder::make('customer_signature_preview')
                            ->label('Kunden-Unterschrift')
                            ->content(fn ($record) => new HtmlString(
                                $record?->customer_signature
                                    ? '<img src="' . e($record->customer_signature) . '" style="max-width: 320px; max-height: 140px;">'
                                    : '-'
                            ))
                            ->visible($lockedForDriver)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}