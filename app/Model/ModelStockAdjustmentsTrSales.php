<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelStockAdjustmentsTrSales extends Model
{
    protected $table = 'stock_adjustments_tr_sales';
    protected $primaryKey = 'stock_adjustments_id';
    public $timestamps = false;

    public function stockAdjustments()
    {
        $data = $this->belongsTo(ModelStockAdjustments::class, 'stock_adjustments_id')->with('cashier');
        return $data;
    }

    public function medicine()
    {
        $data = $this->belongsTo(ModelMedicines::class, 'medicines_items_id')->with('item', 'medicineDetails');
        return $data;
    }
}