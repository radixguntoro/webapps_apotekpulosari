<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelReturns;
use App\Model\ModelReturnTrSales;
use App\Model\ModelMedicines;
use DB;

class ReturnCtrl extends Controller
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
        $filter = explode(',', trim($request->get('filter')));

        if ($search) {
            $data = ModelReturns::with('returnSales', 'cashier')
                ->has('returnSales')
                ->orderBy('returns.created_at', 'desc')
                ->paginate(10);
        } else {
            $data = ModelReturns::with('returnSales', 'cashier')
                ->has('returnSales')
                ->orderBy('returns.created_at', 'desc')
                ->paginate(10);
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
            $tbl_name = "returns";
            $tbl_primary_key = "id";
            $tbl_init_code = "401";
            $returns_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            if ($request->segment(1) == 'api') {
                $return = json_decode($request->return);
            } else {
                $return = json_decode(json_encode($request->return));
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
            $m_return_sales = new ModelReturnTrSales;
            $m_return_sales->returns_id = $returns_id;
            $m_return_sales->price = $return->price;
            $m_return_sales->discount = $return->discount;
            $m_return_sales->qty = $return->qty;
            $m_return_sales->medicines_items_id = $return->medicines_items_id;
            $m_return_sales->save();

            ModelMedicines::where('items_id', $return->medicines_items_id)->increment('qty_total', $return->qty);
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $returns_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "id" => $returns_id]);
            DB::rollback();
        }
    }
}
