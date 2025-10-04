<?php

namespace Modules\MasterManager\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;

class MasterUser extends Authenticatable
{
    protected $connection = 'master';
    protected $table = 'master_users';

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
