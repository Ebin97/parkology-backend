<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Mediable;
use App\Traits\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory,  Mediable, SoftDeletes, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'language',
        'type_id',
        'password',
        'work_place',
        'pharmacy_id',
        'city_id',
        'language',
        'email_verified_at',
        'document_verified',
        'fcm',
        'avatar'
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
    ];

    public function Type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function City()
    {
        return $this->belongsTo(City::class, 'city_id');
    }


    public function Pharmacy()
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function UserQuiz()
    {
        return $this->hasMany(UserQuiz::class, 'user_id');
    }

    public function UserToken()
    {
        return $this->hasMany(UserToken::class, 'user_id');
    }

    public function userScores()
    {
        return $this->hasMany(UserScore::class, 'user_id');
    }

    public function notification()
    {
        return $this->hasMany(Notifiaction::class, 'user_id');
    }


}
