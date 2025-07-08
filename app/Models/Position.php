<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'position_master';
    protected $primaryKey = 'position_id';
    public $timestamps = false;

    protected $fillable = ['position_name', 'level', 'post_date'];
}
