<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesMix extends Model
{
    protected $table = 'tr_sales_mix';
    protected $primaryKey = 'tr_sales_transactions_id';
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($search, $row, $sort)
    {
        $order_by = empty($sort) ? "tr_sales_mix.tr_sales_transactions_id" : "tr_sales_mix.tr_sales_transactions_id";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('tr_sales_mix')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_sales_mix.payment as payment',
                'tr_sales_mix.balance as balance',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_mix.tr_sales_transactions_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
            ->where("tr_sales_mix.transactions_id", "LIKE", "%{$search}%")
            ->whereNull('transactions.deleted_at')
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }

    public static function readDataByPagination($row, $sort)
    {
        $order_by = empty($sort) ? "tr_sales_mix.tr_sales_transactions_id" : "tr_sales_mix.tr_sales_transactions_id";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('tr_sales_mix')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_sales_mix.payment as payment',
                'tr_sales_mix.balance as balance',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_mix.tr_sales_transactions_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
            ->whereNull('transactions.deleted_at')
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public function trSales()
    {
        $data = $this->hasOne(ModelTrSales::class, 'transactions_id')->with('transaction');
        return $data;
    }

    public function trSalesMixMedicines()
    {
        $data = $this->hasMany(ModelTrSalesMixMedicines::class, 'tr_sales_mix_id')->with('trSalesMixDetails');
        return $data;
    }
}
