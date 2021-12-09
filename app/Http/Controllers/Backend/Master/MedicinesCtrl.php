<?php

namespace App\Http\Controllers\Backend\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Libraries\GenerateImage;
use App\Libraries\RespMessages;
use App\Libraries\TelegramBot;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Http\Controllers\Backend\Master\ItemsCtrl;
use App\Model\ModelItems;
use App\Model\ModelMedicines;
use App\Model\ModelMedicineDetails;
use App\Model\ModelStockOpname;
use DB;

class MedicinesCtrl extends ItemsCtrl
{
    private $join = '';

    public function __construct()
    {
        $this->histories = new HistoriesCtrl();
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $row = $request->get('row');
        $sort_name = $request->get('sort_name');
        $filter_category = explode(',', trim($request->get('category')));
        $filter_unit = explode(',', trim($request->get('unit')));
        
        if ($search) {
            $data = ModelMedicines::readDataBySearch($search, $row, $sort_name, $filter_category, $filter_unit);
        } else {
            $data = ModelMedicines::readDataByPagination($row, $sort_name, $filter_category, $filter_unit);
        }
        return response($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Create data
    |--------------------------------------------------------------------------
    */
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Generate Code
            |--------------------------------------------------------------------------
            */
            $tbl_name = "medicines";
            $tbl_primary_key = "items_id";
            $tbl_init_code = "201";
            $medicine_id = GenerateNumber::generatePrimaryCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Request data
            |--------------------------------------------------------------------------
            */
            if ($request->segment(1) == 'api') {
                $medicine = json_decode($request->medicine);
            } else {
                $medicine = json_decode(json_encode($request->medicine));  
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table items
            |--------------------------------------------------------------------------
            */
            $is_already_name = 0;

            $is_already_name = count(ModelItems::where('name', $medicine->name)->get());

            if ($is_already_name > 0) {
                return response()->json($resp = ["status" => 2, "result" => 'exist']);
            } else {
                ItemsCtrl::createData($medicine, $medicine_id, $tbl_init_code);
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table medicines
            |--------------------------------------------------------------------------
            */
            $m_medicine = new ModelMedicines();
            $m_medicine->suppliers_persons_id = $medicine->suppliers_persons_id;
            $m_medicine->qty_total = 0;
            $m_medicine->qty_min = $medicine->qty_min;
            $m_medicine->plu = $medicine_id;
            $m_medicine->units_id = $medicine->unit_id;
            $m_medicine->items_id = $medicine_id;
            $m_medicine->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table medicine_details
            |--------------------------------------------------------------------------
            */
            foreach ($medicine->detail as $key => $val) {
                $m_medicine = new ModelMedicineDetails();
                $m_medicine->unit = $val->unit;
                $m_medicine->barcode = $val->barcode;
                $m_medicine->qrcode = $val->qrcode;
                $m_medicine->price_sell = $val->price_sell;
                $m_medicine->price_purchase = $val->price_purchase;
                $m_medicine->qty = 0;
                $m_medicine->profit_percent = $val->profit_percent;
                $m_medicine->medicines_items_id = $medicine_id;
                $m_medicine->save();
            }
            
            /*
            |--------------------------------------------------------------------------
            | Generate Code
            |--------------------------------------------------------------------------
            */
            $tbl_name_so = "stock_opnames";
            $tbl_primary_key_so = "id";
            $tbl_init_code_so = "402";
            $transactions_id_so = GenerateNumber::generateDayCode($tbl_name_so, $tbl_primary_key_so, 1, $tbl_init_code_so);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table stock_opnames
            |--------------------------------------------------------------------------
            */
            $m_stock_opname = new ModelStockOpname();
            $m_stock_opname->id = $transactions_id_so;
            foreach ($medicine->detail as $key => $val) {
                $m_stock_opname->price_purchase_app = $val->price_purchase;
                $m_stock_opname->price_purchase_phx = $val->price_purchase;
                $m_stock_opname->price_purchase_difference = 0;
                $m_stock_opname->price_sell_app = $val->price_sell;
                $m_stock_opname->price_sell_phx = $val->price_sell;
                $m_stock_opname->price_sell_difference = 0;
            }
            $m_stock_opname->stock_in_system = 0;
            $m_stock_opname->stock_in_physic = 0;
            $m_stock_opname->stock_difference = 0;
            $m_stock_opname->unit = "tablet";
            $m_stock_opname->status = "done";
            $m_stock_opname->users_persons_id = Auth::user()->persons_id;
            $m_stock_opname->medicines_items_id = $medicine_id;
            $m_stock_opname->codes_id = $tbl_init_code_so;
            $m_stock_opname->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menambahkan data Obat",
                "medicine_id" => $medicine_id,
                "actor" => Auth::user()->persons_id
            ];

            $initial = "medicine_created";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();

            return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $medicine_id]);
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
    public function readDataById($id)
    {
        $medicine = ModelMedicines::readDataById($id);
        $data = [
            "medicine" => $medicine
        ];
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public function readDataByBarcode(Request $request)
    {
        $barcode = $request->get('id');
        $medicine = ModelMedicines::select(
            'items_id as items_id',
            'plu as plu',
            'qty_total as qty_total',
            'qty_min as qty_min',
            'suppliers_persons_id as suppliers_persons_id',
            'units_id as units_id',
            'medicines_items_id as medicines_items_id',
            'unit as unit',
            'barcode as barcode',
            'qrcode as qrcode',
            'price_sell as price_sell',
            'price_purchase as price_purchase',
            'qty as qty',
            'profit_percent as profit_percent',
            'profit_value as profit_value',
            'medicine_details.id as id',
            DB::raw('CASE 
            WHEN (
                SELECT tr_purchase_details.discount
                FROM tr_purchase_details 
                JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                AND tr_purchase_details.unit = "tablet"
                ORDER BY tr_purchases.date desc
                LIMIT 1
            ) is NULL THEN 0
            ELSE (
                SELECT tr_purchase_details.discount
                FROM tr_purchase_details 
                JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                AND tr_purchase_details.unit = "tablet"
                ORDER BY tr_purchases.date desc
                LIMIT 1
            ) END
            as discount'),
            DB::raw('CASE 
            WHEN (
                SELECT tr_purchases.ppn
                FROM tr_purchase_details 
                JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                AND tr_purchase_details.unit = "tablet"
                ORDER BY tr_purchases.date desc
                LIMIT 1
            ) is NULL THEN 0
            ELSE (
                SELECT tr_purchases.ppn
                FROM tr_purchase_details 
                JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                AND tr_purchase_details.unit = "tablet"
                ORDER BY tr_purchases.date desc
                LIMIT 1
            ) END
            as ppn')
        )
        ->with('item')
        ->join('medicine_details', 'medicine_details.medicines_items_id', '=', 'medicines.items_id')
        ->join('items as i', 'i.id', '=', 'medicines.items_id')
        ->where('medicine_details.unit', 'Tablet')
        ->where('medicine_details.barcode', $barcode)
        ->whereNull('i.deleted_at')
        ->orWhere('medicine_details.unit', 'Tablet')
        ->where('i.id', $barcode)
        ->whereNull('i.deleted_at')
        ->first();
        $data = [
            "medicine" => $medicine,
        ];
        return response($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by autocomplete
    |--------------------------------------------------------------------------
    */
    public function readAutocomplete(Request $request)
    {
        $type = $request->get('type');
        $medicine = ModelMedicines::readDataByAutocomplete($type);
        $data = [
            "medicine" => $medicine,
        ];
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Update data
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Request data
            |--------------------------------------------------------------------------
            */
            if ($request->segment(1) == 'api') {
                $medicine = json_decode($request->medicine);
            } else {
                $medicine = json_decode(json_encode($request->medicine));  
            }
            // echo response()->json($medicine);die;
            /*
            |--------------------------------------------------------------------------
            | Update data at table items
            |--------------------------------------------------------------------------
            */
            ItemsCtrl::updateData($medicine);
            /*
            |--------------------------------------------------------------------------
            | Update data at table medicines
            |--------------------------------------------------------------------------
            */
            $m_medicine = ModelMedicines::find($medicine->id);
            $m_medicine->units_id = $medicine->unit_id;
            $m_medicine->suppliers_persons_id = $medicine->suppliers_persons_id;
            $m_medicine->qty_total = $medicine->qty_total;
            $m_medicine->qty_min = $medicine->qty_min;
            $m_medicine->save();
            /*
            |--------------------------------------------------------------------------
            | Create or Update data at table medicines
            |--------------------------------------------------------------------------
            */
            $get_medicine = ModelMedicineDetails::where('medicines_items_id', $medicine->id)->get();
            if (count($get_medicine) > 0) {
                foreach ($medicine->detail as $key => $val) {
                    $m_medicine_detail = ModelMedicineDetails::find($val->id);
                    $m_medicine_detail->unit = $val->unit;
                    $m_medicine_detail->barcode = $val->barcode;
                    $m_medicine_detail->qrcode = $val->qrcode;
                    $m_medicine_detail->price_sell = $val->price_sell;
                    $m_medicine_detail->price_purchase = $val->price_purchase;
                    $m_medicine_detail->qty = $val->qty;
                    $m_medicine_detail->profit_percent = $val->profit_percent;
                    $m_medicine_detail->save();
                }
            } else {
                foreach ($medicine->detail as $key => $val) {
                    $m_medicine_detail = new ModelMedicineDetails();
                    $m_medicine_detail->unit = $val->unit;
                    $m_medicine_detail->barcode = $val->barcode;
                    $m_medicine_detail->qrcode = $val->qrcode;
                    $m_medicine_detail->price_sell = $val->price_sell;
                    $m_medicine_detail->price_purchase = $val->price_purchase;
                    $m_medicine_detail->qty = $val->qty;
                    $m_medicine_detail->profit_percent = $val->profit_percent;
                    $m_medicine_detail->medicines_items_id = $medicine->id;
                    $m_medicine_detail->save();
                }
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Mengubah data Obat",
                "medicine_id" => $medicine->id,
                "actor" => Auth::user()->persons_id
            ];

            $initial = "medicine_updated";

            HistoriesCtrl::createData(json_encode($action), $initial);
            
            DB::commit();

            return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $medicine->id]);
        } catch (\Exception $e) {

            TelegramBot::sendError($e->getMessage());

            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => ""]);
            DB::rollback();
        }
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
        $medicine = json_decode(json_encode($request->medicine));
        /*
        |--------------------------------------------------------------------------
        | Update data at table items
        |--------------------------------------------------------------------------
        */
        $data = ItemsCtrl::updateDataStatus($medicine);
        return response()->json($resp = ["status" => 1, "result" => 'success']);
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
                $medicine = json_decode($request->medicine);
            } else {
                $medicine = json_decode(json_encode($request->medicine));  
            }
            /*
            |--------------------------------------------------------------------------
            | Delete data at table units
            |--------------------------------------------------------------------------
            */
            ModelItems::find($medicine->id)->delete();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menghapus data Obat",
                "medicine_id" => $medicine->id,
                "actor" => Auth::user()->persons_id
            ];

            $initial = "medicine_deleted";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();

            return response()->json($resp = ["status" => 1, "result" => 'success']);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
        }
    }
}
