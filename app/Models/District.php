<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'district_master';
    protected $primaryKey = 'district_id';
    public $timestamps = false;

    protected $fillable = ['division_id', 'district_name'];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function vidhansabhas()
    {
        return $this->hasMany(VidhansabhaLokSabha::class, 'district_id');
    }
}
