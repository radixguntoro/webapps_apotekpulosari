<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Libraries\RespMessages;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelTrSales;
use App\Model\ModelTrSalesCredit;
use App\Model\ModelTrSalesCreditDetails;
use App\Model\ModelPayments;
use App\Model\ModelPaymentsTrSalesCredit;
use App\Model\ModelMedicines;
use DB;

class TrSalesCreditCtrl extends TransactionsCtrl
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
        $date = $request->get('date');
        $filter = explode(',', trim($request->get('filter')));

        if ($search) {
            if ($request->segment(1) == 'api') {
                $data = ModelTrSalesCredit::select(
                    'tr_sales_credit.payment as payment',
                    'tr_sales_credit.balance as balance',
                    'tr_sales_credit.status as status',
                    'tr_sales_credit.customers_persons_id as customers_persons_id',
                    'tr_sales_credit.tr_sales_transactions_id as tr_sales_transactions_id',
                    't.id',
                    't.total',
                    't.discount',
                    't.grand_total',
                    't.created_at',
                    't.updated_at',
                    't.codes_id',
                    't.users_persons_id',
                    'p.name as cashier_name',
                    DB::raw('("Kredit") as title'),
                    'ptp.payments_id as payment_id',
                    'pay.date as payment_date'
                )
                ->with('trSales', 'trSalesCreditDetails', 'customer')
                ->doesntHave('closingCashierDetails')
                ->leftJoin('payments_tr_sales_credit as ptp', 'ptp.tr_sales_credit_transactions_id', '=', 'tr_sales_credit.tr_sales_transactions_id')
                ->leftJoin('payments as pay', 'pay.id', '=', 'ptp.payments_id')
                ->join('transactions as t', 't.id', '=', 'tr_sales_credit.tr_sales_transactions_id')
                ->join('persons', 'persons.id', '=', 'tr_sales_credit.customers_persons_id')
                ->join('persons as p', 'p.id', '=', 't.users_persons_id')
                ->where('tr_sales_credit.tr_sales_transactions_id', 'LIKE', "%{$search}%")
                ->whereNotIn('tr_sales_credit.status', $filter)
                ->whereNull('t.deleted_at')
                ->orWhere('persons.name', "LIKE", "%{$search}%")
                ->whereNotIn('tr_sales_credit.status', $filter)
                ->whereNull('t.deleted_at')
                ->orderBy('tr_sales_credit.tr_sales_transactions_id', 'desc')
                ->paginate(10);
            } else {
                $data = ModelTrSalesCredit::readDataBySearch($search, $row, $sort);
            }
        } else {
            if ($request->segment(1) == 'api') {
                $data = ModelTrSalesCredit::select(
                    'tr_sales_credit.payment as payment',
                    'tr_sales_credit.balance as balance',
                    'tr_sales_credit.status as status',
                    'tr_sales_credit.customers_persons_id as customers_persons_id',
                    'tr_sales_credit.tr_sales_transactions_id as tr_sales_transactions_id',
                    't.id',
                    't.total',
                    't.discount',
                    't.grand_total',
                    't.created_at',
                    't.updated_at',
                    't.codes_id',
                    't.users_persons_id',
                    'p.name as cashier_name',
                    DB::raw('("Kredit") as title'),
                    'ptp.payments_id as payment_id',
                    'pay.date as payment_date'
                )
                ->with('trSales', 'trSalesCreditDetails', 'customer')
                ->doesntHave('closingCashierDetails')
                ->leftJoin('payments_tr_sales_credit as ptp', 'ptp.tr_sales_credit_transactions_id', '=', 'tr_sales_credit.tr_sales_transactions_id')
                ->leftJoin('payments as pay', 'pay.id', '=', 'ptp.payments_id')
                ->join('transactions as t', 't.id', '=', 'tr_sales_credit.tr_sales_transactions_id')
                ->join('persons', 'persons.id', '=', 'tr_sales_credit.customers_persons_id')
                ->join('persons as p', 'p.id', '=', 't.users_persons_id')
                ->whereNotIn('tr_sales_credit.status', $filter)
                ->orderBy('tr_sales_credit.tr_sales_transactions_id', 'desc')
                ->paginate(10);

                $value = collect([
                    'value' => ModelTrSalesCredit::join('transactions as t', 't.id', '=', 'tr_sales_credit.tr_sales_transactions_id')
                        ->doesntHave('closingCashierDetails')
                        ->leftJoin('payments_tr_sales_credit as ptp', 'ptp.tr_sales_credit_transactions_id', '=', 'tr_sales_credit.tr_sales_transactions_id')
                        ->leftJoin('payments as p', 'p.id', '=', 'ptp.payments_id')
                        ->whereNotIn('tr_sales_credit.status', $filter)
                        ->sum('t.grand_total')
                ]);
                $data = $value->merge($data);
            } else {
                $data = ModelTrSalesCredit::readDataByPagination($row, $sort);
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
            $tbl_init_code = "307";
            $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);

            if ($request->segment(1) == 'api') {
                $tr_sales_credit = json_decode($request->trSalesCredit);
            } else {
                $tr_sales_credit = json_decode(json_encode($request->trSalesCredit));
            }

            /*
            |--------------------------------------------------------------------------
            | Insert data at table transactions
            |--------------------------------------------------------------------------
            */
            TransactionsCtrl::createData($tr_sales_credit, $transactions_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_credit
            |--------------------------------------------------------------------------
            */
            $m_tr_sales = new ModelTrSales();
            $m_tr_sales->transactions_id = $transactions_id;
            $m_tr_sales->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_credit
            |--------------------------------------------------------------------------
            */
            $m_tr_sales_credit = new ModelTrSalesCredit();
            $m_tr_sales_credit->tr_sales_transactions_id = $transactions_id;
            $m_tr_sales_credit->payment = $tr_sales_credit->payment;
            $m_tr_sales_credit->balance = $tr_sales_credit->balance;
            $m_tr_sales_credit->status = $tr_sales_credit->status;
            $m_tr_sales_credit->customers_persons_id = $tr_sales_credit->customers_persons_id;
            $m_tr_sales_credit->save();

            if (count($tr_sales_credit->details) > 0) {
                /*
                |--------------------------------------------------------------------------
                | Insert data at table tr_sales_credit_details
                |--------------------------------------------------------------------------
                */
                foreach ($tr_sales_credit->details as $key => $val) {
                    $m_tr_sales_credit_details = new ModelTrSalesCreditDetails();
                    $m_tr_sales_credit_details->price = $val->price;
                    $m_tr_sales_credit_details->qty = $val->qty;
                    $m_tr_sales_credit_details->qty_in_tablet = $val->qty;
                    $m_tr_sales_credit_details->discount = $val->discount;
                    $m_tr_sales_credit_details->subtotal = ($val->price * $val->qty);
                    $m_tr_sales_credit_details->tr_sales_credit_id = $transactions_id;
                    $m_tr_sales_credit_details->unit = "Tablet";
                    $m_tr_sales_credit_details->medicines_items_id = $val->medicines_items_id;
                    $m_tr_sales_credit_details->save();

                    ModelMedicines::where('items_id', $val->medicines_items_id)->decrement('qty_total', $val->qty);
                }
            } else {
                return response()->json($resp = ["status" => 0, "result" => "error", "msg" => RespMessages::failEmptyCart(), "id" => ""]);
            }
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $transactions_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => ""]);
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
                $tr_sales_credit = json_decode($request->trSalesCredit);
            } else {
                $tr_sales_credit = json_decode(json_encode($request->trSalesCredit));
            }
            // return response()->json($request->trSalesCredit);die;
            /*
            |--------------------------------------------------------------------------
            | Insert data at table payments
            |--------------------------------------------------------------------------
            */
            $m_payment = new ModelPayments();
            $m_payment->id = $payments_id;
            $m_payment->total = $tr_sales_credit->grand_total;
            $m_payment->date = date('Y-m-d', strtotime($tr_sales_credit->date));
            $m_payment->users_persons_id = Auth::user()->persons_id;
            $m_payment->codes_id = $tbl_init_code;
            $m_payment->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table payment_tr_sales_credits
            |--------------------------------------------------------------------------
            */
            $m_payment_tr_sales_credit = new ModelPaymentsTrSalesCredit();
            $m_payment_tr_sales_credit->payments_id = $payments_id;
            $m_payment_tr_sales_credit->tr_sales_credit_transactions_id = $tr_sales_credit->transactions_id;
            $m_payment_tr_sales_credit->save();
            /*
            |--------------------------------------------------------------------------
            | Update data at table tr_sales_credit
            |--------------------------------------------------------------------------
            */
            $m_tr_sales_credit = ModelTrSalesCredit::find($tr_sales_credit->transactions_id);
            $m_tr_sales_credit->status = 'paid';
            $m_tr_sales_credit->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Melunasi data penjualan kredit",
                "payments_id" => $payments_id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "payment_tr_sales_credit_created";

            HistoriesCtrl::createData(json_encode($action), $initial);
            
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
        $tr_sales_credit = ModelTrSalesCredit::find($id);
        $details = [];
        
        foreach ($tr_sales_credit->trSalesCreditDetails as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = $val_detail->subtotal;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
        }

        $tr_sales_credit->id = $tr_sales_credit->trSales->transactions_id;
        $tr_sales_credit->customer = $tr_sales_credit->customer;
        $tr_sales_credit->total = $tr_sales_credit->trSales->transaction->total;
        $tr_sales_credit->discount = $tr_sales_credit->trSales->transaction->discount;
        $tr_sales_credit->grandTotal = $tr_sales_credit->trSales->transaction->grand_total;
        $tr_sales_credit->payment = $tr_sales_credit->payment;
        $tr_sales_credit->balance = $tr_sales_credit->balance;
        $tr_sales_credit->codesId = $tr_sales_credit->trSales->transaction->codes_id;
        $tr_sales_credit->date = date('d-m-Y', strtotime($tr_sales_credit->trSales->transaction->created_at));
        $tr_sales_credit->time = date('H:i:s', strtotime($tr_sales_credit->trSales->transaction->created_at));
        $tr_sales_credit->cashierId = $tr_sales_credit->trSales->transaction->cashier->id;
        $tr_sales_credit->cashierName = Str::title($tr_sales_credit->trSales->transaction->cashier->name);
        $tr_sales_credit->qtyTotal = count($details);
        $tr_sales_credit->details = $details;

        $data = [
            "tr_sales_credit" => $tr_sales_credit
        ];

        return response()->json($data);
    }

    public function testInvoice() {
        $tbl_name = "transactions";
        $tbl_primary_key = "id";
        $tbl_init_code = "307";
        $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        echo $transactions_id;die;
    }
}
