<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelMedicineDetails extends Model
{
    protected $table = 'medicine_details';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
