<?php

namespace Modules\MasterManager\Entities;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $connection = 'master';
    protected $table = 'login_history';
    protected $primaryKey = 'login_history_id';
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'login_date_time',
        'ip',
        'user_agent'
    ];
}
