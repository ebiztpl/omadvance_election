<?php

namespace Modules\Sampark\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'sampark';
    protected $table = 'sampark_users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'email',
        'number',
        'otp',
        'role',
        'password',
        'date'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    
}
