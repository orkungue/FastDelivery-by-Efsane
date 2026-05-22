<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;

class DeliveryNotePrintController extends Controller
{
    public function __invoke(DeliveryNote $deliveryNote)
    {
        if (! auth()->user()?->isAdmin() && $deliveryNote->user_id !== auth()->id()) {
            abort(403);
        }

        $deliveryNote->load([
            'customer',
            'user',
            'items.article',
        ]);

        return view('delivery-notes.print', [
            'deliveryNote' => $deliveryNote,
        ]);
    }
}