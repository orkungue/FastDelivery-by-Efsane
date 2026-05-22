<?php

namespace App\Filament\Resources\DailyPlans\Pages;

use App\Filament\Resources\DailyPlans\DailyPlanResource;
use App\Models\Article;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

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
                    'article_label' => "{$article->article_number} - {$article->name}",
                    'quantity' => $existingItem?->quantity ?? 0,
                ];
            })
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->items()->delete();

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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}