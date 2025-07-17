<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mandal extends Model
{
    protected $table = 'mandal';
    protected $primaryKey = 'mandal_id';
    public $timestamps = false;

    protected $fillable = ['vidhansabha_id', 'mandal_name'];

    public function vidhansabha()
    {
        return $this->belongsTo(VidhansabhaLokSabha::class, 'vidhansabha_id');
    }

    public function nagars()
    {
        return $this->hasMany(Nagar::class, 'mandal_id');
    }

    public function pollings()
    {
        return $this->hasMany(Polling::class, 'mandal_id');
    }
}
