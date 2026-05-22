<?php

namespace App\Filament\Resources\DeliveryNotes\Pages;

use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Support\DeliveryNumberGenerator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateDeliveryNote extends CreateRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    protected array $itemsData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->itemsData = $data['items'] ?? [];

        $hasItems = collect($this->itemsData)
            ->contains(function (array $item): bool {
                return (float) ($item['quantity'] ?? 0) > 0
                    || (float) ($item['delivered_quantity'] ?? 0) > 0
                    || (float) ($item['return_quantity'] ?? 0) > 0;
            });

        if (! $hasItems) {
            Notification::make()
                ->title('Keine Artikelmenge eingetragen')
                ->body('Bitte trage bei mindestens einem Artikel eine Menge größer als 0 ein.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'items' => 'Bitte trage bei mindestens einem Artikel eine Menge größer als 0 ein.',
            ]);
        }

        unset($data['items']);

        $data['delivery_number'] = DeliveryNumberGenerator::make();
        $data['status'] = 'planned';
        $data['active'] = true;

        if (! auth()->user()?->isAdmin()) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->itemsData as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $deliveredQuantity = (float) ($item['delivered_quantity'] ?? 0);
            $returnQuantity = (float) ($item['return_quantity'] ?? 0);

            if ($quantity <= 0 && $deliveredQuantity <= 0 && $returnQuantity <= 0) {
                continue;
            }

            $this->record->items()->create([
                'article_id' => $item['article_id'],
                'quantity' => $quantity,
                'delivered_quantity' => $deliveredQuantity,
                'return_quantity' => $returnQuantity,
            ]);
        }
    }
}