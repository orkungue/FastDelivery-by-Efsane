<?php

namespace App\Filament\Resources\DailyPlans\Pages;

use App\Filament\Resources\DailyPlans\DailyPlanResource;
use App\Support\DeliveryNumberGenerator;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyPlan extends CreateRecord
{
    protected static string $resource = DailyPlanResource::class;

    protected array $itemsData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->itemsData = $data['items'] ?? [];

        unset($data['items']);

        $data['delivery_number'] = DeliveryNumberGenerator::make();

        $data['status'] = 'planned';

        $data['active'] = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        foreach ($this->itemsData as $item) {

            $quantity = (float) ($item['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $this->record->items()->create([
                'article_id' => $item['article_id'],
                'quantity' => $quantity,
                'delivered_quantity' => 0,
                'return_quantity' => 0,
            ]);
        }
    }
}