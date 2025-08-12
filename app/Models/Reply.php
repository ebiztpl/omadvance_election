<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reply extends Model
{
    protected $table = 'complaint_reply';
    protected $primaryKey = 'complaint_reply_id';

    public $timestamps = false;

    protected $fillable = [
        'complaint_id',
        'complaint_reply',
        'c_video',
        'selected_reply',
        'cb_photo',
        'ca_photo',
        'reply_from',
        'reply_date',
        'complaint_status',
        'forwarded_to',
        'review_date',
        'importance',
        'criticality',
    ];

    protected $casts = [
        'reply_date' => 'datetime',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }

    public function statusTextPlain(): string
    {
        return match ($this->complaint_status) {
            1 => 'शिकायत दर्ज',
            2 => 'प्रक्रिया में',
            3 => 'स्थगित',
            4 => '<button class="btn btn-success">पूर्ण</button>',
            5 => '<button class="btn btn-danger">रद्द</button>',
            default => 'शिकायत दर्ज',
        };
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ComplaintsDataPhoto::class, 'complaint_reply_id');
    }

    public function addPhotos(array $files, string $type): void
    {
        foreach ($files as $file) {
            $filename = $type . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('upload_complaint', $filename, 'public');

            $this->photos()->create([
                'complaint_id' => $this->complaint_id,
                'image' => $path,
                'type' => $type,
                'post_date' => now(),
            ]);
        }
    }


    public function predefinedReply()
    {
        return $this->belongsTo(ComplaintReply::class, 'selected_reply', 'reply_id');
    }

    public function forwardedToManager()
    {
        return $this->belongsTo(\App\Models\User::class, 'forwarded_to', 'admin_id');
    }
}