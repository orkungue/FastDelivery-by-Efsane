<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_number',
        'customer_id',
        'user_id',
        'delivery_date',
        'status',
        'notes',
        'active',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'active' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}