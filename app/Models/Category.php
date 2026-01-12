<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            if (! empty($category->slug)) {
                return;
            }

            $base = Str::slug($category->name ?? 'category');
            $slug = $base;
            $i = 2;

            while (static::query()->where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i;
                $i++;
            }

            $category->slug = $slug;
        });
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
