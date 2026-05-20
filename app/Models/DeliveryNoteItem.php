<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryNoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_note_id',
        'article_id',
        'quantity',
        'return',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'return' => 'boolean',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}