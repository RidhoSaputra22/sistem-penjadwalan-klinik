<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Holiday extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'full_day',
    ];

    protected $casts = [
        'date' => 'date',
        'full_day' => 'boolean',
    ];
}
