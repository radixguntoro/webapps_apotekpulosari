<?php

namespace App\Http\Controllers\Backend\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Http\Controllers\Backend\Master\PersonsCtrl;
use App\Model\ModelPersons;
use App\Model\ModelCustomers;
use DB;

class CustomersCtrl extends PersonsCtrl
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
            $data = ModelCustomers::readDataBySearch($search, $row, $sort);
        } else {
            $data = ModelCustomers::readDataByPagination($row, $sort);
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
            $tbl_name = "customers";
            $tbl_primary_key = "persons_id";
            $tbl_init_code = "103";
            $customer_id = GenerateNumber::generatePrimaryCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Request data
            |--------------------------------------------------------------------------
            */
            if ($request->segment(1) == 'api') {
                $customer = json_decode($request->customer);
            } else {
                $customer = json_decode(json_encode($request->customer));
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table persons
            |--------------------------------------------------------------------------
            */
            PersonsCtrl::createPersons($customer, $customer_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table customers
            |--------------------------------------------------------------------------
            */
            $m_customer = new ModelCustomers();
            $m_customer->persons_id = $customer_id;
            $m_customer->save();
            /*
            |--------------------------------------------------------------------------
            | Insert or Update at table phones
            |--------------------------------------------------------------------------
            */
            PhonesCtrl::createOrUpdate($customer->phones, $customer_id);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menambahkan data customer",
                "customer_id" => $customer_id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "customer_created";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $customer_id]);
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
        $customer = ModelCustomers::readDataById($id);
        $data = [
            "customer" => $customer
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
        $customer = ModelCustomers::readDataByAutocomplete($type);
        $data = [
            "customer" => $customer
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
        $data = ModelCustomers::readDataAll();
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
                $customer = json_decode($request->customer);
            } else {
                $customer = json_decode(json_encode($request->customer));
            }
            /*
            |--------------------------------------------------------------------------
            | Update table at table persons
            |--------------------------------------------------------------------------
            */
            PersonsCtrl::updatePersons($customer);
            /*
            |--------------------------------------------------------------------------
            | Insert or Update at table phones
            |--------------------------------------------------------------------------
            */
            PhonesCtrl::createOrUpdate($customer->phones, $customer->id);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menambahkan data customer",
                "customer_id" => $customer->persons_id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "customer_updated";

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
        $customer = json_decode(json_encode($request->customer));
        /*
        |--------------------------------------------------------------------------
        | Update data at table persons
        |--------------------------------------------------------------------------
        */
        $data = PersonsCtrl::updateDataStatus($customer);
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
            $customer = json_decode(json_encode($request->customer));
            /*
            |--------------------------------------------------------------------------
            | Delete data at table units
            |--------------------------------------------------------------------------
            */
            ModelPersons::find($customer->id)->delete();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menghapus data customer",
                "customer_id" => $customer->id,
                "actor" => Auth::user()->persons_id,
            ];

            $initial = "customer_deleted";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();

            return response()->json($resp = ["status" => 1, "result" => 'success']);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
        }
    }
}
