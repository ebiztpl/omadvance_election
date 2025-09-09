<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintAttachment extends Model
{
    protected $table = 'complaints_multiple_attachments';

    protected $fillable = [
        'complaint_id',
        'file_name',
        'file_type',
        'created_at'
    ];

    public $timestamps = false; 
}
