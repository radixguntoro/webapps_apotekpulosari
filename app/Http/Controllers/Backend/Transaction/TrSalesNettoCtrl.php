<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use App\Libraries\GenerateNumber;
use App\Libraries\RespMessages;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelTrSales;
use App\Model\ModelTrSalesNetto;
use App\Model\ModelTrSalesNettoDetails;
use App\Model\ModelPayments;
use App\Model\ModelPaymentsTrSalesNetto;
use App\Model\ModelMedicines;
use App\Model\ModelReturns;
use App\Model\ModelReturnTrSalesNetto;
use App\Model\ModelStockAdjustments;
use App\Model\ModelStockAdjustmentsTrSalesNetto;
use DB;

class TrSalesNettoCtrl extends TransactionsCtrl
{
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $row = $request->get('row');
        $sort = $request->get('sort');
        $invoice_date = $request->get('invoice_date');
        $payment_date = $request->get('payment_date');
        $customer_id = $request->get('customer_id');
        $filter = explode(',', trim($request->get('filter')));

        if ($search) {
            if ($request->segment(1) == 'api') {
                $data = ModelTrSalesNetto::select(
                        'tr_sales_netto.balance as balance',
                        'tr_sales_netto.ppn as ppn',
                        'tr_sales_netto.date as date',
                        'tr_sales_netto.status as status',
                        'tr_sales_transactions_id as tr_sales_transactions_id',
                        'tr_sales_netto.customers_persons_id as customers_persons_id',
                        'ptp.payments_id as payment_id',
                        'p.date as payment_date',
                        DB::raw('
                            COALESCE(
                            (
                                select SUM((tsd.price - (tsd.price * tsd.discount)) * rtsn.qty) as total_return 
                                from returns_tr_sales_netto rtsn
                                join tr_sales_netto_details tsd on tsd.id = rtsn.tr_sales_details_id
                                join transactions t on t.id = rtsn.tr_sales_transactions_id
                                where rtsn.tr_sales_transactions_id = tr_sales_netto.tr_sales_transactions_id
                                and t.deleted_at is null
                            ), 0)
                            as total_return
                        ')
                    )
                    ->with('trSales', 'trSalesNettoDetails', 'payment', 'returns', 'customer')
                    ->doesntHave('closingCashierDetails')
                    ->join('persons', 'persons.id', '=', 'tr_sales_netto.customers_persons_id')
                    ->leftJoin('payments_tr_sales_netto as ptp', 'ptp.tr_sales_netto_transactions_id', '=', 'tr_sales_netto.tr_sales_transactions_id')
                    ->leftJoin('payments as p', 'p.id', '=', 'ptp.payments_id')
                    ->where('tr_sales_netto.tr_sales_transactions_id', 'LIKE', "%{$search}%")
                    ->whereNotIn('tr_sales_netto.status', $filter)
                    ->where(function($q) use ($invoice_date) {
                        if(!empty($invoice_date)) {
                            $q->where('tr_sales_netto.date', $invoice_date);
                        }
                    })
                    ->where(function($q) use ($payment_date) {
                        if(!empty($invoice_date)) {
                            $q->where('p.date', $payment_date);
                        }
                    })
                    ->orWhere('persons.name', "LIKE", "%{$search}%")
                    ->whereNotIn('tr_sales_netto.status', $filter)
                    ->where(function($q) use ($invoice_date) {
                        if(!empty($invoice_date)) {
                            $q->where('tr_sales_netto.date', $invoice_date);
                        }
                    })
                    ->where(function($q) use ($payment_date) {
                        if(!empty($payment_date)) {
                            $q->where('p.date', $payment_date);
                        }
                    })
                    ->where(function($q) use ($customer_id) {
                        if(!empty($customer_id)) {
                            $q->where('tr_sales_netto.customers_persons_id', $customer_id);
                        }
                    })
                    ->orderBy('tr_sales_netto.tr_sales_transactions_id', 'desc')
                    ->paginate(10);
            } else {
                $data = ModelTrSalesNetto::readDataBySearch($search, $row, $sort);
            }
        } else {
            if ($request->segment(1) == 'api') {
                $is_paid = Arr::where($filter, function ($value, $key) {
                    if($value == 'paid') {
                        return false;
                    } else {
                        return true;
                    }
                });
                $order_by = count($is_paid) > 0 ? 'p.created_at' : 'tr_sales_netto.date';
                $sort = count($is_paid) > 0 ? 'desc' : 'desc';

                $data = ModelTrSalesNetto::select(
                        'tr_sales_netto.balance as balance',
                        'tr_sales_netto.ppn as ppn',
                        'tr_sales_netto.date as date',
                        'tr_sales_netto.status as status',
                        'tr_sales_transactions_id as tr_sales_transactions_id',
                        'tr_sales_netto.customers_persons_id as customers_persons_id',
                        'ptp.payments_id as payment_id',
                        'p.date as payment_date',
                        DB::raw('
                            COALESCE(
                            (
                                select SUM((tsd.price - (tsd.price * tsd.discount)) * rtsn.qty) as total_return 
                                from returns_tr_sales_netto rtsn
                                join tr_sales_netto_details tsd on tsd.id = rtsn.tr_sales_details_id
                                join transactions t on t.id = rtsn.tr_sales_transactions_id
                                where rtsn.tr_sales_transactions_id = tr_sales_netto.tr_sales_transactions_id
                                and t.deleted_at is null
                            ), 0)
                            as total_return
                        ')
                    )
                    ->with('trSales', 'trSalesNettoDetails', 'payment', 'returns', 'customer')
                    ->doesntHave('closingCashierDetails')
                    ->leftJoin('payments_tr_sales_netto as ptp', 'ptp.tr_sales_netto_transactions_id', '=', 'tr_sales_netto.tr_sales_transactions_id')
                    ->leftJoin('payments as p', 'p.id', '=', 'ptp.payments_id')
                    ->whereNotIn('tr_sales_netto.status', $filter)
                    ->where(function($q) use ($invoice_date) {
                        if(!empty($invoice_date)) {
                            $q->where('tr_sales_netto.date', $invoice_date);
                        }
                    })
                    ->where(function($q) use ($payment_date) {
                        if(!empty($payment_date)) {
                            $q->where('p.date', $payment_date);
                        }
                    })
                    ->where(function($q) use ($customer_id) {
                        if(!empty($customer_id)) {
                            $q->where('tr_sales_netto.customers_persons_id', $customer_id);
                        }
                    })
                    ->orderBy($order_by, $sort)
                    ->orderBy('tr_sales_netto.tr_sales_transactions_id', 'desc')
                    ->paginate(10);
                
                $total_sales = ModelTrSalesNetto::select(
                    '*',
                    DB::raw('
                        COALESCE(
                        (
                            select SUM((tsd.price - (tsd.price * tsd.discount)) * rtsn.qty) as total_return 
                            from returns_tr_sales_netto rtsn
                            join tr_sales_netto_details tsd on tsd.id = rtsn.tr_sales_details_id
                            join transactions t on t.id = rtsn.tr_sales_transactions_id
                            where rtsn.tr_sales_transactions_id = tr_sales_netto.tr_sales_transactions_id
                            and t.deleted_at is null
                        ), 0)
                        as total_return
                    ')
                )
                ->join('transactions as t', 't.id', '=', 'tr_sales_netto.tr_sales_transactions_id')
                ->doesntHave('closingCashierDetails')
                ->whereNotIn('tr_sales_netto.status', $filter)
                ->leftJoin('payments_tr_sales_netto as ptp', 'ptp.tr_sales_netto_transactions_id', '=', 'tr_sales_netto.tr_sales_transactions_id')
                ->leftJoin('payments as p', 'p.id', '=', 'ptp.payments_id')
                ->where(function($q) use ($invoice_date) {
                    if(!empty($invoice_date)) {
                        $q->where('tr_sales_netto.date', $invoice_date);
                    }
                })
                ->where(function($q) use ($payment_date) {
                    if(!empty($payment_date)) {
                        $q->where('p.date', $payment_date);
                    }
                })
                ->where(function($q) use ($customer_id) {
                    if(!empty($customer_id)) {
                        $q->where('tr_sales_netto.customers_persons_id', $customer_id);
                    }
                })
                ->where(function($q) use ($customer_id) {
                    if(!empty($customer_id)) {
                        $q->where('tr_sales_netto.customers_persons_id', $customer_id);
                    }
                })
                ->sum('t.grand_total');

                $total_return = ModelTrSalesNetto::select(
                    '*',
                    DB::raw('
                        COALESCE(
                        (
                            select SUM((tsd.price - (tsd.price * tsd.discount)) * rtsn.qty) as total_return 
                            from returns_tr_sales_netto rtsn
                            join tr_sales_netto_details tsd on tsd.id = rtsn.tr_sales_details_id
                            join transactions t on t.id = rtsn.tr_sales_transactions_id
                            where rtsn.tr_sales_transactions_id = tr_sales_netto.tr_sales_transactions_id
                            and t.deleted_at is null
                        ), 0)
                        as total_return
                    ')
                )
                ->join('transactions as t', 't.id', '=', 'tr_sales_netto.tr_sales_transactions_id')
                ->doesntHave('closingCashierDetails')
                ->whereNotIn('tr_sales_netto.status', $filter)
                ->leftJoin('payments_tr_sales_netto as ptp', 'ptp.tr_sales_netto_transactions_id', '=', 'tr_sales_netto.tr_sales_transactions_id')
                ->leftJoin('payments as p', 'p.id', '=', 'ptp.payments_id')
                ->where(function($q) use ($invoice_date) {
                    if(!empty($invoice_date)) {
                        $q->where('tr_sales_netto.date', $invoice_date);
                    }
                })
                ->where(function($q) use ($payment_date) {
                    if(!empty($payment_date)) {
                        $q->where('p.date', $payment_date);
                    }
                })
                ->where(function($q) use ($customer_id) {
                    if(!empty($customer_id)) {
                        $q->where('tr_sales_netto.customers_persons_id', $customer_id);
                    }
                })
                ->get()->sum('total_return');
                // return response()->json($total_return);
                $value = collect([
                    'value' => $total_sales - $total_return
                ]);
                $data = $value->merge($data);
            } else {
                $data = ModelTrSalesNetto::readDataByPagination($row, $sort);
            }
        }
        return response($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Create data
    |--------------------------------------------------------------------------
    */
    protected function create(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Generate Code
            |--------------------------------------------------------------------------
            */
            $tbl_name = "transactions";
            $tbl_primary_key = "id";
            $tbl_init_code = "306";
            $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);

            if ($request->segment(1) == 'api') {
                $tr_sales_netto = json_decode($request->trSalesNetto);
            } else {
                $tr_sales_netto = json_decode(json_encode($request->trSalesNetto));
            }

            /*
            |--------------------------------------------------------------------------
            | Insert data at table transactions
            |--------------------------------------------------------------------------
            */
            TransactionsCtrl::createData($tr_sales_netto, $transactions_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_netto
            |--------------------------------------------------------------------------
            */
            $m_tr_sales = new ModelTrSales();
            $m_tr_sales->transactions_id = $transactions_id;
            $m_tr_sales->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_netto
            |--------------------------------------------------------------------------
            */
            $m_tr_sales_netto = new ModelTrSalesNetto();
            $m_tr_sales_netto->tr_sales_transactions_id = $transactions_id;
            $m_tr_sales_netto->payment = $tr_sales_netto->payment;
            $m_tr_sales_netto->balance = $tr_sales_netto->balance;
            $m_tr_sales_netto->ppn = $tr_sales_netto->ppn;
            $m_tr_sales_netto->date = date('Y-m-d');
            $m_tr_sales_netto->status = $tr_sales_netto->status;
            $m_tr_sales_netto->customers_persons_id = $tr_sales_netto->customers_persons_id;
            $m_tr_sales_netto->save();

            if (count($tr_sales_netto->details) > 0) {
                /*
                |--------------------------------------------------------------------------
                | Insert data at table tr_sales_netto_details
                |--------------------------------------------------------------------------
                */
                foreach ($tr_sales_netto->details as $key => $val) {
                    $m_tr_sales_netto_details = new ModelTrSalesNettoDetails();
                    $m_tr_sales_netto_details->price = $val->price;
                    $m_tr_sales_netto_details->qty = $val->qty;
                    $m_tr_sales_netto_details->qty_in_tablet = $val->qty;
                    $m_tr_sales_netto_details->ppn = $val->ppn;
                    $m_tr_sales_netto_details->discount = $request->segment(1) == 'api' ? ($val->discount/100) : $val->discount;
                    $m_tr_sales_netto_details->subtotal = ($val->price * $val->qty);
                    $m_tr_sales_netto_details->tr_sales_netto_id = $transactions_id;
                    $m_tr_sales_netto_details->unit = "Tablet";
                    $m_tr_sales_netto_details->medicines_items_id = $val->medicines_items_id;
                    $m_tr_sales_netto_details->save();

                    ModelMedicines::where('items_id', $val->medicines_items_id)->decrement('qty_total', $val->qty);
                }
            } else {
                return response()->json($resp = ["status" => 0, "result" => "error", "msg" => RespMessages::failEmptyCart(), "id" => ""]);
            }

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $transactions_id]);
        } catch (\Exception $e) {

            TelegramBot::sendError($e->getMessage());

            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => ""]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Create return
    |--------------------------------------------------------------------------
    */
    protected function createReturn(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Generate Code
            |--------------------------------------------------------------------------
            */
            $tbl_name = "returns";
            $tbl_primary_key = "id";
            $tbl_init_code = "401";
            $returns_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            if ($request->segment(1) == 'api') {
                $tr_sales_netto = json_decode($request->trSalesNetto);
            } else {
                $tr_sales_netto = json_decode(json_encode($request->trSalesNetto));
            }
            // return response()->json($request->trSalesNetto);die;
            /*
            |--------------------------------------------------------------------------
            | Insert data at table return
            |--------------------------------------------------------------------------
            */
            $m_return = new ModelReturns();
            $m_return->id = $returns_id;
            $m_return->codes_id = $tbl_init_code;
            $m_return->users_persons_id = Auth::user()->persons_id;
            $m_return->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table return_tr_sales_netto or return_tr_sales
            |--------------------------------------------------------------------------
            */
            $m_return_sales_netto = new ModelReturnTrSalesNetto;
            $m_return_sales_netto->returns_id = $returns_id;
            $m_return_sales_netto->qty = $tr_sales_netto->qty;
            $m_return_sales_netto->tr_sales_transactions_id = $tr_sales_netto->transactions_id;
            $m_return_sales_netto->tr_sales_details_id = $tr_sales_netto->tr_sales_details_id;
            $m_return_sales_netto->save();

            ModelMedicines::where('items_id', $tr_sales_netto->items_id)->increment('qty_total', $tr_sales_netto->qty);

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $returns_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "id" => $returns_id]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Create adjustments
    |--------------------------------------------------------------------------
    */
    protected function createAdjustments(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Generate Code
            |--------------------------------------------------------------------------
            */
            $tbl_name = "stock_adjustments";
            $tbl_primary_key = "id";
            $tbl_init_code = "403";
            $stock_adjustments_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            if ($request->segment(1) == 'api') {
                $tr_sales_netto = json_decode($request->trSalesNetto);
            } else {
                $tr_sales_netto = json_decode(json_encode($request->trSalesNetto));
            }
            // return response()->json(["id" => $stock_adjustments_id, "data" => $request->trSalesNetto]);die;
            /*
            |--------------------------------------------------------------------------
            | Insert data at table stock_adjustments
            |--------------------------------------------------------------------------
            */
            $m_adjustments = new ModelStockAdjustments();
            $m_adjustments->id = $stock_adjustments_id;
            $m_adjustments->codes_id = $tbl_init_code;
            $m_adjustments->users_persons_id = Auth::user()->persons_id;
            $m_adjustments->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table stock_adjustments_tr_sales_netto
            |--------------------------------------------------------------------------
            */
            $m_adjustments_sales_netto = new ModelStockAdjustmentsTrSalesNetto;
            $m_adjustments_sales_netto->stock_adjustments_id = $stock_adjustments_id;
            $m_adjustments_sales_netto->qty = $tr_sales_netto->qty;
            $m_adjustments_sales_netto->tr_sales_transactions_id = $tr_sales_netto->transactions_id;
            $m_adjustments_sales_netto->tr_sales_details_id = $tr_sales_netto->tr_sales_details_id;
            $m_adjustments_sales_netto->save();

            ModelMedicines::where('items_id', $tr_sales_netto->items_id)->increment('qty_total', $tr_sales_netto->qty);

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $stock_adjustments_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "id" => $stock_adjustments_id]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Create repayment
    |--------------------------------------------------------------------------
    */
    protected function createRepayment(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Generate Code
            |--------------------------------------------------------------------------
            */
            $tbl_name = "payments";
            $tbl_primary_key = "id";
            $tbl_init_code = "601";
            $payments_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            
            if ($request->segment(1) == 'api') {
                $tr_sales_netto = json_decode($request->trSalesNetto);
            } else {
                $tr_sales_netto = json_decode(json_encode($request->trSalesNetto));
            }
            // return response()->json($request->trSalesNetto);die;
            /*
            |--------------------------------------------------------------------------
            | Insert data at table payments
            |--------------------------------------------------------------------------
            */
            $m_payment = new ModelPayments();
            $m_payment->id = $payments_id;
            $m_payment->total = $tr_sales_netto->grand_total;
            $m_payment->date = date('Y-m-d', strtotime($tr_sales_netto->date));
            $m_payment->users_persons_id = Auth::user()->persons_id;
            $m_payment->codes_id = $tbl_init_code;
            $m_payment->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table payment_tr_sales_nettos
            |--------------------------------------------------------------------------
            */
            $m_payment_tr_sales_netto = new ModelPaymentsTrSalesNetto();
            $m_payment_tr_sales_netto->payments_id = $payments_id;
            $m_payment_tr_sales_netto->fee_display = $tr_sales_netto->fee_display;
            $m_payment_tr_sales_netto->tr_sales_netto_transactions_id = $tr_sales_netto->transactions_id;
            $m_payment_tr_sales_netto->save();
            /*
            |--------------------------------------------------------------------------
            | Update data at table tr_sales_netto
            |--------------------------------------------------------------------------
            */
            $m_tr_sales_netto = ModelTrSalesNetto::find($tr_sales_netto->transactions_id);
            $m_tr_sales_netto->status = 'paid';
            $m_tr_sales_netto->save();
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $payments_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "id" => $payments_id]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    protected function readDataById($id)
    {
        $tr_sales_netto = ModelTrSalesNetto::find($id);
        $details = [];

        $tr_sales_netto_total = 0;
        $tr_sales_netto_grand_total = 0;
        
        foreach ($tr_sales_netto->trSalesNettoDetails as $k_detail => $val_detail) {
            $total = $val_detail->price * $val_detail->qty;
            $discount = $total * $val_detail->discount;
            $subtotal = ($total - $discount) + (($total - $discount) * $val_detail->ppn);

            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['ppn'] = $val_detail->ppn;
            $details[$k_detail]['ppn_price'] = $val_detail->ppn > 0.00 ? strval($val_detail->price * $val_detail->ppn) : '0.00';
            $details[$k_detail]['subtotal'] = $subtotal;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['qty_return'] = $val_detail->qty_return;
            $details[$k_detail]['qty_adjustments'] = $val_detail->qty_adjustments;
            $details[$k_detail]['return_details'] = $val_detail->returnDetail;

            $tr_sales_netto_total += $subtotal;
        }

        $tr_sales_netto->id = $tr_sales_netto->trSales->transactions_id;
        $tr_sales_netto->customer = $tr_sales_netto->customer;
        $tr_sales_netto->total = $tr_sales_netto_total;
        $tr_sales_netto->discount = $tr_sales_netto->trSales->transaction->discount;
        $tr_sales_netto->grandTotal = ($tr_sales_netto_total - $tr_sales_netto->trSales->transaction->discount) + (($tr_sales_netto_total - $tr_sales_netto->trSales->transaction->discount) * $tr_sales_netto->ppn);
        $tr_sales_netto->payment = $tr_sales_netto->payment;
        $tr_sales_netto->balance = $tr_sales_netto->balance;
        $tr_sales_netto->codesId = $tr_sales_netto->trSales->transaction->codes_id;
        $tr_sales_netto->date = date('d-m-Y', strtotime($tr_sales_netto->trSales->transaction->created_at));
        $tr_sales_netto->time = date('H:i:s', strtotime($tr_sales_netto->trSales->transaction->created_at));
        $tr_sales_netto->cashierId = $tr_sales_netto->trSales->transaction->cashier->id;
        $tr_sales_netto->cashierName = Str::title($tr_sales_netto->trSales->transaction->cashier->name);
        $tr_sales_netto->qtyTotal = count($details);
        $tr_sales_netto->details = $details;

        $data = [
            "tr_sales_netto" => $tr_sales_netto,
        ];

        return response()->json($data);
    }

    public function testInvoice() {
        $tbl_name = "transactions";
        $tbl_primary_key = "id";
        $tbl_init_code = "306";
        $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        echo $transactions_id;die;
    }
}
