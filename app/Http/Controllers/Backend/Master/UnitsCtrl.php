<?php

namespace App\Http\Controllers\Backend\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Libraries\GenerateNumber;
use App\Model\ModelUnits;
use DB;

class UnitsCtrl extends Controller
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
            $data = ModelUnits::readDataBySearch($search, $row, $sort);
        } else {
            $data = ModelUnits::readDataByPagination($row, $sort);
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
        /*
        |--------------------------------------------------------------------------
        | Request data
        |--------------------------------------------------------------------------
        */
        if ($request->segment(1) == 'api') {
            $unit = json_decode($request->unit);
        } else {
            $unit = json_decode(json_encode($request->unit));
        }
        /*
        |--------------------------------------------------------------------------
        | Insert data at table units
        |--------------------------------------------------------------------------
        */
        $m_unit = new ModelUnits();
        $m_unit->name = Str::upper($unit->name);
        $m_unit->save();

        return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $m_unit->id]);
    }
    /*
    |--------------------------------------------------------------------------
    | Read all data
    |--------------------------------------------------------------------------
    */
    public function readDataAll()
    {
        $data = ModelUnits::readDataAll();
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public function readDataById($id)
    {
        $data = ModelUnits::readDataById($id);
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Update data
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Request data
        |--------------------------------------------------------------------------
        */
        if ($request->segment(1) == 'api') {
            $unit = json_decode($request->unit);
        } else {
            $unit = json_decode(json_encode($request->unit));
        }
        /*
        |--------------------------------------------------------------------------
        | Update data at table units
        |--------------------------------------------------------------------------
        */
        $m_unit = ModelUnits::find($unit->id);
        $m_unit->name = Str::upper($unit->name);
        $m_unit->save();
        return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $unit->id]);
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data
    |--------------------------------------------------------------------------
    */
    public function delete(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Request data
        |--------------------------------------------------------------------------
        */
        $unit = json_decode(json_encode($request->unit));
        /*
        |--------------------------------------------------------------------------
        | Delete data at table units
        |--------------------------------------------------------------------------
        */
        ModelUnits::find($unit->id)->delete();
        return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $unit->id]);
    }
}
