<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesRegular extends Model
{
    protected $table = 'tr_sales_regular';
    protected $primaryKey = 'tr_sales_transactions_id';
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($search, $row, $sort)
    {
        $order_by = empty($sort) ? "tr_sales_regular.tr_sales_transactions_id" : "tr_sales_regular.tr_sales_transactions_id";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('tr_sales_regular')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_sales_regular.payment as payment',
                'tr_sales_regular.balance as balance',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_regular.tr_sales_transactions_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
            ->where("tr_sales_regular.transactions_id", "LIKE", "%{$search}%")
            ->whereNull('transactions.deleted_at')
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }

    public static function readDataByPagination($row, $sort)
    {
        $order_by = empty($sort) ? "tr_sales_regular.tr_sales_transactions_id" : "tr_sales_regular.tr_sales_transactions_id";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('tr_sales_regular')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_sales_regular.payment as payment',
                'tr_sales_regular.balance as balance',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_regular.tr_sales_transactions_id')
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
    public static function readDataById($id)
    {
        $data = DB::table('tr_sales_regular')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'transactions.codes_id as codesId',
                'tr_sales_regular.payment as payment',
                'tr_sales_regular.balance as balance',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_regular.tr_sales_transactions_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
            ->where('tr_sales_regular.tr_sales_transactions_id', $id)
            ->whereNull('transactions.deleted_at')
            ->first();

        return $data;
    }
    
    public function trSales()
    {
        $data = $this->belongsTo(ModelTrSales::class, 'tr_sales_transactions_id')->with('transaction');
        return $data;
    }

    public function trSalesRegularDetails()
    {
        $data = $this->hasMany(ModelTrSalesRegularDetails::class, 'tr_sales_regular_id')->with('medicine');
        return $data;
    }
    
    public function closingCashierDetails()
    {
        $data = $this->hasOne(ModelClosingCashierDetails::class, 'tr_sales_id');
        return $data;
    }
}
