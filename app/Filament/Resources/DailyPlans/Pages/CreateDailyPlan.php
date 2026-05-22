<?php

namespace App\Filament\Resources\DailyPlans\Pages;

use App\Filament\Resources\DailyPlans\DailyPlanResource;
use App\Support\DeliveryNumberGenerator;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateDailyPlan extends CreateRecord
{
    protected static string $resource = DailyPlanResource::class;

    protected array $itemsData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->itemsData = $data['items'] ?? [];

        $hasPlannedItems = collect($this->itemsData)
            ->contains(fn (array $item): bool => (float) ($item['quantity'] ?? 0) > 0);

        if (! $hasPlannedItems) {
            Notification::make()
                ->title('Keine Artikelmenge eingetragen')
                ->body('Bitte trage bei mindestens einem Artikel eine geplante Menge größer als 0 ein.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'items' => 'Bitte trage bei mindestens einem Artikel eine geplante Menge größer als 0 ein.',
            ]);
        }

        unset($data['items']);

        session()->forget('daily_plan_last_customer_id');

        session([
            'daily_plan_last_user_id' => $data['user_id'] ?? null,
        ]);

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