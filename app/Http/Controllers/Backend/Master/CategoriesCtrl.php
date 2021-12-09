<?php

namespace App\Http\Controllers\Backend\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Libraries\GenerateNumber;
use App\Model\ModelCategories;
use DB;

class CategoriesCtrl extends Controller
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
            $data = ModelCategories::readDataBySearch($search, $row, $sort);
        } else {
            $data = ModelCategories::readDataByPagination($row, $sort);
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
        | Generate Code
        |--------------------------------------------------------------------------
        */
        $tbl_name = "categories";
        $tbl_primary_key = "id";
        $tbl_init_code = "100";
        $category_id = GenerateNumber::generatePrimaryCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        /*
        |--------------------------------------------------------------------------
        | Request data
        |--------------------------------------------------------------------------
        */
        if ($request->segment(1) == 'api') {
            $category = json_decode($request->category);
        } else {
            $category = json_decode(json_encode($request->category));
        }
        /*
        |--------------------------------------------------------------------------
        | Insert data at table category
        |--------------------------------------------------------------------------
        */
        $m_category = new ModelCategories();
        $m_category->id = $category_id;
        $m_category->name = $category->name;
        $m_category->parent_id = $category_id;
        $m_category->status = "P";
        $m_category->save();

        return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $category_id]);
    }
    /*
    |--------------------------------------------------------------------------
    | Read all data
    |--------------------------------------------------------------------------
    */
    public function readDataAll()
    {
        $data = ModelCategories::readDataAll();
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public function readDataById($id)
    {
        $data = ModelCategories::readDataById($id);
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
            $category = json_decode($request->category);
        } else {
            $category = json_decode(json_encode($request->category));
        }
        /*
        |--------------------------------------------------------------------------
        | Update data at table categories
        |--------------------------------------------------------------------------
        */
        $m_category = ModelCategories::find($category->id);
        $m_category->name = $category->name;
        $m_category->save();
        return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $category->id]);
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
        if ($request->segment(1) == 'api') {
            $category = json_decode($request->category);
        } else {
            $category = json_decode(json_encode($request->category));
        }
        /*
        |--------------------------------------------------------------------------
        | Delete data at table categories
        |--------------------------------------------------------------------------
        */
        ModelCategories::find($category->id)->delete();
        return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $category->id]);
    }
}
