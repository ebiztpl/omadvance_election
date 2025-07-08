<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VidhansabhaLokSabha extends Model
{
    protected $table = 'vidhansabha_loksabha';
    protected $primaryKey = 'vidhansabha_id';
    public $timestamps = false;
    protected $fillable = ['district_id', 'vidhansabha', 'loksabha'];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function mandals()
    {
        return $this->hasMany(Mandal::class, 'vidhansabha_id');
    }
}
