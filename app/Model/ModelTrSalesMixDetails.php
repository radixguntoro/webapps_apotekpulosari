<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesMixDetails extends Model
{
    protected $table = 'tr_sales_mix_details';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public static function readDataById($id)
    {
        $data = DB::table('tr_sales_mix_details')
            ->select(
                'tr_sales_mix_details.id as id',
                'tr_sales_mix_details.price as price',
                'tr_sales_mix_details.qty as qty',
                'tr_sales_mix_details.discount as discount',
                'tr_sales_mix_details.subtotal as subtotal',
                'items.name as medicineName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_mix_details.tr_sales_mix_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('items', 'items.id', '=', 'tr_sales_mix_details.medicines_items_id')
            ->where('tr_sales_mix_details.tr_sales_mix_id', $id)
            ->whereNull('transactions.deleted_at')
            ->get();

        return $data;
    }

    public function items()
    {
        $data = $this->belongsTo(ModelItems::class, 'medicines_items_id')->with('category');
        return $data;
    }
}
