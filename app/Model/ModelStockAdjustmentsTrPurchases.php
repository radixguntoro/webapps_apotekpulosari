<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelStockAdjustmentsTrPurchases extends Model
{
    protected $table = 'stock_adjustments_tr_purchases';
    protected $primaryKey = 'stock_adjustments_id';
    public $timestamps = false;

    public function stockAdjustments()
    {
        $data = $this->belongsTo(ModelStockAdjustments::class, 'stock_adjustments_id')->with('cashier');
        return $data;
    }

    public function trPurchase()
    {
        $data = $this->belongsTo(ModelTrPurchases::class, 'tr_purchases_transactions_id')->with('transaction');
        return $data;
    }

    public function trPurchaseDetails()
    {
        $data = $this->belongsTo(ModelTrPurchaseDetails::class, 'tr_purchase_details_id')->with('medicine');
        return $data;
    }
}