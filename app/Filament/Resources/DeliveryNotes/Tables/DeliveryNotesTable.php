<?php

namespace App\Filament\Resources\DeliveryNotes\Tables;

use App\Models\Customer;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DeliveryNotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->visible(fn () => Auth::user()?->isAdmin())
                    ->searchable()
                    ->sortable(),

                TextColumn::make('delivery_date')
                    ->label('Lieferdatum')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'planned' => 'Geplant',
                        'delivered' => 'Geliefert',
                        'cancelled' => 'Storniert',
                        default => $state ?? '-',
                    })
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label('Positionen')
                    ->counts('items')
                    ->sortable(),

                IconColumn::make('has_return')
                    ->label('Retoure')
                    ->boolean()
                    ->state(fn ($record): bool => $record->items()->where('return_quantity', '>', 0)->exists()),

                IconColumn::make('active')
                    ->label('Aktiv')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planned' => 'Geplant',
                        'delivered' => 'Geliefert',
                        'cancelled' => 'Storniert',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Kunde')
                    ->options(fn () => Customer::query()
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('user_id')
                    ->label('Fahrer')
                    ->visible(fn () => Auth::user()?->isAdmin())
                    ->options(fn () => User::query()
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable(),

                Filter::make('has_return')
                    ->label('Mit Retoure')
                    ->query(fn (Builder $query): Builder => $query->whereHas('items', function (Builder $query) {
                        return $query->where('return_quantity', '>', 0);
                    })),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Excel Export')
                    ->action(fn ($livewire) => self::exportFilteredDeliveryNotes($livewire)),
            ])
            ->defaultSort('delivery_date', 'desc')
            ->recordActions([
                Action::make('print')
                    ->label('Drucken')
                    ->url(fn ($record) => route('delivery-notes.print', $record))
                    ->openUrlInNewTab(),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function exportFilteredDeliveryNotes($livewire)
    {
        $deliveryNotes = $livewire
            ->getFilteredTableQuery()
            ->with([
                'customer',
                'user',
                'items.article',
            ])
            ->get();

        $filename = 'lieferscheine-export-' . now()->format('Y-m-d-H-i') . '.csv';

        return response()->streamDownload(function () use ($deliveryNotes) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Lieferdatum',
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