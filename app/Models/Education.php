<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'education_master';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['education_name'];
}
