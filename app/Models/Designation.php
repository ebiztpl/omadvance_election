<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $table = 'designation_master';
    protected $primaryKey = 'designation_id';
    public $timestamps = false;

    protected $fillable = ['department_id', 'designation_name'];

    public function department()
    {
        return $this->belongsTo(department::class, 'department_id');
    }
}
