<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'city',
        'state',
        'postcode',
        'country',
        'password',
        'avatar',
        'date_of_birth',
        'gender',
        'is_active',
        'is_verified',
        'verification_code',
        'verification_code_expires_at',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'avatar_url',
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
            'phone_verified_at' => 'datetime',
            'verification_code_expires_at' => 'datetime',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the avatar URL attribute.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        return asset('storage/'.$this->avatar);
    }

    /**
     * Check if customer can login with email.
     */
    public function canLoginWithEmail(): bool
    {
        return ! empty($this->email) && ! empty($this->email_verified_at);
    }

    /**
     * Check if customer can login with phone.
     */
    public function canLoginWithPhone(): bool
    {
        return ! empty($this->phone_number) && ! empty($this->phone_verified_at);
    }

    /**
     * Find customer by email or phone.
     */
    public static function findByEmailOrPhone(string $identifier)
    {
        return static::where(function ($query) use ($identifier) {
            $query->where('email', $identifier)
                ->orWhere('phone_number', $identifier);
        })->first();
    }

    /**
     * Scope to filter active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter verified customers.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Get the cart items for the customer.
     */
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the profile for the customer.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
