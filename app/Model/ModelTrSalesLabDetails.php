<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesLabDetails extends Model
{
    protected $table = 'tr_sales_lab_details';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public function medicine()
    {
        $data = $this->belongsTo(ModelMedicines::class, 'medicines_items_id')->with('item');
        return $data;
    }
}
