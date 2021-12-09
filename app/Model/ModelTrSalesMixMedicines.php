<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesMixMedicines extends Model
{
    protected $table = 'tr_sales_mix_medicines';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public static function readDataById($id)
    {
        $data = DB::table('tr_sales_mix_medicines')
            ->select(
                'tr_sales_mix_medicines.id as id',
                'tr_sales_mix_medicines.name as medicineName',
                'tr_sales_mix_medicines.price as price',
                'tr_sales_mix_medicines.tuslah as tuslah',
                'tr_sales_mix_medicines.subtotal as subtotal'
            )
            ->join('tr_sales_mix', 'tr_sales_mix.tr_sales_transactions_id', '=', 'tr_sales_mix_medicines.tr_sales_mix_id')
            ->join('transactions', 'transactions.id', '=', 'tr_sales_mix_medicines.tr_sales_mix_id')
            ->where('tr_sales_mix_medicines.tr_sales_mix_id', $id)
            ->whereNull('transactions.deleted_at')
            ->get();

        return $data;
    }
    
    public function trSalesMixDetails()
    {
        $data = $this->hasMany(ModelTrSalesMixDetails::class, 'tr_sales_mix_medicines_id')->with('items');
        return $data;
    }
}
