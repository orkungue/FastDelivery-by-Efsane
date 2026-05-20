<?php

namespace App\Filament\Resources\DailyPlans\Pages;

use App\Filament\Resources\DailyPlans\DailyPlanResource;
use App\Models\Article;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyPlan extends EditRecord
{
    protected static string $resource = DailyPlanResource::class;

    protected array $itemsData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $existingItems = $this->record->items()
            ->get()
            ->keyBy('article_id');

        $data['items'] = Article::where('active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Article $article) use ($existingItems) {
                $existingItem = $existingItems->get($article->id);

                return [
                    'article_id' => $article->id,
                    'quantity' => $existingItem?->quantity ?? 0,
                    'delivered_quantity' => $existingItem?->delivered_quantity ?? 0,
                    'return_quantity' => $existingItem?->return_quantity ?? 0,
                ];
            })
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->itemsData = $data['items'] ?? [];

        unset($data['items']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->items()->delete();

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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}