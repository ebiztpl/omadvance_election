<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintsDataPhoto extends Model
{
    protected $table = 'complaints_data_photo';
    public $timestamps = false;

    protected $fillable = [
        'complaint_id',
        'complaint_reply_id',
        'image',
        'type',
        'post_date',
    ];

    public function reply()
    {
        return $this->belongsTo(Reply::class, 'complaint_reply_id');
    }
}
