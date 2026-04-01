<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesWeekdayGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekday',
        'amount',
    ];

    protected $casts = [
        'weekday' => 'integer',
        'amount' => 'integer',
    ];
}
