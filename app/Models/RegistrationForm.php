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
        'mobile1',
        'otp',
        'otp_created_at',
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

    public function step2()
    {
        return $this->hasOne(Step2::class, 'registration_id', 'registration_id');
    }

    public function step3()
    {
        return $this->hasOne(Step3::class, 'registration_id', 'registration_id');
    }

    public function step4()
    {
        return $this->hasOne(Step4::class, 'registration_id', 'registration_id');
    }
}
