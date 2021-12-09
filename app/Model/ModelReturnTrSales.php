<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelReturnTrSales extends Model
{
    protected $table = 'returns_tr_sales';
    protected $primaryKey = 'returns_id';
    public $timestamps = false;

    public function return()
    {
        $data = $this->belongsTo(ModelReturns::class, 'returns_id')->with('cashier');
        return $data;
    }

    public function medicine()
    {
        $data = $this->belongsTo(ModelMedicines::class, 'medicines_items_id')->with('item', 'medicineDetails');
        return $data;
    }
}