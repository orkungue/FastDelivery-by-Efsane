<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;

class DeliveryNotePrintController extends Controller
{
    public function __invoke(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load([
            'customer',
            'items.article',
        ]);

        return view('delivery-notes.print', [
            'deliveryNote' => $deliveryNote,
        ]);
    }
}