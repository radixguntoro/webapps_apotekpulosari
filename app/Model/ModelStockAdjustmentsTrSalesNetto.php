<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelStockAdjustmentsTrSalesNetto extends Model
{
    protected $table = 'stock_adjustments_tr_sales_netto';
    protected $primaryKey = 'stock_adjustments_id';
    public $timestamps = false;

    public function stockAdjustments()
    {
        $data = $this->belongsTo(ModelStockAdjustments::class, 'stock_adjustments_id')->with('cashier');
        return $data;
    }

    public function trSalesNetto()
    {
        $data = $this->belongsTo(ModelTrSalesNetto::class, 'tr_sales_transactions_id')->with('trSales');
        return $data;
    }

    public function trSalesNettoDetails()
    {
        $data = $this->belongsTo(ModelTrSalesNettoDetails::class, 'tr_sales_details_id')->with('medicine');
        return $data;
    }
}