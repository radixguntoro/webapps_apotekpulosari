<?php

namespace App\Http\Controllers\Backend\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Http\Controllers\Backend\Master\PersonsCtrl;
use App\Model\ModelPersons;
use App\Model\ModelPatients;
use DB;

class PatientsCtrl extends PersonsCtrl
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
            $data = ModelPatients::readDataBySearch($search, $row, $sort);
        } else {
            $data = ModelPatients::readDataByPagination($row, $sort);
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
            $tbl_name = "patients";
            $tbl_primary_key = "persons_id";
            $tbl_init_code = "103";
            $patient_id = GenerateNumber::generatePrimaryCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Request data
            |--------------------------------------------------------------------------
            */
            $patient = json_decode(json_encode($request->patient));
            /*
            |--------------------------------------------------------------------------
            | Insert data at table persons
            |--------------------------------------------------------------------------
            */
            PersonsCtrl::createPersons($patient, $patient_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table patients
            |--------------------------------------------------------------------------
            */
            $m_patient = new ModelPatients();
            $m_patient->persons_id = $patient_id;
            $m_patient->age = $patient->age;
            $m_patient->save();
            /*
            |--------------------------------------------------------------------------
            | Insert or Update at table phones
            |--------------------------------------------------------------------------
            */
            $get_phone = $this->createOrUpdate($patient->phones, $patient_id);

            if ($get_phone == 1) {
                return response()->json($resp = ["status" => 5, "result" => 'success']);
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menambahkan data patient",
                "patient_id" => $patient_id
            ];

            $initial = "patient_created";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $patient_id]);
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
        $patient = ModelPatients::readDataById($id);
        $data = [
            "patient" => $patient
        ];
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read all data
    |--------------------------------------------------------------------------
    */
    public function readAll()
    {
        $data = ModelPatients::readDataAll();
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
            $patient = json_decode(json_encode($request->patient));
            /*
            |--------------------------------------------------------------------------
            | Update table at table persons
            |--------------------------------------------------------------------------
            */
            PersonsCtrl::updatePersons($patient);
            /*
            |--------------------------------------------------------------------------
            | Update data at table patients
            |--------------------------------------------------------------------------
            */
            $m_patient = ModelPatients::find($patient->id);
            $m_patient->age = $patient->age;
            $m_patient->save();
            /*
            |--------------------------------------------------------------------------
            | Insert or Update at table phones
            |--------------------------------------------------------------------------
            */
            $get_phone = $this->createOrUpdate($patient->phones, $patient->id);

            if ($get_phone == 1) {
                return response()->json($resp = ["status" => 3, "result" => 'success']);
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menambahkan data patient",
                "patient_id" => $patient->persons_id
            ];

            $initial = "patient_updated";

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
        $patient = json_decode(json_encode($request->patient));
        /*
        |--------------------------------------------------------------------------
        | Update data at table persons
        |--------------------------------------------------------------------------
        */
        $data = PersonsCtrl::updateDataStatus($patient);
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
            $patient = json_decode(json_encode($request->patient));
            /*
            |--------------------------------------------------------------------------
            | Delete data at table units
            |--------------------------------------------------------------------------
            */
            ModelPersons::find($patient->id)->delete();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menghapus data patient",
                "patient_id" => $patient->id
            ];

            $initial = "patient_deleted";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();

            return response()->json($resp = ["status" => 1, "result" => 'success']);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
        }
    }
}
