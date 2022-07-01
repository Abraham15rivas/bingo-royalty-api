<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'referred_by', 
        'referral_code'
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    public static function getUniqueReferralCode()
    {
        do {
            $code = Str::random(7);    
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    private function getReferredBy()
    {
        $referralCode = Cookie::get('referral');

        if ($referralCode)
            return User::where('referral_code', $referralCode)->value('id');

        return null;
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function historicals()
    {
        return $this->hasMany(Historical::class);
    }

    public function userCardboards()
    {
        return $this->hasMany(UserCardboard::class);
    }

    /**
     * Override the mail body for reset password notification mail.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\MailResetPasswordNotification($token));
    }
}
