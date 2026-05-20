<?php

namespace App\Filament\Resources\DeliveryNotes\Pages;

use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Support\DeliveryNumberGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryNote extends CreateRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    protected array $itemsData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->itemsData = $data['items'] ?? [];

        unset($data['items']);

        $data['delivery_number'] = $data['delivery_number'] ?: DeliveryNumberGenerator::make();
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