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
        'complaint_id',
        'followup_contact_status',
        'followup_contact_description',
        'followup_status',
        'followup_created_by',
        'followup_date',
    ];

    public function createdByAdmin()
    {
        return $this->belongsTo(User::class, 'followup_created_by');
    }

    public function followup_status_text()
    {
        return match ($this->followup_status) {
            0 => 'Not Done',
            1 => 'फॉलोअप किया गया है, लेकिन कार्य अपूर्ण है',
            2 => 'पूर्ण',
            default => 'Unknown',
        };
    }
}
