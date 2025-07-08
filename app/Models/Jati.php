<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jati extends Model
{
    protected $table = 'jati_master';

    protected $primaryKey = 'jati_id'; 

    public $timestamps = false; 

    protected $fillable = ['jati_name'];
}
