<?php

namespace App\Http\Controllers\Client\Home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ModelMedicines;
use App\Model\ModelItems;
use App\Model\ModelCategories;
use DB;

class HomeCtrl extends Controller
{
    public function readDataMedicines(Request $request) 
    {
        $search = $request->get('search');
        $row = $request->get('row');
        $sort_name = $request->get('sort_name');
        $filter_category = explode(',', trim($request->get('category')));
        $filter_unit = explode(',', trim($request->get('unit')));
        
        if ($search) {
            $data = ModelMedicines::readDataBySearchClient($search, $row, $sort_name, $filter_category, $filter_unit);
        } else {
            $data = ModelMedicines::readDataByPaginationClient($row, $sort_name, $filter_category, $filter_unit);
        }
        return response($data);
    }
}
