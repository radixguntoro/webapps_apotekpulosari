<?php

namespace App\Http\Controllers\Backend\Inventory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelTrSales;
use App\Model\ModelStockOpname;
use App\Model\ModelMedicines;
use DB;

class StockOpnameCtrl extends Controller
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
            $data = ModelStockOpname::with('medicine')
            ->orderBy('stock_opnames.id', 'desc')
            ->paginate(10);
        } else {
            $data = ModelStockOpname::with('medicine')
                ->orderBy('stock_opnames.id', 'desc')
                ->paginate(10);
        }
        return response($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public function readMedicineList(Request $request)
    {
        $search = $request->get('search');
        $row = $request->get('row');
        $sort = $request->get('sort');
        $filter = explode(',', trim($request->get('filter')));
        
        if ($search) {
            $data = ModelStockOpname::readDataBySearchMedicine($search, $row, $sort, $filter);
        } else {
            $data = ModelStockOpname::readDataByPaginationMedicine($row, $sort, $filter);
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
            $tbl_name = "stock_opnames";
            $tbl_primary_key = "id";
            $tbl_init_code = "402";
            $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            
            if ($request->segment(1) == 'api') {
                $stock_opname = json_decode($request->stockOpname);
            } else {
                $stock_opname = json_decode(json_encode($request->stockOpname));
            }
            // return response()->json($stock_opname);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table stock_opnames
            |--------------------------------------------------------------------------
            */
            $m_stock_opname = new ModelStockOpname();
            $m_stock_opname->id = $transactions_id;
            $m_stock_opname->price_purchase_app = $stock_opname->price_purchase_app;
            $m_stock_opname->price_purchase_phx = $stock_opname->price_purchase_phx;
            $m_stock_opname->price_purchase_difference = $stock_opname->price_purchase_difference;
            $m_stock_opname->price_sell_app = $stock_opname->price_sell_app;
            $m_stock_opname->price_sell_phx = $stock_opname->price_sell_phx;
            $m_stock_opname->price_sell_difference = $stock_opname->price_sell_difference;
            $m_stock_opname->stock_in_system = $stock_opname->stock_in_system;
            $m_stock_opname->stock_in_physic = $stock_opname->stock_in_physic;
            $m_stock_opname->stock_difference = $stock_opname->stock_in_difference;
            $m_stock_opname->unit = "Tablet";
            $m_stock_opname->status = "done";
            $m_stock_opname->users_persons_id = Auth::user()->persons_id;
            $m_stock_opname->medicines_items_id = $stock_opname->medicines_items_id;
            $m_stock_opname->codes_id = $tbl_init_code;
            $m_stock_opname->save();
            
            $m_medicine = ModelMedicines::find($stock_opname->medicines_items_id);
            $m_medicine->qty_total = $stock_opname->stock_in_physic;
            $m_medicine->save();
            
            $action = [
                "action" => "Melakukan Stock Opname",
                "transactions_id" => $transactions_id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "stock_opname_created";

            HistoriesCtrl::createData(json_encode($action), $initial);
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $transactions_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "id" => $transactions_id]);
            DB::rollback();
        }
    }

    public function testInvoice() {
        $tbl_name = "stock_opnames";
        $tbl_primary_key = "id";
        $tbl_init_code = "402";
        $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        echo $transactions_id;
    }
}
