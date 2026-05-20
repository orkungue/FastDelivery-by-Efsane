<?php

namespace App\Filament\Resources\DailyPlans\Schemas;

use App\Models\Article;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DailyPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('delivery_date')
                            ->label('Planungsdatum')
                            ->required()
                            ->default(now())
                            ->displayFormat('d.m.Y')
                            ->native(false),
                    ])
                    ->columnSpanFull(),

                Section::make()
                    ->schema([
                        Select::make('customer_id')
                            ->label('Kunde')
                            ->relationship('customer', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('user_id')
                            ->label('Fahrer')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Geplante Artikel')
                    ->schema([
                        ViewField::make('items')
                            ->hiddenLabel()
                            ->default(fn () => Article::where('active', true)
                                ->orderBy('name')
                                ->get()
                                ->map(fn (Article $article) => [
                                    'article_id' => $article->id,
                                    'article_label' => "{$article->article_number} - {$article->name}",
                                    'quantity' => 0,
                                ])
                                ->toArray())
                            ->view('filament.forms.article-planning-list')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}