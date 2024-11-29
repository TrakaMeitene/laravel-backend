<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'service',
        'end',
        'user',
        'made_by',
        'statuss',
        'visited'
    ];

    public function specialist()
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'id', 'made_by');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service', 'id');
    }
}
