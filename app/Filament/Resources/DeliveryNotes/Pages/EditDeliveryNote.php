<?php

namespace App\Filament\Resources\DeliveryNotes\Pages;

use App\Filament\Resources\DeliveryNotes\DeliveryNoteResource;
use App\Models\Article;
use Filament\Actions\Action;
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

    protected function isLockedForCurrentDriver(): bool
    {
        return ! auth()->user()?->isAdmin()
            && filled($this->record?->customer_signature);
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
                    'delivered_quantity' => $existingItem?->delivered_quantity ?? 0,
                    'return_quantity' => $existingItem?->return_quantity ?? 0,
                ];
            })
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->isLockedForCurrentDriver()) {
            abort(403);
        }

        $this->itemsData = $data['items'] ?? [];

        unset($data['items']);

        if (! auth()->user()?->isAdmin()) {
            unset(
                $data['delivery_number'],
                $data['customer_id'],
                $data['delivery_date']
            );

            $data['user_id'] = auth()->id();
        }

        if (filled($data['customer_signature'] ?? null)) {
            $data['status'] = 'delivered';
        }

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

        $this->record->refresh();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', [
            'record' => $this->record,
        ]);
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->visible(fn () => ! $this->isLockedForCurrentDriver());
    }

    protected function getHeaderActions(): array
    {
        return auth()->user()?->isAdmin()
            ? [DeleteAction::make()]
            : [];
    }
}