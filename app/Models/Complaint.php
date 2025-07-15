<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reply;

class Complaint extends Model
{
    protected $table = 'complaint';

    protected $primaryKey = 'complaint_id';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'mobile_number',
        'complaint_number',
        'complaint_type',
        'issue_title',
        'issue_description',
        'address',
        'post_date',
        'gender',
        'religion',
        'caste',
        'jati',
        'education',
        'business',
        'position',
        'polling_no',
        'division_id',
        'district_id',
        'vidhansabha_id',
        'complaint_department',
        'mandal_id',
        'gram_id',
        'area_id',
        'polling_id',
        'voter_id',
        'issue_attachment',
        'news_time',
        'complaint_status',
        'posted_date',
        'news_date',
        'program_date',
        'complaint_created_by',
        'type'
    ];


    protected $attributes = [
    'complaint_status' => 'Opened',
    ];


    public function replies()
    {
        return $this->hasMany(Reply::class, 'complaint_id');
    }

    public function statusText()
    {
        $statusLabels = [
            1 => '<button class="btn btn-success">Opened</button>',
            2 => '<button class="btn btn-warning">Processing</button>',
            3 => '<button class="btn btn-danger">On Hold</button>',
            4 => '<button class="btn btn-success">Closed</button>',
            5 => '<button class="btn btn-danger">Cancel</button>',
        ];

        return $statusLabels[$this->complaint_status] ?? '<button class="btn btn-primary">Opened</button>';
    }
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'division_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }

    public function vidhansabha()
    {
        return $this->belongsTo(VidhansabhaLokSabha::class, 'vidhansabha_id', 'vidhansabha_id');
    }

    public function mandal()
    {
        return $this->belongsTo(Mandal::class, 'mandal_id', 'mandal_id');
    }

    public function gram()
    {
        return $this->belongsTo(Nagar::class, 'gram_id', 'nagar_id');
    }

    public function polling()
    {
        return $this->belongsTo(Polling::class, 'polling_id', 'gram_polling_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'area_id');
    }

    public function registration()
    {
        return $this->hasOne(RegistrationForm::class, 'mobile1', 'mobile_number');
    }
}
