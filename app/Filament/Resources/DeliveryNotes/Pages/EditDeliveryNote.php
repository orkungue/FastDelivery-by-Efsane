<?php

namespace App\Filament\Resources\DeliveryNotes\Pages;

use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Models\Article;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryNote extends EditRecord
{
    protected static string $resource = DeliveryNoteResource::class;

    protected array $itemsData = [];

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! auth()->user()?->isAdmin() && $this->record->user_id !== auth()->id()) {
            abort(403);
        }
    }

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

        if (! auth()->user()?->isAdmin()) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->items()->delete();

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

    protected function getHeaderActions(): array
    {
        return auth()->user()?->isAdmin()
            ? [DeleteAction::make()]
            : [];
    }
}