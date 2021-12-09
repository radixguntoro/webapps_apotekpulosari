<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class ModelUnits extends Model
{
    use SoftDeletes;
    protected $table = 'units';
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($typo, $row, $sort)
    {
        $order_by = empty($sort) ? "units.id" : "units.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('units')
        ->select(
            'units.id as id',
            'units.name as name'
        )
        ->where("name", "LIKE", "%{$typo}%")
        ->whereNull('deleted_at')
        ->orderBy($order_by, $sort)
        ->paginate($row);

        return $data;
    }
    public static function readDataByPagination($row, $sort)
    {
        $order_by = empty($sort) ? "units.id" : "units.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('units')
        ->select(
            'units.id as id',
            'units.name as name'
        )
        ->whereNull('deleted_at')
        ->orderBy($order_by, $sort)
        ->paginate($row);

        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public static function readDataById($id)
    {
        $data = DB::table('units')
        ->select(
            'units.id as id',
            'units.name as name'
        )
        ->where('units.id', $id)
        ->whereNull('deleted_at')
        ->orderBy('units.id', 'desc')
        ->get();
        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read all data
    |--------------------------------------------------------------------------
    */
    public static function readDataAll()
    {
        $data = DB::table('units')
        ->select(
            'units.id as id',
            'units.name as name'
        )
        ->whereNull('deleted_at')
        ->orderBy('units.id', 'desc')
        ->get();
        return $data;
    }
}
