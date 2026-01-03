<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
