<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctors';

    protected $fillable = [

        'user_id',
        'slug',
        'sip_number',
        'str_number',
        'specialization',
        'gender',
        'birth_date',
        'address',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $doctor): void {
            if (! empty($doctor->slug)) {
                return;
            }

            $user = $doctor->relationLoaded('user') ? $doctor->user : User::query()->find($doctor->user_id);
            $base = Str::slug($user?->name ?? 'doctor');
            $slug = $base;
            $i = 2;

            while (static::query()->where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i;
                $i++;
            }

            $doctor->slug = $slug;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi layanan tetap lewat pivot `doctor_services` yang memakai `user_id` (FK ke users).
     * Jadi parentKey di model ini adalah `user_id`.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'doctor_services',
            'user_id',
            'service_id',
            'user_id',
            'id'
        )->withPivot('priority')->withTimestamps();
    }

    public function doctorAvailabilities(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class, 'user_id', 'user_id');
    }
}
