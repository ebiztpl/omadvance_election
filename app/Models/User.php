<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin_master';

    protected $primaryKey = 'admin_id';

    protected $fillable = ['admin_name', 'admin_pass', 'role', 'posted_date', 'modify_at'];

    protected $hidden = ['admin_pass', 'remember_token'];

    public $timestamps = false;

    public function setAdminPassAttribute($value)
    {
        if (!\Illuminate\Support\Str::startsWith($value, '$2y$')) {
            $value = bcrypt($value);
        }
        $this->attributes['admin_pass'] = $value;
    }

    public function getAuthPassword()
    {
        return $this->admin_pass;
    }

    public function getRouteKeyName()
    {
        return 'admin_id';
    }
}
