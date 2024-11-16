<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice';
    protected $fillable = [
        'user',
        'invoice',
        'status',
        'paid_date',
        'customer',
        'serial_number',
        'service',
        'price',
        'external_customer',
        'booking'
    ];

    public function specialist()
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer', 'id');
    }
}
