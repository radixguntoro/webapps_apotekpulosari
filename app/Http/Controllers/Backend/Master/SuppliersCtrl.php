<?php

namespace App\Http\Controllers\Backend\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Http\Controllers\Backend\Master\PersonsCtrl;
use App\Model\ModelPersons;
use App\Model\ModelSuppliers;
use DB;

class SuppliersCtrl extends PersonsCtrl
{
    use PhonesCtrl;
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
            $data = ModelSuppliers::readDataBySearch($search, $row, $sort);
        } else {
            $data = ModelSuppliers::readDataByPagination($row, $sort);
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
            $tbl_name = "suppliers";
            $tbl_primary_key = "persons_id";
            $tbl_init_code = "102";
            $supplier_id = GenerateNumber::generatePrimaryCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Request data
            |--------------------------------------------------------------------------
            */
            if ($request->segment(1) == 'api') {
                $supplier = json_decode($request->supplier);
            } else {
                $supplier = json_decode(json_encode($request->supplier));
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table persons
            |--------------------------------------------------------------------------
            */
            PersonsCtrl::createPersons($supplier, $supplier_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table suppliers
            |--------------------------------------------------------------------------
            */
            $m_supplier = new ModelSuppliers();
            $m_supplier->persons_id = $supplier_id;
            $m_supplier->save();
            /*
            |--------------------------------------------------------------------------
            | Insert or Update at table phones
            |--------------------------------------------------------------------------
            */
            PhonesCtrl::createOrUpdate($supplier->phones, $supplier_id);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menambahkan data Supplier",
                "supplier_id" => $supplier_id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "supplier_created";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $supplier_id]);
        } catch (\Exception $e) {
            return $e;
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
        $supplier = ModelSuppliers::readDataById($id);
        $data = [
            "supplier" => $supplier
        ];
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by autocomplete
    |--------------------------------------------------------------------------
    */
    public function readAutocomplete(Request $request)
    {
        $type = $request->get('type');
        $supplier = ModelSuppliers::readDataByAutocomplete($type);
        $data = [
            "supplier" => $supplier
        ];
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read all data
    |--------------------------------------------------------------------------
    */
    public function readDataAll()
    {
        $data = ModelSuppliers::readDataAll();
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
                $supplier = json_decode($request->supplier);
            } else {
                $supplier = json_decode(json_encode($request->supplier));
            }
            /*
            |--------------------------------------------------------------------------
            | Update table at table persons
            |--------------------------------------------------------------------------
            */
            PersonsCtrl::updatePersons($supplier);
            /*
            |--------------------------------------------------------------------------
            | Insert or Update at table phones
            |--------------------------------------------------------------------------
            */
            PhonesCtrl::createOrUpdate($supplier->phones, $supplier->id);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menambahkan data Supplier",
                "supplier_id" => $supplier->persons_id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "supplier_updated";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success']);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Update data status
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Request data
        |--------------------------------------------------------------------------
        */
        $supplier = json_decode(json_encode($request->supplier));
        /*
        |--------------------------------------------------------------------------
        | Update data at table persons
        |--------------------------------------------------------------------------
        */
        $data = PersonsCtrl::updateDataStatus($supplier);
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
            $supplier = json_decode(json_encode($request->supplier));
            /*
            |--------------------------------------------------------------------------
            | Delete data at table units
            |--------------------------------------------------------------------------
            */
            ModelPersons::find($supplier->id)->delete();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menghapus data Supplier",
                "supplier_id" => $supplier->id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "supplier_deleted";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();

            return response()->json($resp = ["status" => 1, "result" => 'success']);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
        }
    }
}
