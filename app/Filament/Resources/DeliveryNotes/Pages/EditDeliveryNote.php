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
                    'return' => $existingItem?->return ?? false,
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

    protected function getHeaderActions(): array
    {
        return auth()->user()?->isAdmin()
            ? [DeleteAction::make()]
            : [];
    }
}