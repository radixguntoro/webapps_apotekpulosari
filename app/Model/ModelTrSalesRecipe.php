<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesRecipe extends Model
{
    protected $table = 'tr_sales_recipe';
    protected $primaryKey = 'tr_sales_transactions_id';
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($search, $row, $sort)
    {
        $order_by = empty($sort) ? "tr_sales_recipe.tr_sales_transactions_id" : "tr_sales_recipe.tr_sales_transactions_id";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('tr_sales_recipe')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_sales_recipe.payment as payment',
                'tr_sales_recipe.balance as balance',
                'tr_sales_recipe.patient as patient',
                'tr_sales_recipe.date as date',
                'tr_sales_recipe.doctor as doctor',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_recipe.tr_sales_transactions_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
            ->where("tr_sales_recipe.transactions_id", "LIKE", "%{$search}%")
            ->whereNull('transactions.deleted_at')
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }

    public static function readDataByPagination($row, $sort)
    {
        $order_by = empty($sort) ? "tr_sales_recipe.tr_sales_transactions_id" : "tr_sales_recipe.tr_sales_transactions_id";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('tr_sales_recipe')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_sales_recipe.payment as payment',
                'tr_sales_recipe.balance as balance',
                'tr_sales_recipe.patient as patient',
                'tr_sales_recipe.date as date',
                'tr_sales_recipe.doctor as doctor',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_recipe.tr_sales_transactions_id')
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
        $data = DB::table('tr_sales_recipe')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_sales_recipe.payment as payment',
                'tr_sales_recipe.balance as balance',
                'tr_sales_recipe.patient as patient',
                'tr_sales_recipe.date as date',
                'tr_sales_recipe.doctor as doctor',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_recipe.tr_sales_transactions_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
            ->where('tr_sales_recipe.tr_sales_transactions_id', $id)
            ->whereNull('transactions.deleted_at')
            ->first();

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

    public function trSalesRecipeMedicines()
    {
        $data = $this->hasMany(ModelTrSalesRecipeMedicines::class, 'tr_sales_recipe_id')->with('trSalesRecipeDetails');
        return $data;
    }

    public function closingCashierDetails()
    {
        $data = $this->hasOne(ModelClosingCashierDetails::class, 'tr_sales_id');
        return $data;
    }
}
