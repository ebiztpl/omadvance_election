<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignPosition extends Model
{
    protected $table = 'assign_position';

    protected $primaryKey = 'assign_position_id';

    protected $fillable = [
        'member_id',
        'level_name',
        'refrence_id',
        'position_id',
        'from_date',
        'to_date',
        'status',
        'post_date',
    ];

    public $timestamps = false;

    public function member()
    {
        return $this->belongsTo(RegistrationForm::class, 'member_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function addressInfo()
    {
        return $this->hasOne(Step3::class, 'registration_id', 'member_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'refrence_id', 'district_id');
    }
}