<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Models\DeliveryNote;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TodayDeliveryNotesWidget extends BaseWidget
{
    protected static ?string $heading = 'Meine heutigen Fahrten';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DeliveryNote::query()
                    ->whereDate('delivery_date', today())
                    ->where('user_id', auth()->id())
                    ->orderBy('delivery_date')
            )

            ->columns([
                TextColumn::make('delivery_number')
                    ->label('Lieferscheinnr.')
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable(),

                TextColumn::make('delivery_date')
                    ->label('Datum')
                    ->date('d.m.Y'),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => 'Geplant',
                        'delivered' => 'Geliefert',
                        default => $state,
                    }),
            ])

            ->recordActions([
                Action::make('open')
                    ->label('Öffnen')
                    ->url(fn (DeliveryNote $record): string =>
                        DeliveryNoteResource::getUrl('edit', [
                            'record' => $record,
                        ])
                    ),
            ])

            ->paginated(false)

            ->emptyStateHeading('Keine Fahrten für heute');
    }
}