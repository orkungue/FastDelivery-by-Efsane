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

        if (! auth()->user()?->isAdmin()) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->itemsData as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $returnQuantity = (float) ($item['return_quantity'] ?? 0);

            if ($quantity <= 0 && $returnQuantity <= 0) {
                continue;
            }

            $this->record->items()->create([
                'article_id' => $item['article_id'],
                'quantity' => $quantity,
                'return_quantity' => $returnQuantity,
            ]);
        }
    }
}