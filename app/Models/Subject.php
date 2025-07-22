<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'complaint_subject_master';
    protected $primaryKey = 'subject_id';
    public $timestamps = false;

    protected $fillable = ['department_id', 'subject'];

    public function department()
    {
        return $this->belongsTo(department::class, 'department_id');
    }
}
