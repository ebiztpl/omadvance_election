<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Politics extends Model
{
    protected $table = 'politics_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['name'];
}
