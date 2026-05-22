<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Benutzer')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required(),

                        TextInput::make('username')
                            ->label('Benutzername')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->label('Passwort')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state): bool => filled($state)),

                        Select::make('role')
                            ->label('Rolle')
                            ->required()
                            ->options([
                                'admin' => 'Admin',
                                'employee' => 'Fahrer',
                            ])
                            ->default('employee'),
                    ])
                    ->columns(2),
            ]);
    }
}