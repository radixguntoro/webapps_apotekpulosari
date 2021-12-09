<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelLogPurchaseDetails extends Model
{
    protected $table = 'log_purchase_details';
    public $timestamps = false;
    
    public function medicine()
    {
        $data = $this->belongsTo(ModelMedicines::class, 'medicines_items_id')->with('item');
        return $data;
    }
}
