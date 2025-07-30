<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Step2 extends Model
{
    protected $table = 'step2';
    protected $primaryKey = 'registration_id';
    public $timestamps = false;

    protected $fillable = [
        'registration_id',
        'division_id',
        'vidhansabha',
        'mandal',
        'nagar',
        'matdan_kendra_name',
        'area_id'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'area_id');
    }
}
