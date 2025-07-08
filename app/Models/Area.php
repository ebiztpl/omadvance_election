<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'area_master';
    protected $primaryKey = 'area_id';
    public $timestamps = false;

    protected $fillable = ['polling_id', 'area_name', 'post_date'];

    public function polling()
    {
        return $this->belongsTo(Polling::class, 'polling_id', 'gram_polling_id');
    }
}
