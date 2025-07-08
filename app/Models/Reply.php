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
        'cb_photo',
        'ca_photo',
        'reply_from',
        'reply_date',
    ];

    protected $casts = [
        'reply_date' => 'datetime',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
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
}