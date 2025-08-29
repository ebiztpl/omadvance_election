<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Step4 extends Model
{
    protected $table = 'step4';
    protected $primaryKey = 'step4_id';
    public $timestamps = false;

    protected $fillable = ['registration_id', 'party_name', 'present_post', 'reason_join'];
}
