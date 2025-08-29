<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Step2 extends Model
{
    protected $table = 'step2';
    protected $primaryKey = 'step2_id';
    public $timestamps = false;

    protected $fillable = [
        'registration_id',
        'division_id',
        'vidhansabha',
        'mandal',
        'nagar',
        'matdan_kendra_name',
        'matdan_kendra_no',
        'area_id'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'area_id');
    }

    public function polling()
    {
        return $this->belongsTo(Polling::class, 'matdan_kendra_name', 'gram_polling_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    // Step2.php
    public function districtRelation()
    {
        return $this->belongsTo(District::class, 'district', 'district_id');
    }

    public function vidhansabhaRelation()
    {
        return $this->belongsTo(VidhansabhaLokSabha::class, 'vidhansabha', 'vidhansabha_id');
    }

    public function loksabhaRelation()
    {
        return VidhansabhaLokSabha::where('loksabha', $this->loksabha)->first();
    }

    public function mandalRelation()
    {
        return $this->belongsTo(Mandal::class, 'mandal', 'mandal_id');
    }

    public function nagarRelation()
    {
        return $this->belongsTo(Nagar::class, 'nagar', 'nagar_id');
    }

    public function areaRelation()
    {
        return $this->belongsTo(Area::class, 'area_id', 'area_id');
    }
}
