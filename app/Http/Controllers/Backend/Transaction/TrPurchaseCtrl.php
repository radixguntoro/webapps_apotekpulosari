<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Libraries\GenerateNumber;
use App\Libraries\RespMessages;
use App\Libraries\TelegramBot;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelTransactions;
use App\Model\ModelTrPurchases;
use App\Model\ModelTrPurchaseDetails;
use App\Model\ModelPayments;
use App\Model\ModelPaymentsTrPurchases;
use App\Model\ModelLogs;
use App\Model\ModelLogTransactions;
use App\Model\ModelLogPurchaseDetails;
use App\Model\ModelMedicines;
use App\Model\ModelMedicineDetails;
use App\Model\ModelReturns;
use App\Model\ModelReturnTrPurchases;
use App\Model\ModelStockAdjustments;
use App\Model\ModelStockAdjustmentsTrPurchases;
use DB;

class TrPurchaseCtrl extends TransactionsCtrl
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
        $supplier_id = $request->get('supplier_id');
        $filter = explode(',', trim($request->get('filter')));

        if ($search) {
            if ($request->segment(1) == 'api') {
                $data = ModelTrPurchases::select(
                        "tr_purchases.transactions_id as transactions_id",
                        "tr_purchases.invoice_number as invoice_number",
                        "tr_purchases.ppn as ppn",
                        "tr_purchases.date as date",
                        "tr_purchases.status as status",
                        "tr_purchases.suppliers_persons_id as suppliers_persons_id",
                        "t.id as id",
                        "t.total as total",
                        "t.discount as discount",
                        "t.grand_total as grand_total",
                        "t.created_at as created_at",
                        "t.updated_at as updated_at",
                        "t.deleted_at as deleted_at",
                        "t.codes_id as codes_id",
                        "t.users_persons_id as users_persons_id",
                        'ptp.payments_id as payment_id',
                        'p.date as payment_date',
                        DB::raw('
                            COALESCE(
                            (
                                select SUM((tpd.price - (tpd.price * tpd.discount)) * rtp.qty) as total_return 
                                from returns_tr_purchases rtp
                                join tr_purchase_details tpd on tpd.id = rtp.tr_purchase_details_id
                                join transactions t on t.id = rtp.tr_purchases_transactions_id
                                where rtp.tr_purchases_transactions_id = tr_purchases.transactions_id
                                and t.deleted_at is null
                            ), 0)
                            as total_return
                        ')
                    )
                    ->with('transaction', 'supplier', 'returns', 'details')
                    ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
                    ->join('persons', 'persons.id', '=', 'tr_purchases.suppliers_persons_id')
                    ->leftJoin('payments_tr_purchases as ptp', 'ptp.tr_purchases_transactions_id', '=', 'tr_purchases.transactions_id')
                    ->leftJoin('payments as p', 'p.id', '=', 'ptp.payments_id')
                    ->where('persons.name', "LIKE", "%{$search}%")
                    ->where(function($q) use ($invoice_date) {
                        if(!empty($invoice_date)) {
                            $q->where('tr_purchases.date', $invoice_date);
                        }
                    })
                    ->where(function($q) use ($payment_date) {
                        if(!empty($payment_date)) {
                            $q->where('p.date', $payment_date);
                        }
                    })
                    ->whereNotIn('tr_purchases.status', $filter)
                    ->whereNull('t.deleted_at')
                    ->whereDate('tr_purchases.date', '>', '2020-12-25')
                    ->orWhere("tr_purchases.invoice_number", "LIKE", "%{$search}%")
                    ->where(function($q) use ($invoice_date) {
                        if(!empty($invoice_date)) {
                            $q->where('tr_purchases.date', $invoice_date);
                        }
                    })
                    ->where(function($q) use ($payment_date) {
                        if(!empty($payment_date)) {
                            $q->where('p.date', $payment_date);
                        }
                    })
                    ->whereNotIn('tr_purchases.status', $filter)
                    ->whereNull('t.deleted_at')
                    ->whereDate('tr_purchases.date', '>', '2020-12-25')
                    ->orWhere('tr_purchases.transactions_id', 'LIKE', "%{$search}%")
                    ->where(function($q) use ($invoice_date) {
                        if(!empty($invoice_date)) {
                            $q->where('tr_purchases.date', $invoice_date);
                        }
                    })
                    ->where(function($q) use ($payment_date) {
                        if(!empty($payment_date)) {
                            $q->where('p.date', $payment_date);
                        }
                    })
                    ->whereNotIn('tr_purchases.status', $filter)
                    ->whereNull('t.deleted_at')
                    ->whereDate('tr_purchases.date', '>', '2020-12-25')
                    ->orderBy('tr_purchases.date', 'desc')
                    ->orderBy('tr_purchases.invoice_number', 'desc')
                    ->paginate(10);
                    
                    $value = collect([
                        'value' => ModelTrPurchases::join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
                            ->join('persons', 'persons.id', '=', 'tr_purchases.suppliers_persons_id')
                            ->leftJoin('payments_tr_purchases as ptp', 'ptp.tr_purchases_transactions_id', '=', 'tr_purchases.transactions_id')
                            ->leftJoin('payments as p', 'p.id', '=', 'ptp.payments_id')
                            ->where('persons.name', "LIKE", "%{$search}%")
                            ->where(function($q) use ($invoice_date) {
                                if(!empty($invoice_date)) {
                                    $q->where('tr_purchases.date', $invoice_date);
                                }
                            })
                            ->where(function($q) use ($payment_date) {
                                if(!empty($payment_date)) {
                                    $q->where('p.date', $payment_date);
                                }
                            })
                            ->whereNotIn('tr_purchases.status', $filter)
                            ->whereNull('t.deleted_at')
                            ->whereDate('tr_purchases.date', '>', '2020-12-25')
                            ->orWhere("tr_purchases.invoice_number", "LIKE", "%{$search}%")
                            ->where(function($q) use ($invoice_date) {
                                if(!empty($invoice_date)) {
                                    $q->where('tr_purchases.date', $invoice_date);
                                }
                            })
                            ->where(function($q) use ($payment_date) {
                                if(!empty($payment_date)) {
                                    $q->where('p.date', $payment_date);
                                }
                            })
                            ->whereNotIn('tr_purchases.status', $filter)
                            ->whereNull('t.deleted_at')
                            ->whereDate('tr_purchases.date', '>', '2020-12-25')
                            ->orWhere('tr_purchases.transactions_id', 'LIKE', "%{$search}%")
                            ->where(function($q) use ($invoice_date) {
                                if(!empty($invoice_date)) {
                                    $q->where('tr_purchases.date', $invoice_date);
                                }
                            })
                            ->where(function($q) use ($payment_date) {
                                if(!empty($payment_date)) {
                                    $q->where('p.date', $payment_date);
                                }
                            })
                            ->whereNotIn('tr_purchases.status', $filter)
                            ->whereNull('t.deleted_at')
                            ->whereDate('tr_purchases.date', '>', '2020-12-25')
                            ->sum('t.grand_total')
                    ]);

                    $data = $value->merge($data);
            } else {
                $data = ModelTrPurchases::readDataBySearch($search, $row, $sort, $filter);
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
                $order_by = count($is_paid) > 0 ? 'p.created_at' : 'tr_purchases.date';
                $sort = count($is_paid) > 0 ? 'desc' : 'desc';

                $data = ModelTrPurchases::select(
                        'tr_purchases.transactions_id as transactions_id',
                        'tr_purchases.invoice_number as invoice_number',
                        'tr_purchases.ppn as ppn',
                        'tr_purchases.date as date',
                        'tr_purchases.suppliers_persons_id as suppliers_persons_id',
                        'tr_purchases.status as status',
                        't.id as id',
                        't.total as total',
                        't.discount as discount',
                        't.grand_total as grand_total',
                        't.created_at as created_at',
                        't.updated_at as updated_at',
                        't.deleted_at as deleted_at',
                        't.codes_id as codes_id',
                        't.users_persons_id as users_persons_id',
                        'ptp.payments_id as payment_id',
                        'p.date as payment_date',
                        DB::raw('
                            COALESCE(
                            (
                                select SUM((tpd.price - (tpd.price * tpd.discount)) * rtp.qty) as total_return 
                                from returns_tr_purchases rtp
                                join tr_purchase_details tpd on tpd.id = rtp.tr_purchase_details_id
                                join transactions t on t.id = rtp.tr_purchases_transactions_id
                                where rtp.tr_purchases_transactions_id = tr_purchases.transactions_id
                                and t.deleted_at is null
                            ), 0)
                            as total_return
                        ')
                    )
                    ->with('transaction', 'supplier', 'returns', 'details')
                    ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
                    ->leftJoin('payments_tr_purchases as ptp', 'ptp.tr_purchases_transactions_id', '=', 'tr_purchases.transactions_id')
                    ->leftJoin('payments as p', 'p.id', '=', 'ptp.payments_id')
                    ->whereNotIn('tr_purchases.status', $filter)
                    ->where(function($q) use ($invoice_date) {
                        if(!empty($invoice_date)) {
                            $q->where('tr_purchases.date', $invoice_date);
                        }
                    })
                    ->where(function($q) use ($payment_date) {
                        if(!empty($payment_date)) {
                            $q->where('p.date', $payment_date);
                        }
                    })
                    ->where(function($q) use ($supplier_id) {
                        if(!empty($supplier_id)) {
                            $q->where('tr_purchases.suppliers_persons_id', $supplier_id);
                        }
                    })
                    ->whereNull('t.deleted_at')
                    ->whereDate('tr_purchases.date', '>', '2020-12-25')
                    ->orderBy($order_by, $sort)
                    ->orderBy('tr_purchases.invoice_number', 'desc')
                    ->paginate(10);

                $value = collect([
                    'value' => ModelTrPurchases::join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
                        ->leftJoin('payments_tr_purchases as ptp', 'ptp.tr_purchases_transactions_id', '=', 'tr_purchases.transactions_id')
                        ->leftJoin('payments as p', 'p.id', '=', 'ptp.payments_id')
                        ->whereNotIn('tr_purchases.status', $filter)
                        ->where(function($q) use ($invoice_date) {
                            if(!empty($invoice_date)) {
                                $q->where('tr_purchases.date', $invoice_date);
                            }
                        })
                        ->where(function($q) use ($payment_date) {
                            if(!empty($payment_date)) {
                                $q->where('p.date', $payment_date);
                            }
                        })
                        ->where(function($q) use ($supplier_id) {
                            if(!empty($supplier_id)) {
                                $q->where('tr_purchases.suppliers_persons_id', $supplier_id);
                            }
                        })
                        ->whereNull('t.deleted_at')
                        ->whereDate('tr_purchases.date', '>', '2020-12-25')
                        ->sum('t.grand_total')
                ]);
                $data = $value->merge($data);

            } else {
                $data = ModelTrPurchases::readDataByPagination($row, $sort, $filter);
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
            $tbl_init_code = "305";
            $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            
            if ($request->segment(1) == 'api') {
                $tr_purchase = json_decode($request->trPurchase);
            } else {
                $tr_purchase = json_decode(json_encode($request->trPurchase));
            }
            // return response()->json($tr_purchase);die;
            /*
            |--------------------------------------------------------------------------
            | Insert data at table transactions
            |--------------------------------------------------------------------------
            */
            TransactionsCtrl::createData($tr_purchase, $transactions_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_purchase
            |--------------------------------------------------------------------------
            */
            $m_tr_purchase = new ModelTrPurchases();
            $m_tr_purchase->transactions_id = $transactions_id;
            $m_tr_purchase->suppliers_persons_id = $tr_purchase->suppliers_persons_id;
            $m_tr_purchase->invoice_number = $tr_purchase->invoice_number;
            $m_tr_purchase->ppn = $tr_purchase->ppn;
            $m_tr_purchase->status = $tr_purchase->status;
            $m_tr_purchase->date = date('Y-m-d', strtotime($tr_purchase->date));
            $m_tr_purchase->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_purchase_details
            |--------------------------------------------------------------------------
            */
            foreach ($tr_purchase->details as $key => $val) {
                $m_tr_purchase_detail = new ModelTrPurchaseDetails();
                $m_tr_purchase_detail->price_purchase_old = $val->price_purchase_old;
                $m_tr_purchase_detail->price_sell_old = $val->price_sell_old;
                $m_tr_purchase_detail->price = $val->price;
                $m_tr_purchase_detail->qty = $val->qty;
                $m_tr_purchase_detail->qty_in_tablet = $val->qty;
                $m_tr_purchase_detail->discount = $request->segment(1) == 'api' ? ($val->discount/100) : $val->discount;
                $m_tr_purchase_detail->subtotal = $val->price * $val->qty;
                $m_tr_purchase_detail->tr_purchases_transactions_id = $transactions_id;
                $m_tr_purchase_detail->unit = "Tablet";
                $m_tr_purchase_detail->medicines_items_id = $val->medicines_items_id;
                $m_tr_purchase_detail->save();

                ModelMedicines::where('items_id', $val->medicines_items_id)->increment('qty_total', $val->qty);
                
                $m_medicine_details = ModelMedicineDetails::find($val->id);
                $m_medicine_details->price_sell = $val->tablet_price_sell;
                $m_medicine_details->price_purchase = $val->price;
                $m_medicine_details->save();
            }

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success',  "msg" => RespMessages::successCreate(), "id" => $transactions_id]);
        } catch (\Exception $e) {

            TelegramBot::sendError($e->getMessage());

            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => $transactions_id]);
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
                $tr_purchase = json_decode($request->trPurchase);
            } else {
                $tr_purchase = json_decode(json_encode($request->trPurchase));
            }
            // return response()->json($request->trPurchase);die;
            /*
            |--------------------------------------------------------------------------
            | Insert data at table payments
            |--------------------------------------------------------------------------
            */
            $m_payment = new ModelPayments();
            $m_payment->id = $payments_id;
            $m_payment->total = $tr_purchase->grand_total;
            $m_payment->date = date('Y-m-d', strtotime($tr_purchase->date));
            $m_payment->users_persons_id = Auth::user()->persons_id;
            $m_payment->codes_id = $tbl_init_code;
            $m_payment->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table payment_tr_purchases
            |--------------------------------------------------------------------------
            */
            $m_payment_tr_purchase = new ModelPaymentsTrPurchases();
            $m_payment_tr_purchase->payments_id = $payments_id;
            $m_payment_tr_purchase->tr_purchases_transactions_id = $tr_purchase->transactions_id;
            $m_payment_tr_purchase->save();
            /*
            |--------------------------------------------------------------------------
            | Update data at table tr_purchase
            |--------------------------------------------------------------------------
            */
            $m_tr_purchase = ModelTrPurchases::find($tr_purchase->transactions_id);
            $m_tr_purchase->status = 'paid';
            $m_tr_purchase->save();

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $payments_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "id" => $payments_id]);
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
                $tr_purchase = json_decode($request->trPurchase);
            } else {
                $tr_purchase = json_decode(json_encode($request->trPurchase));
            }
            // return response()->json($request->trPurchase);die;
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
            | Insert data at table return_tr_purchases or return_tr_sales
            |--------------------------------------------------------------------------
            */
            $m_return_purchase = new ModelReturnTrPurchases;
            $m_return_purchase->returns_id = $returns_id;
            $m_return_purchase->qty = $tr_purchase->qty;
            $m_return_purchase->tr_purchases_transactions_id = $tr_purchase->transactions_id;
            $m_return_purchase->tr_purchase_details_id = $tr_purchase->tr_purchase_details_id;
            $m_return_purchase->save();

            ModelMedicines::where('items_id', $tr_purchase->items_id)->decrement('qty_total', $tr_purchase->qty);
            
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
                $tr_purchase = json_decode($request->trPurchase);
            } else {
                $tr_purchase = json_decode(json_encode($request->trPurchase));
            }
            // return response()->json(["id" => $stock_adjustments_id, "data" => $request->trPurchase]);die;
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
            | Insert data at table stock_adjustments_tr_purchases
            |--------------------------------------------------------------------------
            */
            $m_adjustments_purchase = new ModelStockAdjustmentsTrPurchases;
            $m_adjustments_purchase->stock_adjustments_id = $stock_adjustments_id;
            $m_adjustments_purchase->qty = $tr_purchase->qty;
            $m_adjustments_purchase->tr_purchases_transactions_id = $tr_purchase->transactions_id;
            $m_adjustments_purchase->tr_purchase_details_id = $tr_purchase->tr_purchase_details_id;
            $m_adjustments_purchase->save();

            ModelMedicines::where('items_id', $tr_purchase->items_id)->increment('qty_total', $tr_purchase->qty);
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $stock_adjustments_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "id" => $stock_adjustments_id]);
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
        $tr_purchases = ModelTrPurchases::find($id);
        $details = [];

        $tr_purchases_total = 0;
        $tr_purchases_grand_total = 0;
        
        foreach ($tr_purchases->trPurchaseDetails as $k_detail => $val_detail) {
            $total = $val_detail->price * $val_detail->qty;
            $discount = $total * $val_detail->discount;
            $subtotal = ($total - $discount) + (($total - $discount) * $val_detail->ppn);

            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = $subtotal;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
            $details[$k_detail]['qty_return'] = $val_detail->qty_return;
            $details[$k_detail]['qty_adjustments'] = $val_detail->qty_adjustments;
            $details[$k_detail]['return_details'] = $val_detail->returnDetail;

            $tr_purchases_total += $subtotal;
        }

        $tr_purchases->id = $tr_purchases->transaction->id;
        $tr_purchases->total = $tr_purchases_total;
        $tr_purchases->discount = $tr_purchases->transaction->discount;
        $tr_purchases->ppn_price = (($tr_purchases_total - $tr_purchases->transaction->discount) * $tr_purchases->ppn);
        $tr_purchases->grandTotal = ($tr_purchases_total - $tr_purchases->transaction->discount) + (($tr_purchases_total - $tr_purchases->transaction->discount) * $tr_purchases->ppn);
        $tr_purchases->codesId = $tr_purchases->transaction->codes_id;
        $tr_purchases->date = date('d-m-Y', strtotime($tr_purchases->transaction->created_at));
        $tr_purchases->time = date('H:i:s', strtotime($tr_purchases->transaction->created_at));
        $tr_purchases->cashierId = $tr_purchases->transaction->cashier->id;
        $tr_purchases->cashierName = Str::title($tr_purchases->transaction->cashier->name);
        $tr_purchases->supplier = $tr_purchases->supplier->name;
        $tr_purchases->qtyTotal = count($details);
        $tr_purchases->details = $details;

        $data = [
            "tr_purchase" => $tr_purchases
        ];
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data medicine details
    |--------------------------------------------------------------------------
    */
    protected function readDataDetailByMedicineId($id)
    {
        $data = ModelTrPurchaseDetails::with('medicine', 'trPurchase')->where('medicines_items_id', $id)->orderBy('id', 'desc')->limit(25)->get();
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read exist invoice
    |--------------------------------------------------------------------------
    */
    protected function readExistInvoice($id)
    {
        $data = ModelTrPurchases::where('invoice_number', $id)
                ->join('transactions', 'transactions.id', '=', 'tr_purchases.transactions_id')
                ->whereNull('transactions.deleted_at')
                ->get();
                
        if(count($data) > 0) {
            $resp = true;
        } else {
            $resp = false;
        }
        return response()->json($resp);
    }
    /*
    |--------------------------------------------------------------------------
    | Set Status
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Request data
        |--------------------------------------------------------------------------
        */
        $tr_purchase = json_decode(json_encode($request->trPurchase));
        /*
        |--------------------------------------------------------------------------
        | Update data at table tr_purchases
        |--------------------------------------------------------------------------
        */
        foreach ($tr_purchase as $key => $val) {
            $m_tr_purchase = ModelTrPurchases::find($val);
            $m_tr_purchase->status = 'paid';
            $m_tr_purchase->save();
        }
        return response()->json($resp = ["status" => 1, "result" => 'success']);
    }
    /*
    |--------------------------------------------------------------------------
    | Update data invoice date
    |--------------------------------------------------------------------------
    */
    protected function updateInvoiceDate(Request $request)
    {
        try {
            DB::beginTransaction();

            if ($request->segment(1) == 'api') {
                $tr_purchase = json_decode($request->trPurchase);
                $transactions_id = $tr_purchase->transactions_id;
            } else {
                $tr_purchase = json_decode(json_encode($request->trPurchase));
            }

            $m_tr_purchase = ModelTrPurchases::find($tr_purchase->transactions_id);
            $m_tr_purchase->date = date('Y-m-d', strtotime($tr_purchase->date));
            $m_tr_purchase->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Mengubah data pembelian",
                "transactions_id" => $transactions_id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "tr_purchase_updated";

            HistoriesCtrl::createData(json_encode($action), $initial);
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $transactions_id]);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Update data cart
    |--------------------------------------------------------------------------
    */
    protected function updateData(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($request->segment(1) == 'api') {
                $tr_purchase = json_decode($request->trPurchase);
                $transactions_id = $tr_purchase->id;
            } else {
                $tr_purchase = json_decode(json_encode($request->trPurchase));
            }
            /*
            |--------------------------------------------------------------------------
            | Update data at table transactions
            |--------------------------------------------------------------------------
            */
            $m_tr = ModelTransactions::find($transactions_id);
            $m_tr->total = $tr_purchase->total;
            $m_tr->discount = $tr_purchase->discount;
            $m_tr->grand_total = $tr_purchase->grand_total;
            $m_tr->save();
            /*
            |--------------------------------------------------------------------------
            | Update data at table tr_purchases
            |--------------------------------------------------------------------------
            */
            $m_tr_purchase = ModelTrPurchases::find($transactions_id);
            $m_tr_purchase->ppn = $tr_purchase->ppn;
            $m_tr_purchase->status = $tr_purchase->status;
            $m_tr_purchase->save();
            /*
            |--------------------------------------------------------------------------
            | insert data at table logs
            |--------------------------------------------------------------------------
            */
            $m_log = new ModelLogs;
            $m_log->users_persons_id = Auth::user()->persons_id;
            $m_log->save();
            /*
            |--------------------------------------------------------------------------
            | insert data at table log_transactions
            |--------------------------------------------------------------------------
            */
            $m_log_transaction = new ModelLogTransactions;
            $m_log_transaction->logs_id = $m_log->id;
            $m_log_transaction->transactions_id = $transactions_id;
            $m_log_transaction->save();
            /*
            |--------------------------------------------------------------------------
            | Update data at table tr_purchase_details
            |--------------------------------------------------------------------------
            */
            foreach ($tr_purchase->details_old as $key => $val) {
                $m_log_purchase_detail = new ModelLogPurchaseDetails();
                $m_log_purchase_detail->price_purchase_old = $val->price_purchase_old;
                $m_log_purchase_detail->price_sell_old = $val->price_sell_old;
                $m_log_purchase_detail->price = $val->price;
                $m_log_purchase_detail->qty = $val->qty;
                $m_log_purchase_detail->qty_in_tablet = $val->qty;
                $m_log_purchase_detail->discount = $request->segment(1) == 'api' ? ($val->discount/100) : $val->discount;
                $m_log_purchase_detail->subtotal = ($val->price * $val->qty);
                $m_log_purchase_detail->unit = "Tablet";
                $m_log_purchase_detail->log_transactions_id = $m_log->id;
                $m_log_purchase_detail->medicines_items_id = $val->medicines_items_id;
                $m_log_purchase_detail->save();
            }
            /*
            |--------------------------------------------------------------------------
            | Update data at table tr_purchase_details
            |--------------------------------------------------------------------------
            */
            foreach ($tr_purchase->details as $key => $val) {
                $m_tr_purchase_detail = ModelTrPurchaseDetails::find($val->id);
                $m_tr_purchase_detail->price_purchase_old = $val->price_purchase_old;
                $m_tr_purchase_detail->price_sell_old = $val->price_sell_old;
                $m_tr_purchase_detail->price = $val->price;
                $m_tr_purchase_detail->qty = $val->qty;
                $m_tr_purchase_detail->qty_in_tablet = $val->qty;
                $m_tr_purchase_detail->discount = $request->segment(1) == 'api' ? ($val->discount/100) : $val->discount;
                $m_tr_purchase_detail->subtotal = $val->price * $val->qty;
                $m_tr_purchase_detail->tr_purchases_transactions_id = $transactions_id;
                $m_tr_purchase_detail->unit = "Tablet";
                $m_tr_purchase_detail->medicines_items_id = $val->medicines_items_id;
                $m_tr_purchase_detail->save();

                ModelMedicines::where('items_id', $val->medicines_items_id)->increment('qty_total', $val->qty_diff);

                $m_medicine_details = ModelMedicineDetails::find($val->medicines_detail_id);
                $m_medicine_details->price_sell = $val->tablet_price_sell;
                $m_medicine_details->price_purchase = $val->price;
                $m_medicine_details->save();
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Mengubah data pembelian",
                "transactions_id" => $transactions_id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "tr_purchase_updated";

            HistoriesCtrl::createData(json_encode($action), $initial);
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $transactions_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 1, "result" => $e->getMessage(), "id" => $transactions_id]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data
    |--------------------------------------------------------------------------
    */
    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Request data
            |--------------------------------------------------------------------------
            */
            if ($request->segment(1) == 'api') {
                $tr_purchase = json_decode($request->trPurchase);
            } else {
                $tr_purchase = json_decode(json_encode($request->trPurchase));
            }
            /*
            |--------------------------------------------------------------------------
            | insert data at table logs
            |--------------------------------------------------------------------------
            */
            $m_log = new ModelLogs;
            $m_log->users_persons_id = Auth::user()->persons_id;
            $m_log->save();
            /*
            |--------------------------------------------------------------------------
            | insert data at table log_transactions
            |--------------------------------------------------------------------------
            */
            $m_log_transaction = new ModelLogTransactions;
            $m_log_transaction->logs_id = $m_log->id;
            $m_log_transaction->transactions_id = $tr_purchase->transactions_id;
            $m_log_transaction->save();
            /*
            |--------------------------------------------------------------------------
            | insert data at table log_purchase_detail
            |--------------------------------------------------------------------------
            */
            foreach ($tr_purchase->details as $key => $val) {
                $m_log_purchase_detail = new ModelLogPurchaseDetails();
                $m_log_purchase_detail->price_purchase_old = $val->price_purchase_old;
                $m_log_purchase_detail->price_sell_old = $val->price_sell_old;
                $m_log_purchase_detail->price = $val->price;
                $m_log_purchase_detail->qty = $val->qty;
                $m_log_purchase_detail->qty_in_tablet = $val->qty;
                $m_log_purchase_detail->discount = $request->segment(1) == 'api' ? ($val->discount/100) : $val->discount;
                $m_log_purchase_detail->subtotal = ($val->price * $val->qty);
                $m_log_purchase_detail->unit = "Tablet";
                $m_log_purchase_detail->log_transactions_id = $m_log->id;
                $m_log_purchase_detail->medicines_items_id = $val->medicines_items_id;
                $m_log_purchase_detail->save();

                ModelMedicines::where('items_id', $val->medicines_items_id)->decrement('qty_total', $val->qty);
                
                $m_medicine_details = ModelMedicineDetails::find($val->medicine->medicine_details->id);
                $m_medicine_details->price_sell = $val->price_sell_old;
                $m_medicine_details->price_purchase = $val->price_purchase_old;
                $m_medicine_details->save();
            }
            /*
            |--------------------------------------------------------------------------
            | Delete data at table transactions
            |--------------------------------------------------------------------------
            */
            ModelTransactions::find($tr_purchase->transactions_id)->delete();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menghapus data pembelian",
                "transactions_id" => $tr_purchase->transactions_id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "tr_purchase_deleted";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();

            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $tr_purchase->transactions_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => response()->json($e->getMessage()), "id" => $tr_purchase->transactions_id]);
            DB::rollback();
        }
    }

    public function testInvoice() {
        $tbl_name = "returns";
        $tbl_primary_key = "id";
        $tbl_init_code = "401";
        $returns_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        echo $returns_id;
    }
}
