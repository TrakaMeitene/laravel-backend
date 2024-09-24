<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'statuss',
        'from',
        'to',
        'breakfrom',
        'breakto',
        'user',
    ];
}
