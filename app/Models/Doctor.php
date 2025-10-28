<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Doctor extends User
{
    //
    protected $table = 'users';


    protected static function booted(): void
    {
        static::addGlobalScope('doctors', function (Builder $q) {
            $q->where('role', UserRole::DOCTOR); // or 'doctor' depending on your enum
        });
    }

    // override services relation explicitly if you want
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'doctor_services', 'user_id', 'service_id')->withTimestamps();
    }
}
