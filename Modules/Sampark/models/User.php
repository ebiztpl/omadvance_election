<?php

namespace Modules\Sampark\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'sampark';
    protected $table = 'sampark_users';
    protected $fillable = ['name', 'email', 'number', 'otp', 'date', 'role', 'password'];
}
