<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personalnr',
        'email',
        'password',
        'picture',
        'scope',
        'avatar',
        'phone',
        'adress',
        'bio',
        'occupation',
        'city',
        'urlname',
        'bank',
        'facebook_id',
        'abonament',
        'onboarded'

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    public function settings()
    {
        return $this->hasMany(Settings::class, 'user', 'id');
    }

    public function vacation()
    {
        return $this->hasMany(Vacation::class, 'user', 'id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'user', 'id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user', 'id');
    }

    public function activeBookings(): HasMany
    {
        return $this->bookings()->where('statuss', 'active');
    }

    public function specialtimes(): HasMany
    {
        return $this->hasMany(Special_availabilities::class, 'specialist', 'id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user', 'id');
    }

    public function customerInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer', 'id');
    }

    public function specialistInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'user', 'id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Clients::class, 'specialist', 'id');
    }


    public function sendPasswordResetNotification($token): void
    {
        $url = env('FRONTEND_URL') . '/login/renewpassword?token='.$token;
     
        $this->notify(new ResetPasswordNotification($url));
    }
}
