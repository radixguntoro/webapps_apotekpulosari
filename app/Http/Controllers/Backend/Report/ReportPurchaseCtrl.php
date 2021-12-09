<?php

namespace App\Http\Controllers\Backend\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Libraries\ArrayManage;
use App\Http\Controllers\Controller;
use App\Model\ModelTransactions;
use App\Model\ModelTrPurchases;
use App\Model\ModelReturns;
use App\Model\ModelSuppliers;
use DB;

class ReportPurchaseCtrl extends Controller
{
    public function index()
    {
        return view('layouts.adminLayout');
    }

    public function readDataList(Request $request)
    {
        $state = $request->get('state');
        $date_start = $request->get('date_start');
        $date_end = $request->get('date_end');
        $supplier = $request->get('supplier');
        $status = explode(',', trim($request->get('status')));

        switch ($state) {
            case 0:
                $data = $this->readDataPurchaseRecapList($state, $date_start, $date_end, $supplier, $status);
                break;
            case 1:
                $data = $this->readDataPurchaseSupplierList($state, $date_start, $date_end, $supplier, $status);
                break;
            case 2:
                $data = $this->readDataPurchasePaidList($state, $date_start, $date_end, $supplier, $status);
                break;
            default:
                break;
        }

        return response($data);
    }

    public function readDataPurchaseRecapList($state, $date_start, $date_end, $supplier, $status) {
        $data = ModelTrPurchases::select(
            't.id',
            'tr_purchases.invoice_number',
            'tr_purchases.ppn',
            DB::raw(ModelTrPurchases::queryTotal()),
            't.discount',
            DB::raw('
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
                        ) - t.discount
                    )
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as subtotal
            '),
            DB::raw(' 
                CAST((
                    SELECT (SUM(
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
                        ) - t.discount) * tr_purchases.ppn
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as ppn_price
            '),
            DB::raw('
                CAST((
                    SELECT 
                    (
                        SUM(
                        (
                            (
                                (tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * 
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
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as total_return
            '),
            DB::raw(ModelTrPurchases::queryGrandTotal()),
            DB::raw('
                CAST((
                    SELECT SUM(((COALESCE((
                        SELECT SUM(rtp.qty) 
                        FROM returns_tr_purchases rtp 
                        join tr_purchase_details tsnd on tsnd.id = rtp.tr_purchase_details_id
                        join transactions t on t.id = rtp.tr_purchases_transactions_id
                        WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                        and t.deleted_at is null
                    ), 0)))) 
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,0)) as qty_return
            '),
            't.created_at',
            'tr_purchases.date',
            't.codes_id',
            DB::raw('"Pembelian" as title'),
            'p.name as cashier',
            's.name as supplier',
            's.id as supplier_id',
            'tr_purchases.status as status_id',
            DB::raw('
                CASE
                    WHEN tr_purchases.status = "credit" THEN "Kredit"
                    WHEN tr_purchases.status = "cod" THEN "C.O.D"
                    WHEN tr_purchases.status = "consignment" THEN "Konsinyasi"
                ELSE "Lunas"
                END as status
            ')
        )
        ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
        ->join('persons as p', 'p.id', '=', 't.users_persons_id')
        ->join('persons as s', 's.id', '=', 'tr_purchases.suppliers_persons_id')
        ->where('t.created_at', '>=', '2020-12-27')
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(tr_purchases.date, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->whereNotIn('tr_purchases.status', $status)
        // ->where('s.id', $supplier)
        ->orderBy('created_at', 'desc')
        ->paginate(25000);

        // return $data;

        $total_return = ModelTrPurchases::select(
            DB::raw('
                CAST((
                    SELECT 
                    (
                        SUM(
                        (
                            (
                                (tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * 
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
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as total_return
            ')
        )
        ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
        ->where('t.created_at', '>=', '2020-12-27')
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(tr_purchases.date, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->whereNotIn('tr_purchases.status', $status)
        // ->where('s.id', $supplier)
        ->orderBy('created_at', 'desc')
        ->first();

        $total_purchase = collect([
            'total_purchase' => ModelTrPurchases::select(
                't.id',
                'tr_purchases.invoice_number',
                DB::raw(ModelTrPurchases::queryGrandTotal()),
                't.created_at',
                't.codes_id',
                DB::raw('"Pembelian" as title'),
                'p.name as cashier',
                's.name as supplier',
                's.id as supplier_id',
                'tr_purchases.status as status_id',
                DB::raw('
                    CASE
                        WHEN tr_purchases.status = "credit" THEN "Kredit"
                        WHEN tr_purchases.status = "cod" THEN "C.O.D"
                        WHEN tr_purchases.status = "consignment" THEN "Konsinyasi"
                    ELSE "Lunas"
                    END as status
                ')
            )
            ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
            ->join('persons as p', 'p.id', '=', 't.users_persons_id')
            ->join('persons as s', 's.id', '=', 'tr_purchases.suppliers_persons_id')
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereNull('t.deleted_at')
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(tr_purchases.date, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            ->whereNotIn('tr_purchases.status', $status)
            // ->where('s.id', $supplier)
            ->orderBy('created_at', 'desc')
            ->sum(DB::raw('CAST((grand_total) as DECIMAL(10,0))')) - (empty($total_return['total_return']) || $total_return['total_return'] == null ? 0 : $total_return['total_return'])
        ]);

        $total_return = collect([
            'total_return' => empty($total_return['total_return']) || $total_return['total_return'] == null ? 0 : $total_return['total_return']
        ]);

        $data = $total_return->merge($data);
        $data = $total_purchase->merge($data);

        return $data;
    }

    public function readDataPurchaseSupplierList($state, $date_start, $date_end, $supplier, $status) {
        $suppliers = ModelSuppliers::select(
            'suppliers.persons_id',
            'p.name'
        )
        ->with(['trPurchases' => function($q) use ($status) {
            $q->select(
                'tr_purchases.suppliers_persons_id',
                'tr_purchases.ppn',
                't.discount',
                't.created_at',
                't.codes_id',
                DB::raw(ModelTrPurchases::queryTotal()),
                DB::raw('
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
                            ) - t.discount
                        )
                        FROM tr_purchase_details
                        WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                    ) as DECIMAL(10,2))
                    as subtotal
                '),
                DB::raw(' 
                    CAST((
                        SELECT (SUM(
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
                            ) - t.discount) * tr_purchases.ppn
                        FROM tr_purchase_details
                        WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                    ) as DECIMAL(10,2))
                    as ppn_price
                '),
                DB::raw('
                    CAST((
                        SELECT 
                        (
                            SUM(
                            (
                                (
                                    (tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * 
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
                        FROM tr_purchase_details
                        WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                    ) as DECIMAL(10,2))
                    as total_return
                '),
                DB::raw(ModelTrPurchases::queryGrandTotal()),
                DB::raw('
                    CAST((
                        SELECT SUM(((COALESCE((
                            SELECT SUM(rtp.qty) 
                            FROM returns_tr_purchases rtp 
                            join tr_purchase_details tsnd on tsnd.id = rtp.tr_purchase_details_id
                            join transactions t on t.id = rtp.tr_purchases_transactions_id
                            WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                            and t.deleted_at is null
                        ), 0)))) 
                        FROM tr_purchase_details
                        WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                    ) as DECIMAL(10,0)) as qty_return
                '),
                'tr_purchases.status',
                'tr_purchases.date',
                DB::raw('
                    CASE
                        WHEN tr_purchases.status = "credit" THEN "Kredit"
                        WHEN tr_purchases.status = "cod" THEN "C.O.D"
                        WHEN tr_purchases.status = "consignment" THEN "Konsinyasi"
                    ELSE "Lunas"
                    END as status_title
                ')
            )
            ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereNull('t.deleted_at')
            ->whereNotIn('tr_purchases.status', $status);
        }])
        ->join('persons as p', 'p.id', '=', 'suppliers.persons_id')
        ->whereNotIn('suppliers.persons_id', ['1022000000', '1022100016', '1022100017', '1022000045'])
        ->whereNull('p.deleted_at')
        ->where(function($q) use ($supplier) {
            if(!empty($supplier)) {
                $q->where('suppliers.persons_id', $supplier);
            }
        })
        ->get();

        $data = [];

        foreach ($suppliers as $key => $val) {
            $data = collect($data);
            $first = Arr::first(array_unique(Arr::pluck($val->trPurchases, 'date')));
            $last = Arr::last(array_unique(Arr::pluck($val->trPurchases, 'date')));
            $data->push([
                "id" => $val->persons_id,
                "name" => $val->name,
                "total_invoice" => count($val->trPurchases),
                "total_purchase" => $val->trPurchases->sum('grand_total'),
                "total_return" => $val->trPurchases->sum('total_return'),
                "date_start" => $first,
                "date_end" => $last,
            ]);
            $data->all();
        }

        $data = array_reverse(array_values(Arr::sort($data->toArray(), function ($value) {
            return $value['total_purchase'];
        })));

        $data = [
            "data" => $data,
            "total_billing" => collect($data)->sum('total_purchase'),
            "total_invoice" => collect($data)->sum('total_invoice'),
        ];

        return $data;
    }

    public function readDataPurchasePaidList($state, $date_start, $date_end, $supplier, $status) {
        $tr_purchases = ModelTrPurchases::select(
            't.id',
            'tr_purchases.invoice_number',
            'tr_purchases.ppn',
            DB::raw(ModelTrPurchases::queryTotal()),
            't.discount',
            DB::raw('
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
                        ) - t.discount
                    )
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as subtotal
            '),
            DB::raw(' 
                CAST((
                    SELECT (SUM(
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
                        ) - t.discount) * tr_purchases.ppn
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as ppn_price
            '),
            DB::raw('
                CAST((
                    SELECT 
                    (
                        SUM(
                        (
                            (
                                (tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * 
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
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as total_return
            '),
            DB::raw(ModelTrPurchases::queryGrandTotal()),
            DB::raw('
                CAST((
                    SELECT SUM(((COALESCE((
                        SELECT SUM(rtp.qty) 
                        FROM returns_tr_purchases rtp 
                        join tr_purchase_details tsnd on tsnd.id = rtp.tr_purchase_details_id
                        join transactions t on t.id = rtp.tr_purchases_transactions_id
                        WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                        and t.deleted_at is null
                    ), 0)))) 
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,0)) as qty_return
            '),
            't.created_at',
            'pt.date',
            't.codes_id',
            DB::raw('"Pembelian" as title'),
            'p.name as cashier',
            's.name as supplier',
            's.id as supplier_id',
            'tr_purchases.status as status_id',
            DB::raw('
                CASE
                    WHEN tr_purchases.status = "credit" THEN "Kredit"
                    WHEN tr_purchases.status = "cod" THEN "C.O.D"
                    WHEN tr_purchases.status = "consignment" THEN "Konsinyasi"
                ELSE "Lunas"
                END as status
            ')
        )
        ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
        ->join('payments_tr_purchases as ptp', 'ptp.tr_purchases_transactions_id', '=', 'tr_purchases.transactions_id')
        ->join('payments as pt', 'pt.id', '=', 'ptp.payments_id')
        ->join('persons as p', 'p.id', '=', 't.users_persons_id')
        ->join('persons as s', 's.id', '=', 'tr_purchases.suppliers_persons_id')
        ->where('t.created_at', '>=', '2020-12-27')
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(pt.date, "%Y-%m-%d")'), [$date_start, $date_end]);
            } else {
                $q->whereBetween(DB::raw('DATE_FORMAT(pt.date, "%Y-%m-%d")'), [date('Y-m-d'), date('Y-m-d')]);
            }
        })
        ->where('tr_purchases.status', $status)
        ->where(function($q) use ($supplier) {
            if(!empty($supplier)) {
                $q->where('s.id', $supplier);
            }
        })
        ->orderBy('pt.date', 'desc')
        ->get();

        // return $data;

        $total_return = ModelTrPurchases::select(
            DB::raw('
                CAST((
                    SELECT 
                    (
                        SUM(
                        (
                            (
                                (tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * 
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
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as total_return
            ')
        )
        ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
        ->join('payments_tr_purchases as ptp', 'ptp.tr_purchases_transactions_id', '=', 'tr_purchases.transactions_id')
        ->join('payments as pt', 'pt.id', '=', 'ptp.payments_id')
        ->join('persons as s', 's.id', '=', 'tr_purchases.suppliers_persons_id')
        ->where('t.created_at', '>=', '2020-12-27')
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(pt.date, "%Y-%m-%d")'), [$date_start, $date_end]);
            } else {
                $q->whereBetween(DB::raw('DATE_FORMAT(pt.date, "%Y-%m-%d")'), [date('Y-m-d'), date('Y-m-d')]);
            }
        })
        ->where('tr_purchases.status', $status)
        ->where(function($q) use ($supplier) {
            if(!empty($supplier)) {
                $q->where('s.id', $supplier);
            }
        })
        ->orderBy('pt.date', 'desc')
        ->first();

        $total_purchase = collect([
            'total' => ModelTrPurchases::select(
                't.id',
                'tr_purchases.invoice_number',
                DB::raw(ModelTrPurchases::queryGrandTotal()),
                't.created_at',
                't.codes_id',
                DB::raw('"Pembelian" as title'),
                'p.name as cashier',
                's.name as supplier',
                's.id as supplier_id',
                'tr_purchases.status as status_id',
                DB::raw('
                    CASE
                        WHEN tr_purchases.status = "credit" THEN "Kredit"
                        WHEN tr_purchases.status = "cod" THEN "C.O.D"
                        WHEN tr_purchases.status = "consignment" THEN "Konsinyasi"
                    ELSE "Lunas"
                    END as status
                ')
            )
            ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
            ->join('payments_tr_purchases as ptp', 'ptp.tr_purchases_transactions_id', '=', 'tr_purchases.transactions_id')
            ->join('payments as pt', 'pt.id', '=', 'ptp.payments_id')
            ->join('persons as p', 'p.id', '=', 't.users_persons_id')
            ->join('persons as s', 's.id', '=', 'tr_purchases.suppliers_persons_id')
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereNull('t.deleted_at')
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(pt.date, "%Y-%m-%d")'), [$date_start, $date_end]);
                } else {
                    $q->whereBetween(DB::raw('DATE_FORMAT(pt.date, "%Y-%m-%d")'), [date('Y-m-d'), date('Y-m-d')]);
                }
            })
            ->where('tr_purchases.status', $status)
            ->where(function($q) use ($supplier) {
                if(!empty($supplier)) {
                    $q->where('s.id', $supplier);
                }
            })
            ->orderBy('pt.date', 'desc')
            ->sum(DB::raw('CAST((grand_total) as DECIMAL(10,0))')) - (empty($total_return['total_return']) || $total_return['total_return'] == null ? 0 : $total_return['total_return'])
        ]);

        $total_return = collect([
            'total' => empty($total_return['total_return']) || $total_return['total_return'] == null ? 0 : $total_return['total_return']
        ]);

        // $data = $total_return->merge($data);
        // $data = $total_purchase->merge($data);

        $data = [
            "data" => $tr_purchases,
            "total_return" => $total_return['total'],
            "total_purchase" => $total_purchase['total'],
        ];

        return $data;
    }

    public function readDataDetail(Request $request)
    {
        $state = $request->get('state');
        $date_start = $request->get('date_start');
        $date_end = $request->get('date_end');
        $status = explode(',', trim($request->get('status')));
        $id = $request->get('id');
        /*
        |--------------------------------------------------------------------------
        | Grouping state definition
        |--------------------------------------------------------------------------
        |
        | - Code: 0 -> Recap (Rekap)
        | - Code: 1 -> Supplier (Supplier)
        |
        */
        switch ($state) {
            case '0':
                $data = $this->readDataPurchaseRecapDetail($state, $date_start, $date_end, $id);
                break;
            case '1':
                $data = $this->readDataPurchaseSupplierDetail($state, $date_start, $date_end, $status, $id);
                break;
            default:
                break;
        }

        return response($data);
    }

    public function readDataPurchaseSupplierDetail($state, $date_start, $date_end, $status, $id)
    {
        $data_purchase = ModelTrPurchases::select(
            't.codes_id',
            'tr_purchases.transactions_id as id',
            'tr_purchases.suppliers_persons_id',
            's.name as supplier_name',
            'tr_purchases.invoice_number',
            'tr_purchases.ppn',
            DB::raw(ModelTrPurchases::queryTotal()),
            't.discount',
            DB::raw('
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
                        ) - t.discount
                    )
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as subtotal
            '),
            DB::raw(' 
                CAST((
                    SELECT (SUM(
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
                        ) - t.discount) * tr_purchases.ppn
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as ppn_price
            '),
            DB::raw('
                CAST((
                    SELECT 
                    (
                        SUM(
                        (
                            (
                                (tr_purchase_details.price - (tr_purchase_details.price * tr_purchase_details.discount)) * 
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
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,2))
                as total_return
            '),
            DB::raw(ModelTrPurchases::queryGrandTotal()),
            DB::raw('
                CAST((
                    SELECT SUM(((COALESCE((
                        SELECT SUM(rtp.qty) 
                        FROM returns_tr_purchases rtp 
                        join tr_purchase_details tsnd on tsnd.id = rtp.tr_purchase_details_id
                        join transactions t on t.id = rtp.tr_purchases_transactions_id
                        WHERE rtp.tr_purchase_details_id = tr_purchase_details.id
                        and t.deleted_at is null
                    ), 0)))) 
                    FROM tr_purchase_details
                    WHERE tr_purchase_details.tr_purchases_transactions_id = tr_purchases.transactions_id
                ) as DECIMAL(10,0)) as qty_return
            '),
            'tr_purchases.status as status_id',
            DB::raw('
                CASE
                    WHEN tr_purchases.status = "credit" THEN "Kredit"
                    WHEN tr_purchases.status = "cod" THEN "C.O.D"
                    WHEN tr_purchases.status = "consignment" THEN "Konsinyasi"
                ELSE "Lunas"
                END as status
            '),
            DB::raw('"Pembelian" as title'),
            'p.name as cashier',
            'p.name as cashier_name',
            't.created_at'
        )
        ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
        ->join('persons as p', 'p.id', '=', 't.users_persons_id')
        ->join('persons as s', 's.id', '=', 'tr_purchases.suppliers_persons_id')
        ->with('supplier')
        ->where('t.created_at', '>=', '2020-12-27')
        ->whereNull('t.deleted_at')
        ->whereNotIn('tr_purchases.status', $status)
        ->where('tr_purchases.suppliers_persons_id', $id)
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(tr_purchases.date, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->get();

        $data['data'] = $data_purchase;
        $data['title'] = "Pembelian";
        $data['date_start'] = $data_purchase->first() != null ? $data_purchase->first()->created_at : null;
        $data['date_end'] = $data_purchase->last() != null ? $data_purchase->last()->created_at : null;
        $data['total_invoice'] = count($data_purchase);
        $data['total_purchase'] = $data_purchase->sum('grand_total');

        collect($data);

        return $data;
    }
}
