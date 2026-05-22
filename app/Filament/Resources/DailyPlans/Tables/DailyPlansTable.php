<?php

namespace App\Filament\Resources\DailyPlans\Tables;

use App\Models\Customer;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DailyPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('delivery_date')
                    ->label('Tag')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('delivery_number')
                    ->label('Lieferscheinnr.')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Kunde')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Fahrer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => 'Geplant',
                        'delivered' => 'Geliefert',
                        'cancelled' => 'Storniert',
                        default => $state,
                    }),

                TextColumn::make('items_count')
                    ->label('Positionen')
                    ->counts('items'),
            ])
            ->filters([
                Filter::make('today')
                    ->label('Heute')
                    ->query(fn (Builder $query): Builder => $query->whereDate('delivery_date', today())),

                SelectFilter::make('customer_id')
                    ->label('Kunde')
                    ->options(fn () => Customer::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('user_id')
                    ->label('Fahrer')
                    ->options(fn () => User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planned' => 'Geplant',
                        'delivered' => 'Geliefert',
                        'cancelled' => 'Storniert',
                    ]),
            ])
            ->defaultSort('delivery_date', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
    Action::make('export_excel')
        ->label('Excel Export')
        ->action(fn ($livewire) => self::exportFilteredPlans($livewire)),
])
->toolbarActions([
    BulkActionGroup::make([
        DeleteBulkAction::make(),
    ]),
]);
    }

    public static function exportFilteredPlans($livewire)
    {
        $deliveryNotes = $livewire
            ->getFilteredTableQuery()
            ->with([
                'customer',
                'user',
                'items.article',
            ])
            ->get();

        $filename = 'tagesplanung-export-' . now()->format('Y-m-d-H-i') . '.csv';

        return response()->streamDownload(function () use ($deliveryNotes) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Planungsdatum',
                'Lieferscheinnummer',
                'Status',
                'Kunde',
                'Fahrer',
                'Artikelnummer',
                'Artikel',
                'Geplante Menge',
                'Gelieferte Menge',
                'Retoure Menge',
                'Notiz',
            ], ';');

            foreach ($deliveryNotes as $deliveryNote) {
                foreach ($deliveryNote->items as $item) {
                    fputcsv($handle, [
                        $deliveryNote->delivery_date?->format('d.m.Y'),
                        $deliveryNote->delivery_number,
                        match ($deliveryNote->status) {
                            'planned' => 'Geplant',
                            'delivered' => 'Geliefert',
                            'cancelled' => 'Storniert',
                            default => $deliveryNote->status,
                        },
                        $deliveryNote->customer?->name,
                        $deliveryNote->user?->name,
                        $item->article?->article_number,
                        $item->article?->name,
                        number_format((float) $item->quantity, 2, ',', '.'),
                        number_format((float) $item->delivered_quantity, 2, ',', '.'),
                        number_format((float) $item->return_quantity, 2, ',', '.'),
                        $deliveryNote->notes,
                    ], ';');
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}