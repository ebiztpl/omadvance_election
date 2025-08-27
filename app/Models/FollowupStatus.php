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

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'complaint_id');
    }

    public function reply()
    {
        return $this->belongsTo(Reply::class, 'complaint_reply_id', 'complaint_reply_id');
    }

    public function followup_status_text()
    {
        return match ($this->followup_status) {
            0 => 'Not Done',
            1 => '<button class="btn btn-warning">फॉलोअप किया गया है, लेकिन कार्य अपूर्ण है</button>',
            2 => '<button class="btn btn-info">पूर्ण</button>',
            default => 'Unknown',
        };
    }

    public function followup_status_text_plain()
    {
        return match ($this->followup_status) {
            0 => 'Not Done',
            1 => 'अपूर्ण',
            2 => 'पूर्ण',
            default => 'Unknown',
        };
    }

    public function contact_status_text()
    {
        return $this->followup_contact_status ? 'संपर्क किया' : 'संपर्क नहीं किया';
    }


    public function isCompleted()
    {
        return $this->followup_status == 2;
    }

    public function isPending()
    {
        return $this->followup_status != 2;
    }

    public function isContactMade()
    {
        return (bool) $this->followup_contact_status;
    }

    public function scopeCompleted($query)
    {
        return $query->where('followup_status', 2);
    }

    public function scopePending($query)
    {
        return $query->where('followup_status', '!=', 2);
    }

    public function scopeContactMade($query)
    {
        return $query->where('followup_contact_status', 1);
    }

    public function scopeNoContact($query)
    {
        return $query->where('followup_contact_status', 0);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->whereDate('followup_date', '>=', now()->subDays($days));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('followup_date', now()->toDateString());
    }

    public function getFormattedDateAttribute()
    {
        return $this->followup_date ? $this->followup_date->format('d/m/Y H:i') : 'N/A';
    }

    public function getAgeInDaysAttribute()
    {
        return $this->followup_date ? $this->followup_date->diffInDays(now()) : 0;
    }
}
