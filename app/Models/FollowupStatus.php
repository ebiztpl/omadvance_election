<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowupStatus extends Model
{
    use HasFactory;

    protected $table = 'followup_status';

    protected $primaryKey = 'followup_id';

    public $timestamps = false;

    protected $casts = [
        'followup_date' => 'datetime'
    ];

    protected $fillable = [
        'complaint_reply_id',
        'followup_contact_status',
        'followup_contact_description',
        'followup_status',
        'followup_created_by',
        'followup_date',
    ];

    // public function reply()
    // {
    //     return $this->belongsTo(Reply::class, 'complaint_reply_id');
    // }

    public function followup_status_text()
    {
        return match ($this->followup_status) {
            0 => 'Not Done',
            1 => 'Done but Not Completed',
            2 => 'Completed',
            default => 'Unknown',
        };
    }
}
