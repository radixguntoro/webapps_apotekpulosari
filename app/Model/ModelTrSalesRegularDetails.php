<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesRegularDetails extends Model
{
    protected $table = 'tr_sales_regular_details';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public static function readDataById($id)
    {
        $data = DB::table('tr_sales_regular_details')
            ->select(
                'tr_sales_regular_details.id as id',
                'tr_sales_regular_details.price as price',
                'tr_sales_regular_details.qty as qty',
                'tr_sales_regular_details.discount as discount',
                'tr_sales_regular_details.subtotal as subtotal',
                'items.name as medicineName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_regular_details.tr_sales_regular_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('items', 'items.id', '=', 'tr_sales_regular_details.medicines_items_id')
            ->where('tr_sales_regular_details.tr_sales_regular_id', $id)
            ->whereNull('transactions.deleted_at')
            ->get();

        return $data;
    }

    public function trSalesRegular()
    {
        $data = $this->belongsTo(ModelTrSalesRegular::class, 'tr_sales_regular_id')->with('trSales');
        return $data;
    }

    public function medicine()
    {
        $data = $this->belongsTo(ModelMedicines::class, 'medicines_items_id')->with('item');
        return $data;
    }
}
