<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $table = 'business_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['business_name'];
}
