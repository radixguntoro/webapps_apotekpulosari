<?php

namespace App\Http\Controllers\Backend\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Libraries\ArrayManage;
use App\Http\Controllers\Controller;
use App\Model\ModelMedicines;
use App\Model\ModelCustomers;
use App\Model\ModelTransactions;
use App\Model\ModelTrSales;
use App\Model\ModelTrSalesNetto;
use App\Model\ModelTrSalesCredit;
use App\Model\ModelReturns;
use App\Model\ModelClosingCashiers;
use App\Model\ModelClosingCashierDetails;
use DB;

class ReportSalesCtrl extends Controller
{
    public function index()
    {
        return view('layouts.adminLayout');
    }

    public function readDataList(Request $request)
    {
        $state = $request->get('state');
        $state_netto = $request->get('state_netto');
        $date_start = $request->get('date_start');
        $date_end = $request->get('date_end');
        $customer = $request->get('customer');
        $status = explode(',', trim($request->get('status')));
        $sales = explode(',', trim($request->get('sales')));

        /*
        |--------------------------------------------------------------------------
        | Grouping state definition
        |--------------------------------------------------------------------------
        |
        | - Code: 0 -> Recap (Rekap)
        | - Code: 1 -> Customer (Pelanggan)
        | - Code: 2 -> Item (Barang)
        | - Code: 3 -> Closing Cashier (Tutup Kasir)
        |
        */
        switch ($state) {
            case 0:
                $data = $this->readDataSalesRecapList($state, $date_start, $date_end, $sales);
                break;
            case 1:
                $data = $this->readDataSalesNettoList($state, $date_start, $date_end, 306, $status, $customer, $state_netto);
                break;
            case 2:
                $data = $this->readDataSalesCustomerList($state, $date_start, $date_end, $sales, $customer, $status);
                break;
            case 3:
                $data = $this->readDataSalesItemList($state, $date_start, $date_end, $status);
                break;
            case 4:
                $data = $this->readDataSalesClosingCashierList($state, $date_start, $date_end, $status);
                break;
            default:
                break;
        }

        return response($data);
    }

    public function readDataSalesRecapList($state, $date_start, $date_end, $sales) 
    {
        $data_sales = ['301', '302', '303', '304', '307'];
        $filtered = Arr::flatten(ArrayManage::except($data_sales, $sales));
        $filtered_netto = Arr::flatten(Arr::where($sales, function ($value, $key) {
            if ($value == '306') {
                return is_string($value);
            }
        }));

        $sales_netto = ModelTrSalesNetto::select(
            't.id',
            DB::raw(ModelTrSalesNetto::queryGrandTotal()),
            't.created_at',
            't.codes_id',
            DB::raw('"Netto" as title'),
            'p.name as cashier_name',
        )
        ->join('transactions as t', 't.id', '=', 'tr_sales_netto.tr_sales_transactions_id')
        ->join('persons as p', 'p.id', '=', 't.users_persons_id')
        ->where('t.created_at', '>=', '2020-12-27')
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->whereNotIn('t.codes_id', $filtered_netto);

        $data = ModelTransactions::select(
                'transactions.id',
                DB::raw('CAST((transactions.grand_total) as DECIMAL(10,2)) as grand_total'),
                'transactions.created_at',
                'transactions.codes_id',
                DB::raw('
                    CASE
                        WHEN transactions.codes_id = 301 THEN "Reguler"
                        WHEN transactions.codes_id = 302 THEN "Racik"
                        WHEN transactions.codes_id = 303 THEN "Resep"
                        WHEN transactions.codes_id = 304 THEN "Lab"
                        WHEN transactions.codes_id = 307 THEN "Kredit"
                    ELSE "Netto"
                    END as title
                '),
                'p.name as cashier_name'
            )
            ->join('tr_sales as ts', 'ts.transactions_id', '=', 'transactions.id')
            ->join('persons as p', 'p.id', '=', 'transactions.users_persons_id')
            ->whereIn('transactions.codes_id', $filtered)
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(transactions.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            // ->union($sales_netto)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $total_return = collect([
            'total_return' => ModelReturns::select(
                'returns.id as id'
            )
            ->join('returns_tr_sales as rts', 'rts.returns_id', '=', 'returns.id')
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(returns.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            ->sum(DB::raw('CAST(COALESCE((
                    SELECT 
                        SUM((rts.price * rts.qty) - ((rts.price * rts.qty) * (rts.discount / 100))) as total_return 
                    FROM returns as r
                    JOIN returns_tr_sales as rts on rts.returns_id = r.id
                    WHERE DATE_FORMAT(r.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.'"

                ), 0) as DECIMAL(10,0))'
            ))
        ]);

