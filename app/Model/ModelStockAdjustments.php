<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelStockAdjustments extends Model
{
    use SoftDeletes;
    protected $table = 'stock_adjustments';

    public function stockAdjustmentsSales()
    {
        $data = $this->hasOne(ModelStockAdjustmentsTrSales::class, 'stock_adjustments_id')->with('medicine');
        return $data;
    }

    public function stockAdjustmentsPurchase()
    {
        $data = $this->hasOne(ModelStockAdjustmentsTrPurchases::class, 'id');
        return $data;
    }

    public function cashier()
    {
        $data = $this->belongsTo(ModelPersons::class, 'users_persons_id');
        return $data;
    }
}
