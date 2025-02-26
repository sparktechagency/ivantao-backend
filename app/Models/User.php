<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $dates   = ['deleted_at'];
    protected $guarded = ['id'];

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
            'email_verified_at'           => 'datetime',
            'password'                    => 'hashed',
            'completed_stripe_onboarding' => 'bool',

            // 'document' => 'array',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

// Accessor in User model
    public function getImageAttribute($image)
    {
        $defaultImage = 'default_user.png';
        return asset('uploads/profile_images/' . ($image ?? $defaultImage));
    }

    //connection with services
    public function services()
    {
        return $this->hasMany(Services::class, 'provider_id');
    }
    public function service()
    {
        return $this->hasMany(Services::class);
    }
    public function servicesTaken()
    {
        return $this->hasManyThrough(Services::class, 'provider_id', 'service_id');
    }
    //money withdraw model
    public function withdrawMoney()
    {
        return $this->hasMany(WithdrawMoney::class, 'provider_id');
    }
    public function serviceCategories()
    {
        return $this->hasMany(ServiceCategory::class, 'provider_id');
    }

}
