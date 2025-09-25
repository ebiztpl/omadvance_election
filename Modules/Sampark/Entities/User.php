<?php

namespace Modules\Sampark\Entities;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'sampark';
    protected $table = 'sampark_users';
    protected $fillable = [
        'name',
        'email',
        'number',
        'otp',
        'role',
        'password',
        'date'
    ];
    protected $hidden = ['password'];
}
