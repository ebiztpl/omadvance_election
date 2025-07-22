<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adhikari extends Model
{
    protected $table = 'create_adhikari_master';
    protected $primaryKey = 'adhikari_id'; // assuming `id` is the primary key
    public $timestamps = true; // if you have created_at and updated_at columns

    protected $fillable = [
        'department_id',
        'designation_id',
        'name',
        'mobile',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
}
