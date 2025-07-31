<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Religion extends Model
{
    protected $table = 'religion_master';
    protected $primaryKey = 'religion_id';
    public $timestamps = false;

    protected $fillable = ['religion_name'];
}
