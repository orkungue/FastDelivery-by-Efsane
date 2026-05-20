<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_number',
        'name',
        'contact_person',
        'phone',
        'email',
        'street',
        'postal_code',
        'city',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function deliveryNotes()
    {
        return $this->hasMany(DeliveryNote::class);
    }
}