<?php

namespace App\Http\Controllers\Backend\Inventory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelStockAdjustments;
use App\Model\ModelStockAdjustmentsTrSales;
use App\Model\ModelMedicines;
use DB;

class StockAdjustmentsCtrl extends Controller
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
        $state = $request->get('state');

        switch ($state) {
            case 'incoming_goods':
                $data = $this->readDataByStateIncomingGoods($search, $row, $sort);
                break;
            case 'exit_goods':
                $data = $this->readDataByStateExitGoods($search, $row, $sort);
                break;
            default:
                break;
        }
        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Create data incoming goods
    |--------------------------------------------------------------------------
    */
    protected function createIncomingGoods(Request $request)
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
            $stock_adjustments = json_decode($request->stockAdjustments);
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
            | Insert data at table stock_adjustments_tr_sales
            |--------------------------------------------------------------------------
            */
            $m_adjustments_tr_sales = new ModelStockAdjustmentsTrSales();
            $m_adjustments_tr_sales->stock_adjustments_id = $stock_adjustments_id;
            $m_adjustments_tr_sales->qty = $stock_adjustments->qty;
            $m_adjustments_tr_sales->price = $stock_adjustments->price;
            $m_adjustments_tr_sales->discount = $stock_adjustments->discount;
            $m_adjustments_tr_sales->medicines_items_id = $stock_adjustments->medicines_items_id;
            $m_adjustments_tr_sales->note = $stock_adjustments->note;
            $m_adjustments_tr_sales->save();

            ModelMedicines::where('items_id', $stock_adjustments->medicines_items_id)->increment('qty_total', $stock_adjustments->qty);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Membuat penyesuaian stok",
                "stock_adjustments_id" => $stock_adjustments_id,
                "content" => $stock_adjustments
            ];

            $initial = "stock_adjustments_tr_sales_created";

            HistoriesCtrl::createData(json_encode($action), $initial);
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $stock_adjustments_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "id" => $stock_adjustments_id]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Create data exit goods
    |--------------------------------------------------------------------------
    */
    protected function createExitGoods(Request $request)
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
            $stock_adjustments = json_decode($request->stockAdjustments);
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
            | Insert data at table stock_adjustments_tr_sales
            |--------------------------------------------------------------------------
            */
            $m_adjustments_tr_sales = new ModelStockAdjustmentsTrSales();
            $m_adjustments_tr_sales->stock_adjustments_id = $stock_adjustments_id;
            $m_adjustments_tr_sales->qty = 0 - $stock_adjustments->qty;
            $m_adjustments_tr_sales->price = $stock_adjustments->price;
            $m_adjustments_tr_sales->discount = $stock_adjustments->discount;
            $m_adjustments_tr_sales->medicines_items_id = $stock_adjustments->medicines_items_id;
            $m_adjustments_tr_sales->note = $stock_adjustments->note;
            $m_adjustments_tr_sales->save();

            ModelMedicines::where('items_id', $stock_adjustments->medicines_items_id)->decrement('qty_total', $stock_adjustments->qty);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Membuat penyesuaian stok",
                "stock_adjustments_id" => $stock_adjustments_id,
                "content" => $stock_adjustments
            ];

            $initial = "stock_adjustments_tr_sales_created";

            HistoriesCtrl::createData(json_encode($action), $initial);
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $stock_adjustments_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "id" => $stock_adjustments_id]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by state incoming goods
    |--------------------------------------------------------------------------
    */
    protected function readDataByStateIncomingGoods($search, $row, $sort)
    {
        if ($search) {
            $data = ModelStockAdjustmentsTrSales::select(
                'sa.id as id',
                'stock_adjustments_tr_sales.note as note',
                'i.id as sku',
                'i.name as name',
                'stock_adjustments_tr_sales.qty as qty',
                'sa.created_at',
                'p.name as cashier',
                'c.name as category'
            )
            ->join('stock_adjustments as sa', 'sa.id', '=', 'stock_adjustments_tr_sales.stock_adjustments_id')
            ->join('persons as p', 'p.id', '=', 'sa.users_persons_id')
            ->join('medicines as m', 'm.items_id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
            ->join('categories as c', 'c.id', '=', 'i.categories_id')
            ->where('i.name', "LIKE", "%{$search}%")
            ->where('stock_adjustments_tr_sales.qty', '>', 0)
            ->orderBy('sa.created_at', 'desc')
            ->paginate(10);
        } else {
            $data = ModelStockAdjustmentsTrSales::select(
                'sa.id as id',
                'stock_adjustments_tr_sales.note as note',
                'i.id as sku',
                'i.name as name',
                'stock_adjustments_tr_sales.qty as qty',
                'sa.created_at',
                'p.name as cashier',
                'c.name as category'
            )
            ->join('stock_adjustments as sa', 'sa.id', '=', 'stock_adjustments_tr_sales.stock_adjustments_id')
            ->join('persons as p', 'p.id', '=', 'sa.users_persons_id')
            ->join('medicines as m', 'm.items_id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
            ->join('categories as c', 'c.id', '=', 'i.categories_id')
            ->where('stock_adjustments_tr_sales.qty', '>=', 0)
            ->orderBy('sa.created_at', 'desc')
            ->paginate(10);
        }
        return response($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by state exit goods
    |--------------------------------------------------------------------------
    */
    protected function readDataByStateExitGoods($search, $row, $sort)
    {
        if ($search) {
            $data = ModelStockAdjustmentsTrSales::select(
                'sa.id as id',
                'stock_adjustments_tr_sales.note as note',
                'i.id as sku',
                'i.name as name',
                'stock_adjustments_tr_sales.qty as qty',
                'sa.created_at',
                'p.name as cashier',
                'c.name as category'
            )
            ->join('stock_adjustments as sa', 'sa.id', '=', 'stock_adjustments_tr_sales.stock_adjustments_id')
            ->join('persons as p', 'p.id', '=', 'sa.users_persons_id')
            ->join('medicines as m', 'm.items_id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
            ->join('categories as c', 'c.id', '=', 'i.categories_id')
            ->where('i.name', "LIKE", "%{$search}%")
            ->where('stock_adjustments_tr_sales.qty', '>', 0)
            ->orderBy('sa.created_at', 'desc')
            ->paginate(10);
        } else {
            $data = ModelStockAdjustmentsTrSales::select(
                'sa.id as id',
                'stock_adjustments_tr_sales.note as note',
                'i.id as sku',
                'i.name as name',
                'stock_adjustments_tr_sales.qty as qty',
                'sa.created_at',
                'p.name as cashier',
                'c.name as category'
            )
            ->join('stock_adjustments as sa', 'sa.id', '=', 'stock_adjustments_tr_sales.stock_adjustments_id')
            ->join('persons as p', 'p.id', '=', 'sa.users_persons_id')
            ->join('medicines as m', 'm.items_id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'stock_adjustments_tr_sales.medicines_items_id')
            ->join('categories as c', 'c.id', '=', 'i.categories_id')
            ->where('stock_adjustments_tr_sales.qty', '<', 0)
            ->orderBy('sa.created_at', 'desc')
            ->paginate(10);
        }
        return response($data);
    }
}
