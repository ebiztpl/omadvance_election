<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $table = 'interest_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['interest_name'];
}
