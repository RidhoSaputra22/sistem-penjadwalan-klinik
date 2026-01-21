<?php

namespace App\Models;

use App\Helpers\CodeGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Service extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'category_id',
        'priority_id',
        'name',
        'slug',
        'code',
        'duration_minutes',
        'price',
        'description',
        'photo',
        'color',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'price' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function (self $service) {
            if (empty($service->code)) {
                $service->code = CodeGenerator::service();
            }

            if (empty($service->slug)) {
                $base = Str::slug($service->name ?? 'service');
                $slug = $base;
                $i = 2;

                while (static::query()->where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i;
                    $i++;
                }

                $service->slug = $slug;
            }
        });
    }

    public function doctors()
    {
        return $this->belongsToMany(User::class, 'doctor_services', 'service_id', 'user_id')
            ->withPivot('priority')
            ->withTimestamps();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function rrPointer()
    {
        return $this->hasOne(RrPointer::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
