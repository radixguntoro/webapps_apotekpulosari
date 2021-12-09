<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesNetto extends Model
{
    protected $table = 'tr_sales_netto';
    protected $primaryKey = 'tr_sales_transactions_id';
    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($search, $row, $sort)
    {
        $order_by = empty($sort) ? "tr_sales_netto.tr_sales_transactions_id" : "tr_sales_netto.tr_sales_transactions_id";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('tr_sales_netto')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_sales_netto.payment as payment',
                'tr_sales_netto.balance as balance',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_netto.tr_sales_transactions_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
            ->where("tr_sales_netto.transactions_id", "LIKE", "%{$search}%")
            ->whereNull('transactions.deleted_at')
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }

    public static function readDataByPagination($row, $sort)
    {
        $order_by = empty($sort) ? "tr_sales_netto.tr_sales_transactions_id" : "tr_sales_netto.tr_sales_transactions_id";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('tr_sales_netto')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%d %b %Y %h:%i:%s") as dateTime'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_sales_netto.payment as payment',
                'tr_sales_netto.balance as balance',
                'persons.name as userName'
            )
            ->join('transactions', 'transactions.id', '=', 'tr_sales_netto.tr_sales_transactions_id')
            ->join('tr_sales', 'tr_sales.transactions_id', '=', 'transactions.id')
            ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
            ->whereNull('transactions.deleted_at')
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Custom RAW Query
    |--------------------------------------------------------------------------
    */
    public static function queryTotal()
    {
        $query = '
            CAST(
                    (
                        SELECT SUM(
                            (
                                (tr_sales_netto_details.price - (tr_sales_netto_details.price * tr_sales_netto_details.discount)) * (
                                    tr_sales_netto_details.qty + 
                                    COALESCE((
                                        SELECT SUM(satn.qty) 
                                        FROM stock_adjustments_tr_sales_netto satn
                                        join transactions t on t.id = satn.tr_sales_transactions_id
                                        WHERE satn.tr_sales_details_id = tr_sales_netto_details.id
                                        and t.deleted_at is null
                                    ), 0) - 
                                    COALESCE((
                                        SELECT SUM(rtsn.qty) 
                                        FROM returns_tr_sales_netto rtsn 
                                        join tr_sales_netto_details tsnd on tsnd.id = rtsn.tr_sales_details_id
                                        join transactions t on t.id = rtsn.tr_sales_transactions_id
                                        WHERE rtsn.tr_sales_details_id = tr_sales_netto_details.id
                                        and t.deleted_at is null
                                    ), 0)
                                )
                            )
                        ) 
                        FROM tr_sales_netto_details
                        WHERE tr_sales_netto_details.tr_sales_netto_id = tr_sales_netto.tr_sales_transactions_id
                ) as DECIMAL(10,2)
            ) as total
        ';
        
        return $query;
    }
    public static function queryGrandTotal()
    {
        $query = '
            CAST(
                    (
                        SELECT SUM(
                            (
                                (tr_sales_netto_details.price - (tr_sales_netto_details.price * tr_sales_netto_details.discount)) * (
                                    tr_sales_netto_details.qty + 
                                    COALESCE((
                                        SELECT SUM(satn.qty) 
                                        FROM stock_adjustments_tr_sales_netto satn
                                        join transactions t on t.id = satn.tr_sales_transactions_id
                                        WHERE satn.tr_sales_details_id = tr_sales_netto_details.id
                                        and t.deleted_at is null
                                    ), 0) - 
                                    COALESCE((
                                        SELECT SUM(rtsn.qty) 
                                        FROM returns_tr_sales_netto rtsn 
                                        join tr_sales_netto_details tsnd on tsnd.id = rtsn.tr_sales_details_id
                                        join transactions t on t.id = rtsn.tr_sales_transactions_id
                                        WHERE rtsn.tr_sales_details_id = tr_sales_netto_details.id
                                        and t.deleted_at is null
                                    ), 0)
                                ) + 
                                (
                                    (tr_sales_netto_details.price - (tr_sales_netto_details.price * tr_sales_netto_details.discount)) * (
                                        tr_sales_netto_details.qty + 
                                        COALESCE((
                                            SELECT SUM(satn.qty) 
                                            FROM stock_adjustments_tr_sales_netto satn
                                            join transactions t on t.id = satn.tr_sales_transactions_id
                                            WHERE satn.tr_sales_details_id = tr_sales_netto_details.id
                                            and t.deleted_at is null
                                        ), 0) - 
                                        COALESCE((
                                            SELECT SUM(rtsn.qty) 
                                            FROM returns_tr_sales_netto rtsn 
                                            join tr_sales_netto_details tsnd on tsnd.id = rtsn.tr_sales_details_id
                                            join transactions t on t.id = rtsn.tr_sales_transactions_id
                                            WHERE rtsn.tr_sales_details_id = tr_sales_netto_details.id
                                            and t.deleted_at is null
                                        ), 0)
                                    ) * tr_sales_netto_details.ppn
                                )
                            )
                        ) 
                        FROM tr_sales_netto_details
                        WHERE tr_sales_netto_details.tr_sales_netto_id = tr_sales_netto.tr_sales_transactions_id
                ) as DECIMAL(10,2)
            ) as grand_total
        ';
        
        return $query;
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
    public function trSalesNettoDetails()
    {
        $data = $this->hasMany(ModelTrSalesNettoDetails::class, 'tr_sales_netto_id')
        ->select(
            'id',
            'price',
            DB::raw('tr_sales_netto_details.qty + COALESCE((
                SELECT SUM(satn.qty) 
                FROM stock_adjustments_tr_sales_netto satn
                join transactions t on t.id = satn.tr_sales_transactions_id
                WHERE satn.tr_sales_details_id = tr_sales_netto_details.id
                and t.deleted_at is null
            ), 0) - COALESCE((
                SELECT SUM(rtsn.qty) 
                FROM returns_tr_sales_netto rtsn 
                join tr_sales_netto_details tsnd on tsnd.id = rtsn.tr_sales_details_id
                join transactions t on t.id = rtsn.tr_sales_transactions_id
                WHERE rtsn.tr_sales_details_id = tr_sales_netto_details.id
                and t.deleted_at is null
            ), 0) as qty'),
            'tr_sales_netto_details.qty as qty_input',
            'qty_in_tablet',
            'discount',
            'ppn',
            DB::raw('((tr_sales_netto_details.price - (tr_sales_netto_details.price * tr_sales_netto_details.discount)) * (tr_sales_netto_details.qty + COALESCE((
                SELECT SUM(satn.qty) 
                FROM stock_adjustments_tr_sales_netto satn
                join transactions t on t.id = satn.tr_sales_transactions_id
                WHERE satn.tr_sales_details_id = tr_sales_netto_details.id
                and t.deleted_at is null
            ), 0) - COALESCE((
                SELECT SUM(rtsn.qty) 
                FROM returns_tr_sales_netto rtsn 
                join tr_sales_netto_details tsnd on tsnd.id = rtsn.tr_sales_details_id
                join transactions t on t.id = rtsn.tr_sales_transactions_id
                WHERE rtsn.tr_sales_details_id = tr_sales_netto_details.id
                and t.deleted_at is null
            ), 0)) + ((tr_sales_netto_details.price - (tr_sales_netto_details.price * tr_sales_netto_details.discount)) * (tr_sales_netto_details.qty + COALESCE((
                SELECT SUM(satn.qty) 
                FROM stock_adjustments_tr_sales_netto satn
                join transactions t on t.id = satn.tr_sales_transactions_id
                WHERE satn.tr_sales_details_id = tr_sales_netto_details.id
                and t.deleted_at is null
            ), 0) - COALESCE((
                SELECT SUM(rtsn.qty) 
                FROM returns_tr_sales_netto rtsn 
                join tr_sales_netto_details tsnd on tsnd.id = rtsn.tr_sales_details_id
                join transactions t on t.id = rtsn.tr_sales_transactions_id
                WHERE rtsn.tr_sales_details_id = tr_sales_netto_details.id
                and t.deleted_at is null
            ), 0)) * tr_sales_netto_details.ppn)) as subtotal'),
            'unit',
            'medicines_items_id',
            'tr_sales_netto_id'
        )        
        ->with('medicine', 'returnDetail')
        ->withCount([
            'returnSum AS qty_return' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(qty), 0) as qtyreturn'));
            }
        ])
        ->withCount([
            'adjustmentsSum AS qty_adjustments' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(qty), 0) as qtyAdjustments'));
            }
        ]);
        return $data;
    }
    public function payment()
    {
        $data = $this->hasOne(ModelPaymentsTrSalesNetto::class, 'tr_sales_netto_transactions_id');
        return $data;
    }
    public function customer()
    {
        $data = $this->belongsTo(ModelPersons::class, 'customers_persons_id');
        return $data;
    }
    public function closingCashierDetails()
    {
        $data = $this->hasOne(ModelClosingCashierDetails::class, 'tr_sales_id');
        return $data;
    }
    public function returns()
    {
        $data = $this->hasMany(ModelReturnTrSalesNetto::class, 'tr_sales_details_id');
        return $data;
    }
}
