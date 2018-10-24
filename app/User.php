<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasAPiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, HasAPiTokens, softDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    protected $fillable = [
        'name', 'email', 'password','active','activation_token'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','activation_token'
    ];
}
