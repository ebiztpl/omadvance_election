<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationForm extends Model
{
    protected $table = 'registration_form';
    protected $primaryKey = 'registration_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'reference_id',
        'workarea',
        'ref_id',
        'position_id', 
    ];


    public function reference()
    {
        return $this->belongsTo(RegistrationForm::class, 'reference_id', 'registration_id');
    }

  
    public function referrals()
    {
        return $this->hasMany(self::class, 'reference_id', 'registration_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}