        $total_sales = collect([
            'total_sales' => ModelTransactions::select(
                'transactions.id',
                DB::raw('(transactions.total - transactions.discount) as grand_total'),
                'transactions.created_at',
                'transactions.codes_id',
                DB::raw('
                    CASE
                        WHEN transactions.codes_id = 301 THEN "Reguler"
                        WHEN transactions.codes_id = 302 THEN "Racik"
                        WHEN transactions.codes_id = 303 THEN "Resep"
                        WHEN transactions.codes_id = 304 THEN "Lab"
                        WHEN transactions.codes_id = 307 THEN "Kredit"
                    ELSE "Netto"
                    END as title
                '),
                'p.name as cashier_name'
            )
            ->join('tr_sales as ts', 'ts.transactions_id', '=', 'transactions.id')
            ->join('persons as p', 'p.id', '=', 'transactions.users_persons_id')
            ->whereIn('transactions.codes_id', $filtered)
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(transactions.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            // ->union($sales_netto)
            ->orderBy('created_at', 'asc')
            ->sum(DB::raw('CAST((grand_total) as DECIMAL(10,0))')) - $total_return['total_return']
        ]);

        $data = $total_return->merge($data);
        $data = $total_sales->merge($data);

        return $data;
    }

    public function readDataSalesNettoList($state, $date_start, $date_end, $sales, $status, $customer, $state_netto) 
    {
        if ($state_netto == 1) {
            $data = $this->readDataSalesNettoListPaid($state, $date_start, $date_end, $sales, $status, $customer, $state_netto);
        } else {
            $data = $this->readDataSalesNettoListRecap($state, $date_start, $date_end, $sales, $status, $customer, $state_netto);
        }

        return $data;
    }

    public function readDataSalesNettoListRecap($state, $date_start, $date_end, $sales, $status, $customer, $state_netto)
    {
        $data = ModelTransactions::select(
            'transactions.id',
            DB::raw('CAST((transactions.grand_total) as DECIMAL(10,2)) as grand_total'),
            'transactions.created_at',
            'transactions.codes_id',
            DB::raw('
                CASE
                    WHEN transactions.codes_id = 301 THEN "Reguler"
                    WHEN transactions.codes_id = 302 THEN "Racik"
                    WHEN transactions.codes_id = 303 THEN "Resep"
                    WHEN transactions.codes_id = 304 THEN "Lab"
                    WHEN transactions.codes_id = 307 THEN "Kredit"
                ELSE "Netto"
                END as title
            '),
            DB::raw('
                CASE
                    WHEN tsn.status = "credit" THEN "Kredit"
                    WHEN tsn.status = "cod" THEN "C.O.D"
                    WHEN tsn.status = "consignment" THEN "Konsinyasi"
                ELSE "Lunas"
                END as status
            '),
            'tsn.status as status_id',
            'p.name as cashier_name',
            'c.name as customer_name'
        )
        ->join('tr_sales as ts', 'ts.transactions_id', '=', 'transactions.id')
        ->join('tr_sales_netto as tsn', 'tsn.tr_sales_transactions_id', '=', 'ts.transactions_id')
        ->join('persons as p', 'p.id', '=', 'transactions.users_persons_id')
        ->join('persons as c', 'c.id', '=', 'tsn.customers_persons_id')
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(transactions.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->where('transactions.codes_id', $sales)
        ->whereNotIn('tsn.status', $status)
        ->where(function($q) use ($customer) {
            if(!empty($customer)) {
                $q->where('tsn.customers_persons_id', $customer);
            }
        })
        ->orderBy('created_at', 'asc')
        ->paginate(50);

        $total_return = collect([
            'total_return' => ModelReturns::select(
                'returns.id as id'
            )
            ->join('returns_tr_sales_netto as rts', 'rts.returns_id', '=', 'returns.id')
            ->join('tr_sales_netto', 'tr_sales_netto.tr_sales_transactions_id', '=', 'rts.tr_sales_transactions_id')
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(returns.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            ->whereNotIn('tr_sales_netto.status', $status)
            ->where(function($q) use ($customer) {
                if(!empty($customer)) {
                    $q->where('tr_sales_netto.customers_persons_id', $customer);
                }
            })
            ->sum(DB::raw('CAST(COALESCE((
                    select SUM((tsd.price - (tsd.price * tsd.discount)) * rtsn.qty) as total_return 
                    from returns_tr_sales_netto rtsn
                    join returns r on r.id = rtsn.returns_id
                    join tr_sales_netto_details tsd on tsd.id = rtsn.tr_sales_details_id
                    join transactions t on t.id = rtsn.tr_sales_transactions_id
                    where rtsn.tr_sales_transactions_id = tr_sales_netto.tr_sales_transactions_id
                    and t.deleted_at is null
                    and DATE_FORMAT(r.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.'"

                ), 0) as DECIMAL(10,0))'
            ))
        ]);

        $total_sales = collect([
            'total_sales' => ModelTransactions::select(
                'transactions.id',
                DB::raw('(transactions.total - transactions.discount) as grand_total'),
                'transactions.created_at',
                'transactions.codes_id',
                DB::raw('
                    CASE
                        WHEN transactions.codes_id = 301 THEN "Reguler"
                        WHEN transactions.codes_id = 302 THEN "Racik"
                        WHEN transactions.codes_id = 303 THEN "Resep"
                        WHEN transactions.codes_id = 304 THEN "Lab"
                        WHEN transactions.codes_id = 307 THEN "Kredit"
                    ELSE "Netto"
                    END as title
                '),
                DB::raw('
                    CASE
                        WHEN tsn.status = "credit" THEN "Kredit"
                        WHEN tsn.status = "cod" THEN "C.O.D"
                        WHEN tsn.status = "consignment" THEN "Konsinyasi"
                    ELSE "Lunas"
                    END as status
                '),
                'tsn.status as status_id',
                'p.name as cashier_name',
                'c.name as customer_name'
            )
            ->join('tr_sales as ts', 'ts.transactions_id', '=', 'transactions.id')
            ->join('tr_sales_netto as tsn', 'tsn.tr_sales_transactions_id', '=', 'ts.transactions_id')
            ->join('persons as p', 'p.id', '=', 'transactions.users_persons_id')
            ->join('persons as c', 'c.id', '=', 'tsn.customers_persons_id')
            ->where('transactions.codes_id', $sales)
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(transactions.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            ->whereNotIn('tsn.status', $status)
            ->where(function($q) use ($customer) {
                if(!empty($customer)) {
                    $q->where('tsn.customers_persons_id', $customer);
                }
            })
            ->orderBy('created_at', 'asc')
            ->sum(DB::raw('CAST((grand_total) as DECIMAL(10,0))')) - $total_return['total_return']
        ]);

        $data = $total_return->merge($data);
        $data = $total_sales->merge($data);

        return $data;
    }

    public function readDataSalesNettoListPaid($state, $date_start, $date_end, $sales, $status, $customer, $state_netto)
    {
        $data = ModelTransactions::select(
            'transactions.id',
            DB::raw('CAST((transactions.grand_total) as DECIMAL(10,2)) as grand_total'),
            'pt.date as created_at',
            'transactions.codes_id',
            DB::raw('
                CASE
                    WHEN transactions.codes_id = 301 THEN "Reguler"
                    WHEN transactions.codes_id = 302 THEN "Racik"
                    WHEN transactions.codes_id = 303 THEN "Resep"
                    WHEN transactions.codes_id = 304 THEN "Lab"
                    WHEN transactions.codes_id = 307 THEN "Kredit"
                ELSE "Netto"
                END as title
            '),
            DB::raw('
                CASE
                    WHEN tsn.status = "credit" THEN "Kredit"
                    WHEN tsn.status = "cod" THEN "C.O.D"
                    WHEN tsn.status = "consignment" THEN "Konsinyasi"
                ELSE "Lunas"
                END as status
            '),
            'tsn.status as status_id',
            'p.name as cashier_name',
            'c.name as customer_name'
        )
        ->join('tr_sales as ts', 'ts.transactions_id', '=', 'transactions.id')
        ->join('tr_sales_netto as tsn', 'tsn.tr_sales_transactions_id', '=', 'ts.transactions_id')
        ->join('persons as p', 'p.id', '=', 'transactions.users_persons_id')
        ->join('persons as c', 'c.id', '=', 'tsn.customers_persons_id')
        ->join('payments_tr_sales_netto as ptp', 'ptp.tr_sales_netto_transactions_id', '=', 'tsn.tr_sales_transactions_id')
        ->join('payments as pt', 'pt.id', '=', 'ptp.payments_id')
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(pt.date, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->where('transactions.codes_id', $sales)
        ->where('tsn.status', $status)
        ->where(function($q) use ($customer) {
            if(!empty($customer)) {
                $q->where('tsn.customers_persons_id', $customer);
            }
        })
        ->orderBy('created_at', 'asc')
        ->paginate(50);

        // return $data;

        $total_return = collect([
            'total_return' => ModelReturns::select(
                'returns.id as id'
            )
            ->join('returns_tr_sales_netto as rts', 'rts.returns_id', '=', 'returns.id')
            ->join('tr_sales_netto', 'tr_sales_netto.tr_sales_transactions_id', '=', 'rts.tr_sales_transactions_id')
            ->join('payments_tr_sales_netto as ptp', 'ptp.tr_sales_netto_transactions_id', '=', 'tr_sales_netto.tr_sales_transactions_id')
            ->join('payments as pt', 'pt.id', '=', 'ptp.payments_id')
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(returns.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            ->where('tr_sales_netto.status', $status)
            ->where(function($q) use ($customer) {
                if(!empty($customer)) {
                    $q->where('tr_sales_netto.customers_persons_id', $customer);
                }
            })
            ->sum(DB::raw('CAST(COALESCE((
                    select SUM((tsd.price - (tsd.price * tsd.discount)) * rtsn.qty) as total_return 
                    from returns_tr_sales_netto rtsn
                    join returns r on r.id = rtsn.returns_id
                    join tr_sales_netto_details tsd on tsd.id = rtsn.tr_sales_details_id
                    join transactions t on t.id = rtsn.tr_sales_transactions_id
                    where rtsn.tr_sales_transactions_id = tr_sales_netto.tr_sales_transactions_id
                    and t.deleted_at is null
                    and DATE_FORMAT(r.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.'"

                ), 0) as DECIMAL(10,0))'
            ))
        ]);

        $total_sales = collect([
            'total_sales' => ModelTransactions::select(
                'transactions.id',
                DB::raw('(transactions.total - transactions.discount) as grand_total'),
                'pt.date as created_at',
                'transactions.codes_id',
                DB::raw('
                    CASE
                        WHEN transactions.codes_id = 301 THEN "Reguler"
                        WHEN transactions.codes_id = 302 THEN "Racik"
                        WHEN transactions.codes_id = 303 THEN "Resep"
                        WHEN transactions.codes_id = 304 THEN "Lab"
                        WHEN transactions.codes_id = 307 THEN "Kredit"
                    ELSE "Netto"
                    END as title
                '),
                DB::raw('
                    CASE
                        WHEN tsn.status = "credit" THEN "Kredit"
                        WHEN tsn.status = "cod" THEN "C.O.D"
                        WHEN tsn.status = "consignment" THEN "Konsinyasi"
                    ELSE "Lunas"
                    END as status
                '),
                'tsn.status as status_id',
                'p.name as cashier_name',
                'c.name as customer_name'
            )
            ->join('tr_sales as ts', 'ts.transactions_id', '=', 'transactions.id')
            ->join('tr_sales_netto as tsn', 'tsn.tr_sales_transactions_id', '=', 'ts.transactions_id')
            ->join('payments_tr_sales_netto as ptp', 'ptp.tr_sales_netto_transactions_id', '=', 'tsn.tr_sales_transactions_id')
            ->join('payments as pt', 'pt.id', '=', 'ptp.payments_id')
            ->join('persons as p', 'p.id', '=', 'transactions.users_persons_id')
            ->join('persons as c', 'c.id', '=', 'tsn.customers_persons_id')
            ->where('transactions.codes_id', $sales)
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(pt.date, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            ->where('tsn.status', $status)
            ->where(function($q) use ($customer) {
                if(!empty($customer)) {
                    $q->where('tsn.customers_persons_id', $customer);
                }
            })
            ->orderBy('created_at', 'asc')
            ->sum(DB::raw('CAST((grand_total) as DECIMAL(10,0))')) - $total_return['total_return']
        ]);

        $data = $total_return->merge($data);
        $data = $total_sales->merge($data);

        return $data;
    }

    public function readDataSalesCustomerList($state, $date_start, $date_end, $sales, $customer, $status) 
    {
        $sales = collect($sales)->first();
        $customers = ModelCustomers::select(
                    'customers.persons_id',
                    'p.name'
                )
                ->join('persons as p', 'p.id', '=', 'customers.persons_id')
                ->with($sales == '306' ? ['trSalesNetto' => function($q) use ($status) {
                    $q->select(
                        't.id',
                        'tr_sales_netto.customers_persons_id',
                        DB::raw('
                            CAST((
                                SELECT SUM(((tr_sales_netto_details.price - (tr_sales_netto_details.price * tr_sales_netto_details.discount)) * (tr_sales_netto_details.qty + COALESCE((
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
                                ), 0)) * tr_sales_netto_details.ppn))) 
                                FROM tr_sales_netto_details
                                WHERE tr_sales_netto_details.tr_sales_netto_id = tr_sales_netto.tr_sales_transactions_id
                            ) as DECIMAL(10,2))as grand_total
                        '),
                        't.created_at',
                        't.codes_id',
                        'tr_sales_netto.status',
                        DB::raw('
                            CASE
                                WHEN tr_sales_netto.status = "credit" THEN "Kredit"
                                WHEN tr_sales_netto.status = "cod" THEN "C.O.D"
                                WHEN tr_sales_netto.status = "consignment" THEN "Konsinyasi"
                            ELSE "Lunas"
                            END as status_title
                        ')
                    )
                    ->join('transactions as t', 't.id', '=', 'tr_sales_netto.tr_sales_transactions_id')
                    ->where('t.created_at', '>=', '2020-12-27')
                    ->whereNotIn('tr_sales_netto.status', $status);
                }] : ['trSalesCredit' => function($q) use ($status) {
                    $q->select(
                        't.id',
                        'tr_sales_credit.customers_persons_id',
                        't.grand_total',
                        't.created_at',
                        't.codes_id',
                        'tr_sales_credit.status',
                        DB::raw('
                            CASE
                                WHEN tr_sales_credit.status = "credit" THEN "Kredit"
                            ELSE "Lunas"
                            END as status_title
                        ')
                    )
                    ->join('transactions as t', 't.id', '=', 'tr_sales_credit.tr_sales_transactions_id')
                    ->where('t.created_at', '>=', '2020-12-27')
                    ->whereNotIn('tr_sales_credit.status', $status);
                }])
                ->get();

        $data = [];

        foreach ($customers as $key => $val) {
            $first = Arr::first(array_unique(Arr::pluck($val->trSalesCredit, 'created_at')));
            $last = Arr::last(array_unique(Arr::pluck($val->trSalesCredit, 'created_at')));
            $data = collect($data);
            $data->push([
                "id" => $val->persons_id,
                "name" => $val->name,
                "total_invoice" => $sales == '306' ? count($val->trSalesNetto) : count($val->trSalesCredit),
                "total_sales" => $sales == '306' ? $val->trSalesNetto->sum('grand_total') : $val->trSalesCredit->sum('grand_total'),
                "date_start" => date('Y-m-d', strtotime($first)),
                "date_end" => date('Y-m-d', strtotime($last)),
            ]);
            $data->all();
        }

        $data = array_reverse(array_values(Arr::sort($data->toArray(), function ($value) {
            return $value['total_sales'];
        })));

        $data = [
            "data" => $data,
            "total_billing" => collect($data)->sum('total_sales'),
            "total_invoice" => collect($data)->sum('total_invoice'),
        ];

        return $data;
    }

    public function readDataSalesItemList($state, $date_start, $date_end, $status) 
    {
        $data = ModelMedicines::select(
            'medicines.items_id as id',
            'i.name',
            DB::raw('
                (
                    (
                        SELECT
                            COALESCE(SUM(tsrd.qty), 0)
                        FROM tr_sales_regular_details as tsrd
                        JOIN transactions as t on t.id = tsrd.tr_sales_regular_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsrd.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tsmd.qty), 0)
                        FROM tr_sales_mix_details as tsmd
                        JOIN tr_sales_mix_medicines as tsmm on tsmm.id = tsmd.tr_sales_mix_medicines_id
                        JOIN transactions as t on t.id = tsmm.tr_sales_mix_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsmd.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tsrd.qty), 0)
                        FROM tr_sales_recipe_details as tsrd
                        JOIN tr_sales_recipe_medicines as tsrm on tsrm.id = tsrd.tr_sales_recipe_medicines_id
                        JOIN transactions as t on t.id = tsrm.tr_sales_recipe_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsrd.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tsld.qty), 0)
                        FROM tr_sales_lab_details as tsld
                        JOIN transactions as t on t.id = tsld.tr_sales_lab_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsld.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tsnd.qty), 0)
                        FROM tr_sales_netto_details as tsnd
                        JOIN transactions as t on t.id = tsnd.tr_sales_netto_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsnd.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tscd.qty), 0)
                        FROM tr_sales_credit_details as tscd
                        JOIN transactions as t on t.id = tscd.tr_sales_credit_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tscd.medicines_items_id = medicines.items_id
                    )
                ) as total_qty
            '),
            DB::raw('
                (
                    (
                        SELECT
                            COALESCE(SUM(tsrd.qty * tsrd.price - ((tsrd.qty * tsrd.price) * tsrd.discount)), 0)
                        FROM tr_sales_regular_details as tsrd
                        JOIN transactions as t on t.id = tsrd.tr_sales_regular_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsrd.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tsmd.qty * tsmd.price - ((tsmd.qty * tsmd.price) * tsmd.discount)), 0)
                        FROM tr_sales_mix_details as tsmd
                        JOIN tr_sales_mix_medicines as tsmm on tsmm.id = tsmd.tr_sales_mix_medicines_id
                        JOIN transactions as t on t.id = tsmm.tr_sales_mix_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsmd.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tsrd.qty * tsrd.price - ((tsrd.qty * tsrd.price) * tsrd.discount)), 0)
                        FROM tr_sales_recipe_details as tsrd
                        JOIN tr_sales_recipe_medicines as tsrm on tsrm.id = tsrd.tr_sales_recipe_medicines_id
                        JOIN transactions as t on t.id = tsrm.tr_sales_recipe_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsrd.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tsld.qty * tsld.price - ((tsld.qty * tsld.price) * tsld.discount)), 0)
                        FROM tr_sales_lab_details as tsld
                        JOIN transactions as t on t.id = tsld.tr_sales_lab_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsld.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tsnd.qty * tsnd.price - ((tsnd.qty * tsnd.price) * tsnd.discount)), 0)
                        FROM tr_sales_netto_details as tsnd
                        JOIN transactions as t on t.id = tsnd.tr_sales_netto_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tsnd.medicines_items_id = medicines.items_id
                    ) +
                    (
                        SELECT
                            COALESCE(SUM(tscd.qty * tscd.price - ((tscd.qty * tscd.price) * tscd.discount)), 0)
                        FROM tr_sales_credit_details as tscd
                        JOIN transactions as t on t.id = tscd.tr_sales_credit_id
                        WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                        AND t.deleted_at is null
                        AND tscd.medicines_items_id = medicines.items_id
                    )
                ) as total_sales
            '),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsrd.qty), 0)
                    FROM tr_sales_regular_details as tsrd
                    JOIN transactions as t on t.id = tsrd.tr_sales_regular_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsrd.medicines_items_id = medicines.items_id
                ) as qty_regular'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsmd.qty), 0)
                    FROM tr_sales_mix_details as tsmd
                    JOIN tr_sales_mix_medicines as tsmm on tsmm.id = tsmd.tr_sales_mix_medicines_id
                    JOIN transactions as t on t.id = tsmm.tr_sales_mix_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsmd.medicines_items_id = medicines.items_id
                ) as qty_mix'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsrd.qty), 0)
                    FROM tr_sales_recipe_details as tsrd
                    JOIN tr_sales_recipe_medicines as tsrm on tsrm.id = tsrd.tr_sales_recipe_medicines_id
                    JOIN transactions as t on t.id = tsrm.tr_sales_recipe_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsrd.medicines_items_id = medicines.items_id
                ) as qty_recipe'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsld.qty), 0)
                    FROM tr_sales_lab_details as tsld
                    JOIN transactions as t on t.id = tsld.tr_sales_lab_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsld.medicines_items_id = medicines.items_id
                ) as qty_lab'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsnd.qty), 0)
                    FROM tr_sales_netto_details as tsnd
                    JOIN transactions as t on t.id = tsnd.tr_sales_netto_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsnd.medicines_items_id = medicines.items_id
                ) as qty_netto'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tscd.qty), 0)
                    FROM tr_sales_credit_details as tscd
                    JOIN transactions as t on t.id = tscd.tr_sales_credit_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tscd.medicines_items_id = medicines.items_id
                ) as qty_credit'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsrd.qty * tsrd.price - ((tsrd.qty * tsrd.price) * tsrd.discount)), 0)
                    FROM tr_sales_regular_details as tsrd
                    JOIN transactions as t on t.id = tsrd.tr_sales_regular_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsrd.medicines_items_id = medicines.items_id
                ) as sales_regular'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsmd.qty * tsmd.price - ((tsmd.qty * tsmd.price) * tsmd.discount)), 0)
                    FROM tr_sales_mix_details as tsmd
                    JOIN tr_sales_mix_medicines as tsmm on tsmm.id = tsmd.tr_sales_mix_medicines_id
                    JOIN transactions as t on t.id = tsmm.tr_sales_mix_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsmd.medicines_items_id = medicines.items_id
                ) as sales_mix'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsrd.qty * tsrd.price - ((tsrd.qty * tsrd.price) * tsrd.discount)), 0)
                    FROM tr_sales_recipe_details as tsrd
                    JOIN tr_sales_recipe_medicines as tsrm on tsrm.id = tsrd.tr_sales_recipe_medicines_id
                    JOIN transactions as t on t.id = tsrm.tr_sales_recipe_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsrd.medicines_items_id = medicines.items_id
                ) as sales_recipe'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsld.qty * tsld.price - ((tsld.qty * tsld.price) * tsld.discount)), 0)
                    FROM tr_sales_lab_details as tsld
                    JOIN transactions as t on t.id = tsld.tr_sales_lab_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsld.medicines_items_id = medicines.items_id
                ) as sales_lab'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tsnd.qty * tsnd.price - ((tsnd.qty * tsnd.price) * tsnd.discount)), 0)
                    FROM tr_sales_netto_details as tsnd
                    JOIN transactions as t on t.id = tsnd.tr_sales_netto_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tsnd.medicines_items_id = medicines.items_id
                ) as sales_netto'
            ),
            DB::raw('
                (
                    SELECT
                        COALESCE(SUM(tscd.qty * tscd.price - ((tscd.qty * tscd.price) * tscd.discount)), 0)
                    FROM tr_sales_credit_details as tscd
                    JOIN transactions as t on t.id = tscd.tr_sales_credit_id
                    WHERE DATE_FORMAT(t.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" AND "'.$date_end.' 23:59:59"
                    AND t.deleted_at is null
                    AND tscd.medicines_items_id = medicines.items_id
                ) as sales_credit'
            )
        )
        ->join('items as i', 'i.id', '=', 'medicines.items_id')
        ->whereNull('i.deleted_at')
        ->whereNotIn('i.id', ['2012001318', '2012100242', '2012100041', '2012001072', '2012001152'])
        ->where('i.status', 'active')
        ->orderBy('total_qty', 'desc')
        ->paginate(25);

        return $data;
    }

    public function readDataSalesClosingCashierList($state, $date_start, $date_end, $status) 
    {
        $data = ModelClosingCashiers::select(
            "closing_cashiers.id as id",
            DB::raw(ModelClosingCashiers::queryIncomeApp()),
            "closing_cashiers.income_real as income_real",
            DB::raw(ModelClosingCashiers::queryIncomeDiff()),
            "closing_cashiers.shift as shift",
            DB::raw('DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d") as date'),
            "closing_cashiers.created_at as created_at",
            DB::raw('DATE_FORMAT(closing_cashiers.created_at, "%H:%i") as time_at'),
            "closing_cashiers.users_persons_id as users_persons_id",
            "c.name as cashier",
            DB::raw('(
                SELECT count(closing_cashiers_id) as total
                FROM closing_cashier_details
                WHERE closing_cashier_details.closing_cashiers_id = closing_cashiers.id
            ) as nota'),
            DB::raw(ModelClosingCashiers::queryTotalReturn())
        )
        ->join('persons as c', 'c.id', '=', 'closing_cashiers.users_persons_id')
        ->orderBy('closing_cashiers.id', 'desc')
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->get();

        // $get_date = array_unique(Arr::pluck($closing_cashiers->toArray(), 'date'));
        
        // $date = [];
        // foreach ($get_date as $key => $val) {
        //     $date[]['date'] = $val;
        // }

        // $group_date = [];
        // foreach ($date as $key_date => $val_date) {
        //     foreach ($closing_cashiers as $key_cc => $val_cc) {
        //         if ($val_date['date'] == $val_cc->date) {
        //             $group_date[$key_date]['date'] = $val_date['date'];
        //             $group_date[$key_date]['details'][] = $val_cc;
        //         }
        //     }
        // }
        
        // $data = [];

        // foreach ($group_date as $key => $val) {
        //     $data[$key]['date'] = $val['date'];
        //     $data[$key]['1_shift'] = $val['details'][1]['shift'];
        //     $data[$key]['1_cashier'] = $val['details'][1]['cashier'];
        //     $data[$key]['1_time_at'] = $val['details'][1]['time_at'];
        //     $data[$key]['1_income_app'] = $val['details'][1]['income_app'];
        //     $data[$key]['1_income_real'] = $val['details'][1]['income_real'];
        //     $data[$key]['1_income_diff'] = $val['details'][1]['income_diff'];
        //     $data[$key]['2_shift'] = $val['details'][0]['shift'];
        // }

        $data = [
            "data" => $data,
            "total_income_app" => $data->sum('income_app'),
            "total_income_real" => $data->sum('income_real'),
            "total_income_diff" => $data->sum('income_diff'),
            "total" => count($data),
            "last_page" => 1,
            "current_page" => 1,
        ];

        return $data;
    }

    public function readDataDetail(Request $request)
    {
        $state = $request->get('state');
        $date_start = $request->get('date_start');
        $date_end = $request->get('date_end');
        $sales = explode(',', trim($request->get('sales')));
        $status = explode(',', trim($request->get('status')));
        $id = $request->get('id');
        /*
        |--------------------------------------------------------------------------
        | Grouping state definition
        |--------------------------------------------------------------------------
        |
        | - Code: 0 -> Recap (Rekap)
        | - Code: 1 -> Customer (Pelanggan)
        | - Code: 2 -> Closing Cashier (Tutup Kasir)
        |
        */
        switch ($state) {
            case '0':
                $data = $this->readDataSalesRecapDetail($state, $date_start, $date_end, $id);
                break;
            case '2':
                $data = $this->readDataSalesCustomerDetail($state, $date_start, $date_end, $sales, $status, $id);
                break;
            case '4':
                $data = $this->readDataSalesClosingCashierDetail($state, $date_start, $date_end, $sales, $status, $id);
                break;
            default:
                break;
        }

        return response($data);
    }

    public function readDataSalesCustomerDetail($state, $date_start, $date_end, $sales, $status, $id)
    {
        $sales = collect($sales)->first();
        if($sales == '306') {
            $data_sales = ModelTrSalesNetto::select(
                't.codes_id',
                'tr_sales_netto.tr_sales_transactions_id as id',
                'c.name as customer_name',
                DB::raw(ModelTrSalesNetto::queryTotal()),
                DB::raw(ModelTrSalesNetto::queryGrandTotal()),
                'tr_sales_netto.status',
                DB::raw('
                    CASE
                        WHEN tr_sales_netto.status = "credit" THEN "Kredit"
                        WHEN tr_sales_netto.status = "cod" THEN "C.O.D"
                        WHEN tr_sales_netto.status = "consignment" THEN "Konsinyasi"
                    ELSE "Lunas"
                    END as status_title
                '),
                DB::raw('"Netto" as title'),
                'p.name as cashier_name',
                't.created_at'
            )
            ->join('transactions as t', 't.id', '=', 'tr_sales_netto.tr_sales_transactions_id')
            ->join('persons as p', 'p.id', '=', 't.users_persons_id')
            ->join('persons as c', 'c.id', '=', 'tr_sales_netto.customers_persons_id')
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereNotIn('tr_sales_netto.status', $status)
            ->where('tr_sales_netto.customers_persons_id', $id)
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            ->get();
        } else {
            $data_sales = ModelTrSalesCredit::select(
                't.codes_id',
                'tr_sales_credit.tr_sales_transactions_id as id',
                'c.name as customer_name',
                't.total',
                't.grand_total',
                'tr_sales_credit.status',
                DB::raw('
                    CASE
                        WHEN tr_sales_credit.status = "credit" THEN "Kredit"
                    ELSE "Lunas"
                    END as status_title
                '),
                DB::raw('"Kredit" as title'),
                'p.name as cashier_name',
                't.created_at'
            )
            ->join('transactions as t', 't.id', '=', 'tr_sales_credit.tr_sales_transactions_id')
            ->join('persons as p', 'p.id', '=', 't.users_persons_id')
            ->join('persons as c', 'c.id', '=', 'tr_sales_credit.customers_persons_id')
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereNotIn('tr_sales_credit.status', $status)
            ->where('tr_sales_credit.customers_persons_id', $id)
            ->where(function($q) use ($date_start, $date_end) {
                if(!empty($date_start) && !empty($date_end)) {
                    $q->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
                }
            })
            ->get();
        }

        $data['data'] = $data_sales;
        $data['title'] = $sales == '306' ? "Netto" : "Kredit";
        $data['date_start'] = $data_sales->first() != null ? $data_sales->first()->created_at : null;
        $data['date_end'] = $data_sales->last() != null ? $data_sales->last()->created_at : null;
        $data['total_invoice'] = count($data_sales);
        $data['total_sales'] = $data_sales->sum('grand_total');

        collect($data);

        return $data;
    }

    public function readDataSalesClosingCashierDetail($state, $date_start, $date_end, $sales, $status, $id)
    {
        $data_sales = ModelClosingCashierDetails::select(
            'closing_cashiers_id',
            't.id',
            't.total',
            't.discount',
            't.grand_total',
            'p.name as cashier_name',
            't.created_at',
            't.codes_id',
            DB::raw('
                CASE
                    WHEN t.codes_id = 302 THEN "Racik"
                    WHEN t.codes_id = 303 THEN "Resep"
                    WHEN t.codes_id = 304 THEN "Lab"
                    WHEN t.codes_id = 306 THEN "Netto"
                    WHEN t.codes_id = 307 THEN "Kredit"
                ELSE "Reguler"
                END as title
            ')
        )
        ->join('transactions as t', 't.id', '=', 'closing_cashier_details.tr_sales_id')
        ->join('persons as p', 'p.id', '=', 't.users_persons_id')
        ->where('t.created_at', '>=', '2020-12-27')
        ->where('closing_cashiers_id', $id)
        ->whereNotIn('t.codes_id', $sales)
        ->get();

        $data['data'] = $data_sales;
        $data['total_invoice'] = count($data_sales);

        return $data;
    }
}
