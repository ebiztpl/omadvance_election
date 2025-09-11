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
        'father_name',
        'reference_name',
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
        'complaint_designation',
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
        'complaint_created_by_member',
        'type',
        'jati_id'
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
            1 => '<button class="btn btn-success">शिकायत दर्ज</button>',
            2 => '<button class="btn btn-warning">प्रक्रिया में</button>',
            3 => '<button class="btn btn-danger">स्थगित</button>',
            4 => '<button class="btn btn-success">पूर्ण</button>',
            5 => '<button class="btn btn-danger">रद्द</button>',
            11 => '<button class="btn btn-success">सूचना प्राप्त</button>',
            12 => '<button class="btn btn-success">फॉरवर्ड किया</button>',
            13 => '<button class="btn btn-success">सम्मिलित हुए</button>',
            14 => '<button class="btn btn-warning">सम्मिलित नहीं हुए</button>',
            15 => '<button class="btn btn-info">फोन पर संपर्क किया</button>',
            16 => '<button class="btn btn-info">ईमेल पर संपर्क किया</button>',
            17 => '<button class="btn btn-info">व्हाट्सएप पर संपर्क किया</button>',
            18 => '<button class="btn btn-danger">रद्द</button>',
        ];

        return $statusLabels[$this->complaint_status] ?? '<button class="btn btn-primary">शिकायत दर्ज</button>';
    }

    public function statusTextPlain()
    {
        $statusLabels = [
            1 => 'शिकायत दर्ज',
            2 => 'प्रक्रिया में',
            3 => 'स्थगित',
            4 => '<button class="btn btn-success">पूर्ण</button>',
            5 => '<button class="btn btn-danger">रद्द</button>',
            11 => 'सूचना प्राप्त',
            12 => 'फॉरवर्ड किया',
            13 => '<button class="btn btn-success">सम्मिलित हुए</button>',
            14 => '<button class="btn btn-warning">सम्मिलित नहीं हुए</button>',
            15 => '<button class="btn btn-info">फोन पर संपर्क किया</button>',
            16 => '<button class="btn btn-info">ईमेल पर संपर्क किया</button>',
            17 => '<button class="btn btn-info">व्हाट्सएप पर संपर्क किया</button>',
            18 => '<button class="btn btn-danger">रद्द</button>'

        ];

        return $statusLabels[$this->complaint_status] ?? 'शिकायत दर्ज';
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

    public function registrationDetails()
    {
        return $this->belongsTo(RegistrationForm::class, 'complaint_created_by_member', 'registration_id');
    }
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'complaint_created_by', 'admin_id');
    }


    public function latestNonDefaultReply()
    {
        return $this->hasOne(Reply::class, 'complaint_id')
            ->where('complaint_reply', '!=', 'शिकायत दर्ज की गई है।')
            ->latest('reply_date');
    }

    public function latestReply()
    {
        return $this->hasOne(Reply::class, 'complaint_id')
            ->latestOfMany('reply_date'); 
    }

    public function latestReplyWithoutFollowup()
    {
        return $this->hasOne(Reply::class, 'complaint_id')
            ->latest('reply_date')
            ->whereDoesntHave('followups')
            ->where('complaint_reply', '!=', 'शिकायत दर्ज की गई है।')
            ->latestOfMany('reply_date');
    }

    public function followups()
    {
        return $this->hasMany(FollowupStatus::class, 'complaint_id', 'complaint_id');
    }

    public function latestFollowup()
    {
        return $this->hasOne(FollowupStatus::class, 'complaint_id')
            ->latestOfMany('followup_date');
    }

    public function allFollowups()
    {
        return $this->hasMany(FollowupStatus::class, 'complaint_id')
            ->orderBy('followup_date', 'desc');
    }


    public function jati()
    {
        return $this->belongsTo(Jati::class, 'jati_id', 'jati_id');
    }

    public function attachments()
    {
        return $this->hasMany(ComplaintAttachment::class, 'complaint_id', 'complaint_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'complaint_department', 'department_name');
    }
}
