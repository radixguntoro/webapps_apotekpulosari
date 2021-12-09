<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrPurchases extends Model
{
    protected $table = 'tr_purchases';
    protected $primaryKey = 'transactions_id';
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
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(tr_purchases.date, "%d-%m-%Y") as date'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_purchases.invoice_number as invoiceNumber',
                'tr_purchases.ppn as ppn',
                'tr_purchases.status as status',
                'p_user.name as userName',
                'p_supplier.name as supplierName',
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = p_supplier.id
                ) as supplierPhone')
            )
            ->join('transactions', 'transactions.id', '=', 'tr_purchases.transactions_id')
            ->join('persons as p_user', 'p_user.id', '=', 'transactions.users_persons_id')
            ->join('persons as p_supplier', 'p_supplier.id', '=', 'tr_purchases.suppliers_persons_id')
            ->where("tr_purchases.transactions_id", "LIKE", "%{$search}%")
            ->whereNull('transactions.deleted_at')
            ->whereNotIn('tr_purchases.status', $filter)
            ->orWhere("tr_purchases.invoice_number", "LIKE", "%{$search}%")
            ->whereNull('transactions.deleted_at')
            ->whereNotIn('tr_purchases.status', $filter)
            ->orWhere("tr_purchases.date", "LIKE", "%{$search}%")
            ->whereNull('transactions.deleted_at')
            ->whereNotIn('tr_purchases.status', $filter)
            ->orWhere("p_supplier.name", "LIKE", "%{$search}%")
            ->whereNull('transactions.deleted_at')
            ->whereNotIn('tr_purchases.status', $filter)
            ->orderBy('tr_purchases.date', 'desc')
            ->orderBy('tr_purchases.invoice_number', 'desc')
            ->paginate(10);

        return $data;
    }

    public static function readDataByPagination($row, $sort, $filter)
    {
        $order_by = empty($sort) ? "tr_purchases.transactions_id" : "tr_purchases.transactions_id";
        $sort = empty($sort) ? "desc" : $sort;
        $filter = empty($filter) ? [] : $filter;

        $data = DB::table('tr_purchases')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(tr_purchases.date, "%d %b %Y") as date'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_purchases.invoice_number as invoiceNumber',
                'tr_purchases.ppn as ppn',
                'tr_purchases.status as status',
                'p_user.name as userName',
                'p_supplier.name as supplierName',
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = p_supplier.id
                ) as supplierPhone')
            )
            ->join('transactions', 'transactions.id', '=', 'tr_purchases.transactions_id')
            ->join('persons as p_user', 'p_user.id', '=', 'transactions.users_persons_id')
            ->join('persons as p_supplier', 'p_supplier.id', '=', 'tr_purchases.suppliers_persons_id')
            ->whereNull('transactions.deleted_at')
            ->whereNotIn('tr_purchases.status', $filter)
            ->orderBy('tr_purchases.date', 'desc')
            ->orderBy('tr_purchases.invoice_number', 'desc')
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
        $data = DB::table('tr_purchases')
            ->select(
                'transactions.id as id',
                DB::raw('DATE_FORMAT(transactions.created_at, "%d-%m-%Y") as createdAt'),
                DB::raw('DATE_FORMAT(tr_purchases.date, "%d-%m-%Y") as date'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%h:%i:%s") as timeAt'),
                'transactions.total as total',
                'transactions.discount as discount',
                'transactions.grand_total as grandTotal',
                'tr_purchases.invoice_number as invoiceNumber',
                'tr_purchases.ppn as ppn',
                'tr_purchases.status as status',
                'p_user.name as userName',
                'p_supplier.name as supplierName',
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = p_supplier.id
                ) as supplierPhone')
            )
            ->join('transactions', 'transactions.id', '=', 'tr_purchases.transactions_id')
            ->join('persons as p_user', 'p_user.id', '=', 'transactions.users_persons_id')
            ->join('persons as p_supplier', 'p_supplier.id', '=', 'tr_purchases.suppliers_persons_id')
            ->where('tr_purchases.transactions_id', $id)
            ->whereNull('transactions.deleted_at')
            ->first();

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
            CAST((
                SELECT 
                (
                    SUM(
                    (
                        (
                            (tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * 
                            (
                                (tr_purchase_details.qty + 
                                COALESCE
                                (
                                    (
                                        SELECT SUM(satp.qty) 
                                        FROM stock_adjustments_tr_purchases satp
                                        join transactions t on t.id = satp.tr_purchases_transactions_id
                                        WHERE satp.tr_purchase_details_id = tr_purchase_details.id
                                        and t.deleted_at is null
                                    ), 0
                                )) -
                                COALESCE
                                (
                                    (
                                        SELECT SUM(rtp.qty) 
                                        FROM returns_tr_purchases rtp 
                                        join tr_purchase_details tsnd on tsnd.id = rtp.tr_purchase_details_id
                                        join transactions t on t.id = rtp.tr_purchases_transactions_id
                                        WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                                        and t.deleted_at is null
                                    ), 0
                                )
                            )
                            )
                        )
                    )
                )
                FROM tr_purchase_details
                WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
            ) as DECIMAL(10,2)) as total
        ';
        
        return $query;
    }
    public static function queryGrandTotal()
    {
        $query = '
            CAST((
                SELECT SUM(
                    (
                        (
                            (tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * 
                            (
                                (tr_purchase_details.qty + 
                                COALESCE
                                (
                                    (
                                        SELECT SUM(satp.qty) 
                                        FROM stock_adjustments_tr_purchases satp
                                        join transactions t on t.id = satp.tr_purchases_transactions_id
                                        WHERE satp.tr_purchase_details_id = tr_purchase_details.id
                                        and t.deleted_at is null
                                    ), 0
                                )) -
                                COALESCE
                                (
                                    (
                                        SELECT SUM(rtp.qty) 
                                        FROM returns_tr_purchases rtp 
                                        join tr_purchase_details tsnd on tsnd.id = rtp.tr_purchase_details_id
                                        join transactions t on t.id = rtp.tr_purchases_transactions_id
                                        WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                                        and t.deleted_at is null
                                    ), 0
                                )
                            )
                            )
                        )
                    ) - t.discount
                FROM tr_purchase_details
                WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
            ) as DECIMAL(10,2)) + 
            CAST((
                SELECT (SUM(
                    (
                        (tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * 
                            (
                                (tr_purchase_details.qty + 
                                COALESCE
                                (
                                    (
                                        SELECT SUM(satp.qty) 
                                        FROM stock_adjustments_tr_purchases satp
                                        join transactions t on t.id = satp.tr_purchases_transactions_id
                                        WHERE satp.tr_purchase_details_id = tr_purchase_details.id
                                        and t.deleted_at is null
                                    ), 0
                                )) -
                                COALESCE
                                (
                                    (
                                        SELECT SUM(rtp.qty) 
                                        FROM returns_tr_purchases rtp 
                                        join tr_purchase_details tsnd on tsnd.id = rtp.tr_purchase_details_id
                                        join transactions t on t.id = rtp.tr_purchases_transactions_id
                                        WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                                        and t.deleted_at is null
                                    ), 0
                                )
                            )
                            )
                    ) - t.discount) * tr_purchases.ppn
                FROM tr_purchase_details
                WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
            ) as DECIMAL(10,2))
            as grand_total
        ';
        
        return $query;
    }

    public function transaction()
    {
        $data = $this->belongsTo(ModelTransactions::class, 'transactions_id')->with('cashier');
        return $data;
    }

    public function supplier()
    {
        $data = $this->belongsTo(ModelPersons::class, 'suppliers_persons_id')->with('phones');
        return $data;
    }

    public function details()
    {
        $data = $this->hasMany(ModelTrPurchaseDetails::class, 'tr_purchases_transactions_id')->select([
            'id',
            'price',
            DB::raw('tr_purchase_details.qty + COALESCE((
                SELECT SUM(satp.qty) 
                FROM stock_adjustments_tr_purchases satp
                join transactions t on t.id = satp.tr_purchases_transactions_id
                WHERE satp.tr_purchase_details_id = tr_purchase_details.id
                and t.deleted_at is null
            ), 0) - COALESCE((
                SELECT SUM(rtp.qty) 
                FROM returns_tr_purchases rtp 
                join tr_purchase_details tpd on tpd.id = rtp.tr_purchase_details_id
                join transactions t on t.id = rtp.tr_purchases_transactions_id
                WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                and t.deleted_at is null
            ), 0) as qty'),
            'qty_in_tablet',
            'discount',
            DB::raw('(tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * (tr_purchase_details.qty + COALESCE((
                SELECT SUM(satp.qty) 
                FROM stock_adjustments_tr_purchases satp
                join transactions t on t.id = satp.tr_purchases_transactions_id
                WHERE satp.tr_purchase_details_id = tr_purchase_details.id
                and t.deleted_at is null
            ), 0) - COALESCE((
                SELECT SUM(rtp.qty) 
                FROM returns_tr_purchases rtp 
                join tr_purchase_details tpd on tpd.id = rtp.tr_purchase_details_id
                join transactions t on t.id = rtp.tr_purchases_transactions_id
                WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                and t.deleted_at is null
            ), 0)) as subtotal'),
            'unit',
            'price_sell_old',
            'price_purchase_old',
            'medicines_items_id',
            'tr_purchases_transactions_id'
        ])
        ->with('medicine', 'returnDetail')
        ->withCount([
            'returnSum AS qty_return' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(qty), 0) as qtyReturn'));
            }
        ])
        ->withCount([
            'adjustmentsSum AS qty_adjustments' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(qty), 0) as qtyAdjustments'));
            }
        ]);
        return $data;
    }

    public function trPurchaseDetails()
    {
        $data = $this->hasMany(ModelTrPurchaseDetails::class, 'tr_purchases_transactions_id')->select([
            'id',
            'price',
            DB::raw('tr_purchase_details.qty + COALESCE((
                SELECT SUM(satp.qty) 
                FROM stock_adjustments_tr_purchases satp
                join transactions t on t.id = satp.tr_purchases_transactions_id
                WHERE satp.tr_purchase_details_id = tr_purchase_details.id
                and t.deleted_at is null
            ), 0) - COALESCE((
                SELECT SUM(rtp.qty) 
                FROM returns_tr_purchases rtp 
                join tr_purchase_details tpd on tpd.id = rtp.tr_purchase_details_id
                join transactions t on t.id = rtp.tr_purchases_transactions_id
                WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                and t.deleted_at is null
            ), 0) as qty'),
            'qty_in_tablet',
            'discount',
            DB::raw('(tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * (tr_purchase_details.qty + COALESCE((
                SELECT SUM(satp.qty) 
                FROM stock_adjustments_tr_purchases satp
                join transactions t on t.id = satp.tr_purchases_transactions_id
                WHERE satp.tr_purchase_details_id = tr_purchase_details.id
                and t.deleted_at is null
            ), 0) - COALESCE((
                SELECT SUM(rtp.qty) 
                FROM returns_tr_purchases rtp 
                join tr_purchase_details tpd on tpd.id = rtp.tr_purchase_details_id
                join transactions t on t.id = rtp.tr_purchases_transactions_id
                WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                and t.deleted_at is null
            ), 0)) as subtotal'),
            'unit',
            'price_sell_old',
            'price_purchase_old',
            'medicines_items_id',
            'tr_purchases_transactions_id'
        ])
        ->with('medicine', 'returnDetail')
        ->withCount([
            'returnSum AS qty_return' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(qty), 0) as qtyReturn'));
            }
        ])
        ->withCount([
            'adjustmentsSum AS qty_adjustments' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(qty), 0) as qtyAdjustments'));
            }
        ]);
        return $data;
    }
    public function returns()
    {
        $data = $this->hasMany(ModelReturnTrPurchases::class, 'tr_purchases_transactions_id');
        return $data;
    }
}
