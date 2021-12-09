<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrPurchaseDetails extends Model
{
    protected $table = 'tr_purchase_details';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public static function readDataById($id)
    {
        $data = DB::table('tr_purchase_details')
            ->select(
                'tr_purchase_details.id as id',
                'tr_purchase_details.price as price',
                'tr_purchase_details.qty as qty',
                'tr_purchase_details.discount as discount',
                'tr_purchase_details.subtotal as subtotal',
                'items.name as medicineName',
                'items.id as medicineId'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_purchase_details.tr_purchases_transactions_id')
            ->join('tr_purchases', 'tr_purchases.transactions_id', '=', 'transactions.id')
            ->join('items', 'items.id', '=', 'tr_purchase_details.medicines_items_id')
            ->where('tr_purchase_details.tr_purchases_transactions_id', $id)
            ->whereNull('transactions.deleted_at')
            ->get();

        return $data;
    }

    public function trPurchase()
    {
        $data = $this->belongsTo(ModelTrPurchases::class, 'tr_purchases_transactions_id')->with('supplier');
        return $data;
    }

    public function medicine()
    {
        $data = $this->belongsTo(ModelMedicines::class, 'medicines_items_id')->with('item', 'medicineDetails');
        return $data;
    }

    public function returnSum()
    {
        $data = $this->hasMany(ModelReturnTrPurchases::class, 'tr_purchase_details_id');
        return $data;
    }

    public function returnDetail()
    {
        $data = $this->hasMany(ModelReturnTrPurchases::class, 'tr_purchase_details_id')->with('return');
        return $data;
    }

    public function adjustmentsSum()
    {
        $data = $this->hasMany(ModelStockAdjustmentsTrPurchases::class, 'tr_purchase_details_id');
        return $data;
    }

    public function adjustmentsDetail()
    {
        $data = $this->hasMany(ModelStockAdjustmentsTrPurchases::class, 'tr_purchase_details_id')->with('stockAdjustments');
        return $data;
    }
}
