<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Vacation extends Model
{
    use HasFactory;


    protected $fillable = [
        "date",
        'user',
    ];
}
