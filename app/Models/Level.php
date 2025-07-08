<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $table = 'level_master';
    protected $primaryKey = 'level_id';
    protected $fillable = ['level_name', 'ref_level_id', 'post_date'];
    public $timestamps = false;

    public function parent()
    {
        return $this->belongsTo(self::class, 'ref_level_id');
    }
}
