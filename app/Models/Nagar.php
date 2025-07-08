<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nagar extends Model
{
    protected $table = 'nagar_master';
    protected $primaryKey = 'nagar_id';
    public $timestamps = false;
    protected $fillable = ['mandal_id', 'mandal_type', 'nagar_name', 'post_date'];

    public function mandal()
    {
        return $this->belongsTo(Mandal::class, 'mandal_id');
    }

    public function pollings()
    {
        return $this->hasMany(Polling::class, 'nagar_id');
    }
}
