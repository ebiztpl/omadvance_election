<?php

namespace Modules\Sampark\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $connection = 'sampark'; // points to sampark_db
    protected $table = 'messages';
    protected $fillable = ['user_id', 'message'];
}
