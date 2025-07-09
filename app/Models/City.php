<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'city';
    protected $primaryKey = 'city_id';
    public $timestamps = false;

    protected $fillable = ['state_id', 'name'];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
