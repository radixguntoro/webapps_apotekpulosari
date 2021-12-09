<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Libraries\RespMessages;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelTrSales;
use App\Model\ModelTrSalesMix;
use App\Model\ModelTrSalesMixMedicines;
use App\Model\ModelTrSalesMixDetails;
use App\Model\ModelMedicines;
use DB;

class TrSalesMixCtrl extends TransactionsCtrl
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
                $data = ModelTrSalesMix::with('trSales', 'trSalesMixMedicines')
                    ->leftJoin('closing_cashier_details', 'closing_cashier_details.tr_sales_id', '=', 'tr_sales_mix.tr_sales_transactions_id')
                    ->leftJoin('closing_cashiers', 'closing_cashiers.id', '=', 'closing_cashier_details.closing_cashiers_id')
                    ->whereNull('closing_cashier_details.closing_cashiers_id')
                    ->where('tr_sales_mix.tr_sales_transactions_id', 'LIKE', "%{$search}%")
                    ->orderBy('tr_sales_mix.tr_sales_transactions_id', 'desc')
                    ->paginate(10);
            } else {
                $data = ModelTrSalesMix::readDataBySearch($search, $row, $sort);
            }
        } else {
            if ($request->segment(1) == 'api') {
                $data = ModelTrSalesMix::with('trSales', 'trSalesMixMedicines')
                    ->leftJoin('closing_cashier_details', 'closing_cashier_details.tr_sales_id', '=', 'tr_sales_mix.tr_sales_transactions_id')
                    ->leftJoin('closing_cashiers', 'closing_cashiers.id', '=', 'closing_cashier_details.closing_cashiers_id')
                    ->whereNull('closing_cashier_details.closing_cashiers_id')
                    ->orderBy('tr_sales_mix.tr_sales_transactions_id', 'desc')
                    ->paginate(10);
            } else {
                $data = ModelTrSalesMix::readDataByPagination($row, $sort);
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
            $tbl_init_code = "302";
            $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);

            if ($request->segment(1) == 'api') {
                $tr_sales_mix = json_decode($request->trSalesMix);
            } else {
                $tr_sales_mix = json_decode(json_encode($request->trSalesMix));
            }

            /*
            |--------------------------------------------------------------------------
            | Insert data at table transactions
            |--------------------------------------------------------------------------
            */
            TransactionsCtrl::createData($tr_sales_mix, $transactions_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_mix
            |--------------------------------------------------------------------------
            */
            $m_tr_sales = new ModelTrSales();
            $m_tr_sales->transactions_id = $transactions_id;
            $m_tr_sales->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_mix
            |--------------------------------------------------------------------------
            */
            $m_tr_sales_mix = new ModelTrSalesMix();
            $m_tr_sales_mix->tr_sales_transactions_id = $transactions_id;
            $m_tr_sales_mix->payment = $tr_sales_mix->payment;
            $m_tr_sales_mix->balance = $tr_sales_mix->balance;
            $m_tr_sales_mix->fee_pharmacist = $tr_sales_mix->fee_pharmacist;
            $m_tr_sales_mix->patient = Str::upper($tr_sales_mix->patient);
            $m_tr_sales_mix->weight = $tr_sales_mix->weight;
            $m_tr_sales_mix->age = $tr_sales_mix->age;
            $m_tr_sales_mix->save();

            if (count($tr_sales_mix->medicineMix) > 0) {
                /*
                |--------------------------------------------------------------------------
                | Insert data at table tr_sales_mix_details
                |--------------------------------------------------------------------------
                */
                foreach ($tr_sales_mix->medicineMix as $key => $val) {
                    $m_tr_sales_mix_medicines = new ModelTrSalesMixMedicines();
                    $m_tr_sales_mix_medicines->name = $val->medicineMixName;
                    $m_tr_sales_mix_medicines->price = $val->price;
                    $m_tr_sales_mix_medicines->tuslah = $val->tuslah;
                    $m_tr_sales_mix_medicines->qty = $val->qty;
                    $m_tr_sales_mix_medicines->subtotal = (($val->price + $val->tuslah) * $val->qty);
                    $m_tr_sales_mix_medicines->tr_sales_mix_id = $transactions_id;
                    $m_tr_sales_mix_medicines->save();

                    foreach ($val->details as $key => $v_detail) {
                        $m_tr_sales_mix_details = new ModelTrSalesMixDetails();
                        $m_tr_sales_mix_details->price = $v_detail->price;
                        $m_tr_sales_mix_details->qty = $v_detail->qty;
                        $m_tr_sales_mix_details->qty_in_tablet = $v_detail->qty;
                        $m_tr_sales_mix_details->discount = $v_detail->discount;
                        $m_tr_sales_mix_details->subtotal = ($v_detail->price * $v_detail->qty);
                        $m_tr_sales_mix_details->tr_sales_mix_medicines_id = $m_tr_sales_mix_medicines->id;
                        $m_tr_sales_mix_details->unit = "Tablet";
                        $m_tr_sales_mix_details->medicines_items_id = $v_detail->medicines_items_id;
                        $m_tr_sales_mix_details->save();
        
                        ModelMedicines::where('items_id', $v_detail->medicines_items_id)->decrement('qty_total', $v_detail->qty);
                    }
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
        $tr_sales_mix = ModelTrSalesMix::find($id);
        $details = [];

        foreach ($tr_sales_mix->trSalesMixMedicines as $key => $val) {
            $ingredients = [];
            foreach ($val->trSalesMixDetails as $k_detail => $val_detail) {
                $ingredients[$k_detail]['id'] = $val_detail->id;
                $ingredients[$k_detail]['price'] = $val_detail->price;
                $ingredients[$k_detail]['qty'] = $val_detail->qty;
                $ingredients[$k_detail]['discount'] = $val_detail->discount;
                $ingredients[$k_detail]['subtotal'] = $val_detail->subtotal;
                $ingredients[$k_detail]['medicineId'] = $val_detail->medicines_items_id;
                $ingredients[$k_detail]['medicineName'] = $val_detail->items->name;
            }

            $details[$key]['id'] = $val->id;
            $details[$key]['medicineName'] = $val->name;
            $details[$key]['price'] = $val->price;
            $details[$key]['tuslah'] = $val->tuslah;
            $details[$key]['qty'] = $val->qty;
            $details[$key]['subtotal'] = $val->subtotal;
            $details[$key]['trSalesMixId'] = $val->tr_sales_mix_id;
            $details[$key]['ingredients'] = $ingredients;
        }

        $tr_sales_mix->id = $tr_sales_mix->trSales->transactions_id;
        $tr_sales_mix->total = $tr_sales_mix->trSales->transaction->total;
        $tr_sales_mix->discount = $tr_sales_mix->trSales->transaction->discount;
        $tr_sales_mix->grandTotal = $tr_sales_mix->trSales->transaction->grand_total;
        $tr_sales_mix->payment = $tr_sales_mix->payment;
        $tr_sales_mix->balance = $tr_sales_mix->balance;
        $tr_sales_mix->codesId = $tr_sales_mix->trSales->transaction->codes_id;
        $tr_sales_mix->date = date('d-m-Y', strtotime($tr_sales_mix->trSales->transaction->created_at));
        $tr_sales_mix->time = date('H:i:s', strtotime($tr_sales_mix->trSales->transaction->created_at));
        $tr_sales_mix->cashierId = $tr_sales_mix->trSales->transaction->cashier->id;
        $tr_sales_mix->cashierName = Str::title($tr_sales_mix->trSales->transaction->cashier->name);
        $tr_sales_mix->qtyTotal = count($details);
        $tr_sales_mix->details = $details;

        $data = [
            "tr_sales_mix" => $tr_sales_mix
        ];
        return response()->json($data);
    }

    public function testInvoice() {
        $tbl_name = "transactions";
        $tbl_primary_key = "id";
        $tbl_init_code = "302";
        $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        echo $transactions_id;die;
    }
}
