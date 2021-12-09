<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Libraries\RespMessages;
use App\Libraries\TelegramBot;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelTrSales;
use App\Model\ModelTrSalesRegular;
use App\Model\ModelTrSalesRegularDetails;
use App\Model\ModelMedicines;
use DB;

class TrSalesRegularCtrl extends TransactionsCtrl
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
        $date_yesterday = date('Y-m-d',strtotime(date('Y-m-d') . "-1 days"));
        $date_today = date('Y-m-d');

        if ($search) {
            if ($request->segment(1) == 'api') {
                $data = ModelTrSalesRegular::select(
                        'tr_sales_regular.payment',
                        'tr_sales_regular.balance',
                        'tr_sales_regular.tr_sales_transactions_id',
                        't.id',
                        't.total',
                        't.discount',
                        't.grand_total',
                        't.created_at',
                        't.updated_at',
                        't.codes_id',
                        't.users_persons_id',
                        'p.name as cashier_name',
                        DB::raw('("Reguler") as title')
                    )
                    ->with('trSales', 'trSalesRegularDetails')
                    ->join('transactions as t', 't.id', '=', 'tr_sales_regular.tr_sales_transactions_id')
                    ->join('persons as p', 'p.id', '=', 't.users_persons_id')
                    ->doesntHave('closingCashierDetails')
                    ->whereDate('t.created_at', $date_yesterday)
                    ->where('tr_sales_regular.tr_sales_transactions_id', 'LIKE', "%{$search}%")
                    ->orWhereDate('t.created_at', $date_today)
                    ->where('tr_sales_regular.tr_sales_transactions_id', 'LIKE', "%{$search}%")
                    ->orderBy('tr_sales_regular.tr_sales_transactions_id', 'desc')
                    ->paginate(10);
            } else {
                $data = ModelTrSalesRegular::readDataBySearch($search, $row, $sort);
            }
        } else {
            if ($request->segment(1) == 'api') {
                $data = ModelTrSalesRegular::select(
                        'tr_sales_regular.payment',
                        'tr_sales_regular.balance',
                        'tr_sales_regular.tr_sales_transactions_id',
                        't.id',
                        't.total',
                        't.discount',
                        't.grand_total',
                        't.created_at',
                        't.updated_at',
                        't.codes_id',
                        't.users_persons_id',
                        'p.name as cashier_name',
                        DB::raw('("Reguler") as title')
                    )
                    ->with('trSales', 'trSalesRegularDetails')
                    ->join('transactions as t', 't.id', '=', 'tr_sales_regular.tr_sales_transactions_id')
                    ->join('persons as p', 'p.id', '=', 't.users_persons_id')
                    ->doesntHave('closingCashierDetails')
                    ->orderBy('tr_sales_regular.tr_sales_transactions_id', 'desc')
                    ->paginate(10);
            } else {
                $data = ModelTrSalesRegular::readDataByPagination($row, $sort);
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
            $tbl_init_code = "301";
            $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            
            if ($request->segment(1) == 'api') {
                $tr_sales_regular = json_decode($request->trSalesRegular);
            } else {
                $tr_sales_regular = json_decode(json_encode($request->trSalesRegular));
            }

            // TelegramBot::sendError(json_encode($tr_sales_regular));
            $check_transactions_id = DB::table($tbl_name)->where('id', $transactions_id)->first();

            if ($check_transactions_id) {
                $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
                /*
                |--------------------------------------------------------------------------
                | Insert data at table transactions
                |--------------------------------------------------------------------------
                */
                TransactionsCtrl::createData($tr_sales_regular, $transactions_id, $tbl_init_code);
                /*
                |--------------------------------------------------------------------------
                | Insert data at table tr_sales_regular
                |--------------------------------------------------------------------------
                */
                $m_tr_sales = new ModelTrSales();
                $m_tr_sales->transactions_id = $transactions_id;
                $m_tr_sales->save();
                /*
                |--------------------------------------------------------------------------
                | Insert data at table tr_sales_regular
                |--------------------------------------------------------------------------
                */
                $m_tr_sales_regular = new ModelTrSalesRegular();
                $m_tr_sales_regular->tr_sales_transactions_id = $transactions_id;
                $m_tr_sales_regular->payment = $tr_sales_regular->payment;
                $m_tr_sales_regular->balance = $tr_sales_regular->balance;
                $m_tr_sales_regular->save();
    
                if (count($tr_sales_regular->details) > 0) {
                    /*
                    |--------------------------------------------------------------------------
                    | Insert data at table tr_sales_regular_details
                    |--------------------------------------------------------------------------
                    */
                    foreach ($tr_sales_regular->details as $key => $val) {
                        $m_tr_sales_regular_details = new ModelTrSalesRegularDetails();
                        $m_tr_sales_regular_details->price = $val->price;
                        $m_tr_sales_regular_details->qty = $val->qty;
                        $m_tr_sales_regular_details->qty_in_tablet = $val->qty;
                        // $m_tr_sales_regular_details->discount = $val->discount;
                        $m_tr_sales_regular_details->discount = $request->segment(1) == 'api' ? ($val->discount/100) : $val->discount;
                        $m_tr_sales_regular_details->subtotal = ($val->price * $val->qty);
                        $m_tr_sales_regular_details->tr_sales_regular_id = $transactions_id;
                        $m_tr_sales_regular_details->unit = "Tablet";
                        $m_tr_sales_regular_details->medicines_items_id = $val->medicines_items_id;
                        $m_tr_sales_regular_details->save();
        
                        ModelMedicines::where('items_id', $val->medicines_items_id)->decrement('qty_total', $val->qty);
                    }
                } else {
                    return response()->json($resp = ["status" => 0, "result" => "error", "msg" => RespMessages::failEmptyCart(), "id" => ""]);
                }
                
                DB::commit();
                return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $transactions_id]);
            } else {
                /*
                |--------------------------------------------------------------------------
                | Insert data at table transactions
                |--------------------------------------------------------------------------
                */
                TransactionsCtrl::createData($tr_sales_regular, $transactions_id, $tbl_init_code);
                /*
                |--------------------------------------------------------------------------
                | Insert data at table tr_sales_regular
                |--------------------------------------------------------------------------
                */
                $m_tr_sales = new ModelTrSales();
                $m_tr_sales->transactions_id = $transactions_id;
                $m_tr_sales->save();
                /*
                |--------------------------------------------------------------------------
                | Insert data at table tr_sales_regular
                |--------------------------------------------------------------------------
                */
                $m_tr_sales_regular = new ModelTrSalesRegular();
                $m_tr_sales_regular->tr_sales_transactions_id = $transactions_id;
                $m_tr_sales_regular->payment = $tr_sales_regular->payment;
                $m_tr_sales_regular->balance = $tr_sales_regular->balance;
                $m_tr_sales_regular->save();
    
                if (count($tr_sales_regular->details) > 0) {
                    /*
                    |--------------------------------------------------------------------------
                    | Insert data at table tr_sales_regular_details
                    |--------------------------------------------------------------------------
                    */
                    foreach ($tr_sales_regular->details as $key => $val) {
                        $m_tr_sales_regular_details = new ModelTrSalesRegularDetails();
                        $m_tr_sales_regular_details->price = $val->price;
                        $m_tr_sales_regular_details->qty = $val->qty;
                        $m_tr_sales_regular_details->qty_in_tablet = $val->qty;
                        // $m_tr_sales_regular_details->discount = $val->discount;
                        $m_tr_sales_regular_details->discount = $request->segment(1) == 'api' ? ($val->discount/100) : $val->discount;
                        $m_tr_sales_regular_details->subtotal = ($val->price * $val->qty);
                        $m_tr_sales_regular_details->tr_sales_regular_id = $transactions_id;
                        $m_tr_sales_regular_details->unit = "Tablet";
                        $m_tr_sales_regular_details->medicines_items_id = $val->medicines_items_id;
                        $m_tr_sales_regular_details->save();
        
                        ModelMedicines::where('items_id', $val->medicines_items_id)->decrement('qty_total', $val->qty);
                    }
                } else {
                    return response()->json($resp = ["status" => 0, "result" => "error", "msg" => RespMessages::failEmptyCart(), "id" => ""]);
                }
                
                DB::commit();
                return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $transactions_id]);
            }
        } catch (\Exception $e) {

            TelegramBot::sendError($e->getMessage());

            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => ""]);
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
        $trs_reg_parent = ModelTrSalesRegular::readDataById($id);
        $tr_sales_regular_details = ModelTrSalesRegularDetails::readDataById($id);
        
        $tr_sales_regular['balance'] = $trs_reg_parent->balance;
        $tr_sales_regular['createdAt'] = $trs_reg_parent->createdAt;
        $tr_sales_regular['timeAt'] = $trs_reg_parent->timeAt;
        $tr_sales_regular['discount'] = $trs_reg_parent->discount;
        $tr_sales_regular['grandTotal'] = $trs_reg_parent->grandTotal;
        $tr_sales_regular['id'] = $trs_reg_parent->id;
        $tr_sales_regular['codesId'] = $trs_reg_parent->codesId;
        $tr_sales_regular['payment'] = $trs_reg_parent->payment;
        $tr_sales_regular['total'] = $trs_reg_parent->total;
        $tr_sales_regular['userName'] = $trs_reg_parent->userName;
        $tr_sales_regular['qtyTotal'] = count($tr_sales_regular_details);
        $tr_sales_regular['details'] = $tr_sales_regular_details;

        $data = [
            "tr_sales_regular" => $tr_sales_regular
        ];
        return response()->json($data);
    }

    public function testInvoice() {
        $tbl_name = "transactions";
        $tbl_primary_key = "id";
        $tbl_init_code = "301";
        $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        echo $transactions_id;die;
    }
}
