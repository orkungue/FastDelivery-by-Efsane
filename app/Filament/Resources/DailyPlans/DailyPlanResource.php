<?php

namespace App\Filament\Resources\DailyPlans;

use App\Filament\Resources\DailyPlans\Pages\CreateDailyPlan;
use App\Filament\Resources\DailyPlans\Pages\EditDailyPlan;
use App\Filament\Resources\DailyPlans\Pages\ListDailyPlans;
use App\Filament\Resources\DailyPlans\Schemas\DailyPlanForm;
use App\Filament\Resources\DailyPlans\Tables\DailyPlansTable;
use App\Models\DeliveryNote;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DailyPlanResource extends Resource
{
    protected static ?string $model = DeliveryNote::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $navigationLabel = 'Tagesplanung';

    protected static ?string $modelLabel = 'Tagesplanung';

    protected static ?string $pluralModelLabel = 'Tagesplanung';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return DailyPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyPlansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyPlans::route('/'),
            'create' => CreateDailyPlan::route('/create'),
            'edit' => EditDailyPlan::route('/{record}/edit'),
        ];
    }
}