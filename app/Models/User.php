<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'title',
        'notes',
        'photo',
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    protected static function booted()
    {
        static::created(function ($user) {

            Patient::create([
                'user_id' => $user->id,
                'medical_record_number' => null,
                'nik' => null,
                'birth_date' => null,
                'address' => null,
            ]);
        });
    }

    // FILAMET AUTH
    public function canAccessPanel(Panel $panel): bool
    {
        if(Auth::check() && $this->role == UserRole::ADMIN){
            return true;
        }{



        return false;
    }


    // === Relasi ===
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'doctor_services')->withTimestamps();
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function doctorAvailabilities(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'user_id');
    }
}