<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'level',
        'description',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
