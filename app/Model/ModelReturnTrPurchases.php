<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelReturnTrPurchases extends Model
{
    protected $table = 'returns_tr_purchases';
    protected $primaryKey = 'returns_id';
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($search, $row, $sort, $filter)
    {
        $order_by = empty($sort) ? "tr_purchases.transactions_id" : "tr_purchases.transactions_id";
        $sort = empty($sort) ? "desc" : $sort;
        $filter = empty($filter) ? [] : $filter;

        $data = DB::table('tr_purchases')
            ->select(
                'returns.id as id',
                DB::raw('DATE_FORMAT(returns.created_at, "%d %b %Y") as date'),
                DB::raw('DATE_FORMAT(returns.created_at, "%h:%i:%s") as timeAt'),
                'returns.total as total',
                'returns.discount as discount',
                'returns.grand_total as grandTotal',
                'returns.ppn as ppn',
                'tr_purchases.invoice_number as invoiceNumber',
                'tr_purchases.status as status',
                'p_user.name as userName',
                'p_supplier.name as supplierName',
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = p_supplier.id
                ) as supplierPhone')
            )
            ->join('returns_tr_purchases', 'returns_tr_purchases.returns_id', '=', 'returns.id')
            ->join('tr_purchases', 'tr_purchases.transactions_id', '=', 'returns_tr_purchases.tr_purchases_transactions_id')
            ->join('transactions', 'transactions.id', '=', 'tr_purchases.transactions_id')
            ->join('persons as p_user', 'p_user.id', '=', 'transactions.users_persons_id')
            ->join('persons as p_supplier', 'p_supplier.id', '=', 'tr_purchases.suppliers_persons_id')
            ->where("tr_purchases.transactions_id", "LIKE", "%{$search}%")
            ->orWhere("tr_purchases.invoice_number", "LIKE", "%{$search}%")
            ->orWhere("tr_purchases.date", "LIKE", "%{$search}%")
            ->orWhere("p_supplier.name", "LIKE", "%{$search}%")
            ->whereNull('transactions.deleted_at')
            ->whereNotIn('tr_purchases.status', $filter)
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }

    public static function readDataByPagination($row, $sort, $filter)
    {
        $order_by = empty($sort) ? "tr_purchases.transactions_id" : "tr_purchases.transactions_id";
        $sort = empty($sort) ? "desc" : $sort;
        $filter = empty($filter) ? [] : $filter;

        $data = DB::table('returns')
            ->select(
                'returns.id as id',
                DB::raw('DATE_FORMAT(returns.created_at, "%d %b %Y") as date'),
                DB::raw('DATE_FORMAT(returns.created_at, "%h:%i:%s") as timeAt'),
                'returns.total as total',
                'returns.discount as discount',
                'returns.grand_total as grandTotal',
                'returns.ppn as ppn',
                'tr_purchases.invoice_number as invoiceNumber',
                'tr_purchases.status as status',
                'p_user.name as userName',
                'p_supplier.name as supplierName',
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = p_supplier.id
                ) as supplierPhone')
            )
            ->join('returns_tr_purchases', 'returns_tr_purchases.returns_id', '=', 'returns.id')
            ->join('tr_purchases', 'tr_purchases.transactions_id', '=', 'returns_tr_purchases.tr_purchases_transactions_id')
            ->join('transactions', 'transactions.id', '=', 'tr_purchases.transactions_id')
            ->join('persons as p_user', 'p_user.id', '=', 'transactions.users_persons_id')
            ->join('persons as p_supplier', 'p_supplier.id', '=', 'tr_purchases.suppliers_persons_id')
            ->whereNull('transactions.deleted_at')
            ->whereNotIn('tr_purchases.status', $filter)
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
        $data = DB::table('returns')
            ->select(
                'returns.id as id',
                'tr_purchases.transactions_id as trPurchaseId',
                DB::raw('DATE_FORMAT(returns.created_at, "%d %b %Y") as date'),
                DB::raw('DATE_FORMAT(returns.created_at, "%h:%i:%s") as timeAt'),
                'returns.total as total',
                'returns.discount as discount',
                'returns.grand_total as grandTotal',
                'returns.ppn as ppn',
                'tr_purchases.invoice_number as invoiceNumber',
                'tr_purchases.status as status',
                'p_user.name as userName',
                'p_supplier.name as supplierName',
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = p_supplier.id
                ) as supplierPhone')
            )
            ->join('returns_tr_purchases', 'returns_tr_purchases.returns_id', '=', 'returns.id')
            ->join('tr_purchases', 'tr_purchases.transactions_id', '=', 'returns_tr_purchases.tr_purchases_transactions_id')
            ->join('transactions', 'transactions.id', '=', 'tr_purchases.transactions_id')
            ->join('persons as p_user', 'p_user.id', '=', 'transactions.users_persons_id')
            ->join('persons as p_supplier', 'p_supplier.id', '=', 'tr_purchases.suppliers_persons_id')
            ->where('returns.id', $id)
            ->whereNull('returns.deleted_at')
            ->first();

        return $data;
    }

    public function return()
    {
        $data = $this->belongsTo(ModelReturns::class, 'returns_id')->with('cashier');
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