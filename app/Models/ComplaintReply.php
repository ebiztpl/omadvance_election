<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintReply extends Model
{
    protected $table = 'complaint_reply_master';
    protected $primaryKey = 'reply_id';
    public $timestamps = false;

    protected $fillable = ['reply'];
}
