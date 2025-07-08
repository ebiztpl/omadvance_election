<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JatiwiseVoter extends Model
{
    protected $table = 'jatiwise_voter';
    protected $primaryKey = 'jatiwise_voter_id';
    public $timestamps = false;

    protected $fillable = [
        'vidhansabha_id',
        'mandal_id',
        'gram_id',
        'polling_id',
        'jati_id',
        'voter_total',
        'post_date'
    ];
}
