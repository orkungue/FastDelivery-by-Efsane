<?php

namespace App\Filament\Resources\DeliveryNotes\Pages;

use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryNote extends CreateRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    protected array $itemsData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->itemsData = $data['items'] ?? [];

        unset($data['items']);

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->itemsData as $item) {
            if ((float) ($item['quantity'] ?? 0) <= 0) {
                continue;
            }

            $this->record->items()->create([
                'article_id' => $item['article_id'],
                'quantity' => $item['quantity'],
                'return' => (bool) ($item['return'] ?? false),
            ]);
        }
    }
}