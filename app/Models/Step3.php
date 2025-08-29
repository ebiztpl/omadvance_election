<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Step3 extends Model
{
    protected $table = 'step3';
    protected $primaryKey = 'step3_id';
    public $timestamps = false;

    protected $fillable = ['registration_id', 'intrest'];

    public function registration()
    {
        return $this->belongsTo(RegistrationForm::class, 'registration_id');
    }
}
