<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_number',
        'name',
        'unit',
        'price',
        'description',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function deliveryNoteItems()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}