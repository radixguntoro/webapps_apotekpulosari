<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Libraries\RespMessages;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelTrSales;
use App\Model\ModelTrSalesLab;
use App\Model\ModelTrSalesLabDetails;
use App\Model\ModelMedicines;
use DB;

class TrSalesLabCtrl extends TransactionsCtrl
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

        if ($search) {
            if ($request->segment(1) == 'api') {
                $data = ModelTrSalesLab::select(
                        'tr_sales_lab.payment',
                        'tr_sales_lab.balance',
                        'tr_sales_lab.patient',
                        'tr_sales_lab.age',
                        'tr_sales_lab.glucosa_fasting',
                        'tr_sales_lab.glucosa_2hours_pp',
                        'tr_sales_lab.glucosa_random',
                        'tr_sales_lab.uric_acid',
                        'tr_sales_lab.cholesterol',
                        'tr_sales_lab.blood_pressure',
                        'tr_sales_lab.tr_sales_transactions_id',
                        't.id',
                        't.total',
                        't.discount',
                        't.grand_total',
                        't.created_at',
                        't.updated_at',
                        't.codes_id',
                        't.users_persons_id',
                        'p.name as cashier_name',
                        DB::raw('("Lab") as title')
                    )
                    ->with('trSales', 'trSalesLabDetails')
                    ->join('transactions as t', 't.id', '=', 'tr_sales_regular.tr_sales_transactions_id')
                    ->join('persons as p', 'p.id', '=', 't.users_persons_id')
                    ->doesntHave('closingCashierDetails')
                    ->where('tr_sales_lab.tr_sales_transactions_id', 'LIKE', "%{$search}%")
                    ->orderBy('tr_sales_lab.tr_sales_transactions_id', 'desc')
                    ->paginate(10);
            } else {
                $data = ModelTrSalesLab::readDataBySearch($search, $row, $sort);
            }
        } else {
            if ($request->segment(1) == 'api') {
                $data = ModelTrSalesLab::select(
                    'tr_sales_lab.payment',
                    'tr_sales_lab.balance',
                    'tr_sales_lab.patient',
                    'tr_sales_lab.age',
                    'tr_sales_lab.glucosa_fasting',
                    'tr_sales_lab.glucosa_2hours_pp',
                    'tr_sales_lab.glucosa_random',
                    'tr_sales_lab.uric_acid',
                    'tr_sales_lab.cholesterol',
                    'tr_sales_lab.blood_pressure',
                    'tr_sales_lab.tr_sales_transactions_id',
                    't.id',
                    't.total',
                    't.discount',
                    't.grand_total',
                    't.created_at',
                    't.updated_at',
                    't.codes_id',
                    't.users_persons_id',
                    'p.name as cashier_name',
                    DB::raw('("Lab") as title')
                )
                ->with('trSales', 'trSalesLabDetails')
                ->join('transactions as t', 't.id', '=', 'tr_sales_lab.tr_sales_transactions_id')
                ->join('persons as p', 'p.id', '=', 't.users_persons_id')
                ->doesntHave('closingCashierDetails')
                ->orderBy('tr_sales_lab.tr_sales_transactions_id', 'desc')
                ->paginate(10);
            } else {
                $data = ModelTrSalesLab::readDataByPagination($row, $sort);
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
            $tbl_init_code = "304";
            $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);

            if ($request->segment(1) == 'api') {
                $tr_sales_lab = json_decode($request->trSalesLab);
            } else {
                $tr_sales_lab = json_decode(json_encode($request->trSalesLab));
            }

            /*
            |--------------------------------------------------------------------------
            | Insert data at table transactions
            |--------------------------------------------------------------------------
            */
            TransactionsCtrl::createData($tr_sales_lab, $transactions_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_lab
            |--------------------------------------------------------------------------
            */
            $m_tr_sales = new ModelTrSales();
            $m_tr_sales->transactions_id = $transactions_id;
            $m_tr_sales->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_lab
            |--------------------------------------------------------------------------
            */
            $m_tr_sales_lab = new ModelTrSalesLab();
            $m_tr_sales_lab->tr_sales_transactions_id = $transactions_id;
            $m_tr_sales_lab->payment = $tr_sales_lab->payment;
            $m_tr_sales_lab->balance = $tr_sales_lab->balance;
            $m_tr_sales_lab->patient = Str::upper($tr_sales_lab->patient);
            $m_tr_sales_lab->age = $tr_sales_lab->age;
            $m_tr_sales_lab->glucosa_fasting = $tr_sales_lab->glucosa_fasting;
            $m_tr_sales_lab->glucosa_2hours_pp = $tr_sales_lab->glucosa_2hours_pp;
            $m_tr_sales_lab->glucosa_random = $tr_sales_lab->glucosa_random;
            $m_tr_sales_lab->uric_acid = $tr_sales_lab->uric_acid;
            $m_tr_sales_lab->cholesterol = $tr_sales_lab->cholesterol;
            $m_tr_sales_lab->blood_pressure = $tr_sales_lab->blood_pressure;
            $m_tr_sales_lab->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_lab_details
            |--------------------------------------------------------------------------
            */
            if (count($tr_sales_lab->details) > 0) {
                foreach ($tr_sales_lab->details as $key => $val) {
                    $m_tr_sales_lab_details = new ModelTrSalesLabDetails();
                    $m_tr_sales_lab_details->price = $val->price;
                    $m_tr_sales_lab_details->qty = $val->qty;
                    $m_tr_sales_lab_details->qty_in_tablet = $val->qty;
                    $m_tr_sales_lab_details->discount = $val->discount;
                    $m_tr_sales_lab_details->subtotal = ($val->price * $val->qty);
                    $m_tr_sales_lab_details->tr_sales_lab_id = $transactions_id;
                    $m_tr_sales_lab_details->unit = "Tablet";
                    $m_tr_sales_lab_details->medicines_items_id = $val->medicines_items_id;
                    $m_tr_sales_lab_details->save();

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
    | Read data by id
    |--------------------------------------------------------------------------
    */
    protected function readDataById($id)
    {
        $tr_sales_lab = ModelTrSalesLab::find($id);
        $details = [];
        
        foreach ($tr_sales_lab->trSalesLabDetails as $k_detail => $val_detail) {
            $details[$k_detail]['id'] = $val_detail->id;
            $details[$k_detail]['price'] = $val_detail->price;
            $details[$k_detail]['qty'] = $val_detail->qty;
            $details[$k_detail]['discount'] = $val_detail->discount;
            $details[$k_detail]['subtotal'] = $val_detail->subtotal;
            $details[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
            $details[$k_detail]['medicineName'] = $val_detail->medicine->item->name;
        }

        $tr_sales_lab->id = $tr_sales_lab->trSales->transactions_id;
        $tr_sales_lab->total = $tr_sales_lab->trSales->transaction->total;
        $tr_sales_lab->discount = $tr_sales_lab->trSales->transaction->discount;
        $tr_sales_lab->grandTotal = $tr_sales_lab->trSales->transaction->grand_total;
        $tr_sales_lab->payment = $tr_sales_lab->payment;
        $tr_sales_lab->balance = $tr_sales_lab->balance;
        $tr_sales_lab->codesId = $tr_sales_lab->trSales->transaction->codes_id;
        $tr_sales_lab->date = date('d-m-Y', strtotime($tr_sales_lab->trSales->transaction->created_at));
        $tr_sales_lab->time = date('H:i:s', strtotime($tr_sales_lab->trSales->transaction->created_at));
        $tr_sales_lab->cashierId = $tr_sales_lab->trSales->transaction->cashier->id;
        $tr_sales_lab->cashierName = Str::title($tr_sales_lab->trSales->transaction->cashier->name);
        $tr_sales_lab->qtyTotal = count($details);
        $tr_sales_lab->details = $details;

        $data = [
            "tr_sales_lab" => $tr_sales_lab
        ];

        return response()->json($data);
    }

    public function testInvoice() {
        $tbl_name = "transactions";
        $tbl_primary_key = "id";
        $tbl_init_code = "304";
        $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        echo $transactions_id;die;
    }

    public function closingCashierDetails()
    {
        $data = $this->hasOne(ModelClosingCashierDetails::class, 'tr_sales_id');
        return $data;
    }
}
