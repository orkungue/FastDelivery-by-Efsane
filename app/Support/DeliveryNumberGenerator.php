<?php

namespace App\Support;

use App\Models\DeliveryNote;

class DeliveryNumberGenerator
{
    public static function make(): string
    {
        $year = now()->year;

        $lastNumber = DeliveryNote::query()
            ->where('delivery_number', 'like', "LS-{$year}-%")
            ->orderByDesc('id')
            ->value('delivery_number');

        if (! $lastNumber) {
            return "LS-{$year}-0001";
        }

        $lastSequence = (int) substr($lastNumber, -4);
        $nextSequence = $lastSequence + 1;

        return 'LS-' . $year . '-' . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}