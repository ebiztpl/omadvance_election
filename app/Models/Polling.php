<?php

// app/Models/Polling.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Polling extends Model
{
    protected $table = 'gram_polling'; 

    protected $primaryKey = 'gram_polling_id'; 

    protected $fillable = ['polling_name', 'polling_no', 'mandal_id', 'nagar_id'];
    public $timestamps = false;

    public function mandal()
    {
        return $this->belongsTo(Mandal::class, 'mandal_id');
    }

    public function nagar()
    {
        return $this->belongsTo(Nagar::class, 'nagar_id');
    }

    public function areas()
    {
        return $this->hasMany(Area::class, 'polling_id', 'gram_polling_id');
    }

    public function area()
    {
        return $this->hasOne(Area::class, 'polling_id', 'gram_polling_id');
    }
}
