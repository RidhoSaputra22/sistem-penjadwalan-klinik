<?php

namespace App\Models;

use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RrPointer extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'service_id',
        'last_assigned_doctor_id',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'last_assigned_doctor_id');
    }
}
