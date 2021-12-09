<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Libraries\RespMessages;
use App\Libraries\TelegramBot;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelTrSales;
use App\Model\ModelTrSalesRecipe;
use App\Model\ModelTrSalesRecipeMedicines;
use App\Model\ModelTrSalesRecipeDetails;
use App\Model\ModelMedicines;
use DB;

class TrSalesRecipeCtrl extends TransactionsCtrl
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
                $data = ModelTrSalesRecipe::select(
                    'tr_sales_recipe.payment',
                    'tr_sales_recipe.balance',
                    'tr_sales_recipe.patient',
                    'tr_sales_recipe.date',
                    'tr_sales_recipe.address',
                    'tr_sales_recipe.doctor',
                    'tr_sales_recipe.tr_sales_transactions_id',
                    't.id',
                    't.total',
                    't.discount',
                    't.grand_total',
                    't.created_at',
                    't.updated_at',
                    't.codes_id',
                    't.users_persons_id',
                    'p.name as cashier_name',
                    DB::raw('("Resep") as title')
                )
                ->with('trSales', 'trSalesRecipeMedicines')
                ->join('transactions as t', 't.id', '=', 'tr_sales_recipe.tr_sales_transactions_id')
                ->join('persons as p', 'p.id', '=', 't.users_persons_id')
                ->doesntHave('closingCashierDetails')
                ->where('tr_sales_recipe.tr_sales_transactions_id', 'LIKE', "%{$search}%")
                ->orderBy('tr_sales_recipe.tr_sales_transactions_id', 'desc')
                ->paginate(10);
            } else {
                $data = ModelTrSalesRecipe::readDataBySearch($search, $row, $sort);
            }
        } else {
            if ($request->segment(1) == 'api') {
                $data = ModelTrSalesRecipe::select(
                    'tr_sales_recipe.payment',
                    'tr_sales_recipe.balance',
                    'tr_sales_recipe.patient',
                    'tr_sales_recipe.date',
                    'tr_sales_recipe.address',
                    'tr_sales_recipe.doctor',
                    'tr_sales_recipe.tr_sales_transactions_id',
                    't.id',
                    't.total',
                    't.discount',
                    't.grand_total',
                    't.created_at',
                    't.updated_at',
                    't.codes_id',
                    't.users_persons_id',
                    'p.name as cashier_name',
                    DB::raw('("Resep") as title')
                )
                ->with('trSales', 'trSalesRecipeMedicines')
                ->join('transactions as t', 't.id', '=', 'tr_sales_recipe.tr_sales_transactions_id')
                ->join('persons as p', 'p.id', '=', 't.users_persons_id')
                ->doesntHave('closingCashierDetails')
                // ->leftJoin('closing_cashier_details', 'closing_cashier_details.tr_sales_id', '=', 'tr_sales_recipe.tr_sales_transactions_id')
                // ->leftJoin('closing_cashiers', 'closing_cashiers.id', '=', 'closing_cashier_details.closing_cashiers_id')
                // ->whereNull('closing_cashier_details.closing_cashiers_id')
                ->orderBy('tr_sales_recipe.tr_sales_transactions_id', 'desc')
                ->paginate(10);
            } else {
                $data = ModelTrSalesRecipe::readDataByPagination($row, $sort);
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
            $tbl_init_code = "303";
            $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);

            if ($request->segment(1) == 'api') {
                $tr_sales_recipe = json_decode($request->trSalesRecipe);
            } else {
                $tr_sales_recipe = json_decode(json_encode($request->trSalesRecipe));
            }
            
            /*
            |--------------------------------------------------------------------------
            | Insert data at table transactions
            |--------------------------------------------------------------------------
            */
            TransactionsCtrl::createData($tr_sales_recipe, $transactions_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_recipe
            |--------------------------------------------------------------------------
            */
            $m_tr_sales = new ModelTrSales();
            $m_tr_sales->transactions_id = $transactions_id;
            $m_tr_sales->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_recipe
            |--------------------------------------------------------------------------
            */
            $m_tr_sales_recipe = new ModelTrSalesRecipe();
            $m_tr_sales_recipe->tr_sales_transactions_id = $transactions_id;
            $m_tr_sales_recipe->payment = $tr_sales_recipe->payment;
            $m_tr_sales_recipe->balance = $tr_sales_recipe->balance;
            $m_tr_sales_recipe->patient = Str::upper($tr_sales_recipe->patient);
            $m_tr_sales_recipe->address = Str::upper($tr_sales_recipe->address);
            $m_tr_sales_recipe->date = date('Y-m-d', strtotime($tr_sales_recipe->date));
            $m_tr_sales_recipe->doctor = Str::upper($tr_sales_recipe->doctor);
            $m_tr_sales_recipe->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table tr_sales_recipe_details
            |--------------------------------------------------------------------------
            */
            if (count($tr_sales_recipe->medicineRecipe) > 0) {
                foreach ($tr_sales_recipe->medicineRecipe as $key => $val) {
                    $m_tr_sales_recipe_medicines = new ModelTrSalesRecipeMedicines();
                    $m_tr_sales_recipe_medicines->name = $val->medicineRecipeName;
                    $m_tr_sales_recipe_medicines->price = $val->price;
                    $m_tr_sales_recipe_medicines->tuslah = $val->tuslah;
                    $m_tr_sales_recipe_medicines->qty = $val->qty;
                    $m_tr_sales_recipe_medicines->subtotal = ($val->price * $val->qty) + $val->tuslah;
                    $m_tr_sales_recipe_medicines->status = $val->status_mix;
                    $m_tr_sales_recipe_medicines->tr_sales_recipe_id = $transactions_id;
                    $m_tr_sales_recipe_medicines->save();

                    foreach ($val->details as $key => $v_detail) {
                        $m_tr_sales_recipe_details = new ModelTrSalesRecipeDetails();
                        $m_tr_sales_recipe_details->price = $v_detail->price;
                        $m_tr_sales_recipe_details->qty = $val->status_mix == 'nonmix' ? $val->qty : $v_detail->qty;
                        $m_tr_sales_recipe_details->qty_in_tablet = $val->status_mix == 'nonmix' ? $val->qty : $v_detail->qty;
                        $m_tr_sales_recipe_details->discount = $v_detail->discount;
                        $m_tr_sales_recipe_details->subtotal = $val->status_mix == 'nonmix' ?  ($val->price * $v_detail->qty) : ($v_detail->price * $v_detail->qty);
                        $m_tr_sales_recipe_details->tr_sales_recipe_medicines_id = $m_tr_sales_recipe_medicines->id;
                        $m_tr_sales_recipe_details->unit = "Tablet";
                        $m_tr_sales_recipe_details->medicines_items_id = $v_detail->medicines_items_id;
                        $m_tr_sales_recipe_details->save();
                        
                        if($val->status_mix == 'nonmix') {
                            ModelMedicines::where('items_id', $v_detail->medicines_items_id)->decrement('qty_total', $val->qty);
                        } else {
                            ModelMedicines::where('items_id', $v_detail->medicines_items_id)->decrement('qty_total', $v_detail->qty);
                        }
                    }
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
    | Read data by id
    |--------------------------------------------------------------------------
    */
    protected function readDataById($id)
    {
        $tr_sales_recipe = ModelTrSalesRecipe::find($id);
        $details = [];
        
        foreach ($tr_sales_recipe->trSalesRecipeMedicines as $key => $val) {
            $ingredients = [];
            foreach ($val->trSalesRecipeDetails as $k_detail => $val_detail) {
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
            $details[$key]['discount'] = $val->discount == null ? 0 : $val->discount;
            $details[$key]['subtotal'] = $val->subtotal;
            $details[$key]['status'] = $val->status;
            $details[$key]['trSalesRecipeId'] = $val->tr_sales_recipe_id;
            $details[$key]['ingredients'] = $ingredients;
        }

        $tr_sales_recipe->id = $tr_sales_recipe->trSales->transactions_id;
        $tr_sales_recipe->invoiceNumber = $tr_sales_recipe->trSales->invoice_number;
        $tr_sales_recipe->total = $tr_sales_recipe->trSales->transaction->total;
        $tr_sales_recipe->discount = $tr_sales_recipe->trSales->transaction->discount;
        $tr_sales_recipe->grandTotal = $tr_sales_recipe->trSales->transaction->grand_total;
        $tr_sales_recipe->payment = $tr_sales_recipe->payment;
        $tr_sales_recipe->balance = $tr_sales_recipe->balance;
        $tr_sales_recipe->codesId = $tr_sales_recipe->trSales->transaction->codes_id;
        $tr_sales_recipe->date = date('d-m-Y', strtotime($tr_sales_recipe->trSales->transaction->created_at));
        $tr_sales_recipe->time = date('H:i:s', strtotime($tr_sales_recipe->trSales->transaction->created_at));
        $tr_sales_recipe->cashierId = $tr_sales_recipe->trSales->transaction->cashier->id;
        $tr_sales_recipe->cashierName = Str::title($tr_sales_recipe->trSales->transaction->cashier->name);
        $tr_sales_recipe->qtyTotal = count($details);
        $tr_sales_recipe->details = $details;

        $data = [
            "tr_sales_recipe" => $tr_sales_recipe
        ];
        return response()->json($data);
    }

    public function testInvoice() {
        $tbl_name = "transactions";
        $tbl_primary_key = "id";
        $tbl_init_code = "303";
        $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        echo $transactions_id;die;
    }
}
