<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Special_availabilities extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'service',
        'from',
        'to',
        'days',
        'specialist'
    ];

    public function specialist(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'specialist');
    }

    public function service(): HasOne
    {
        return $this->hasone(Service::class, 'id', 'service');
    }
}
