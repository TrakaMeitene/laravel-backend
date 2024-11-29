<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'specialist',
        'userid',
        'rating',
        'attended'
    ];

    public function specialist()
    {
        return $this->belongsTo(User::class, 'specialist', 'id');
    }
}