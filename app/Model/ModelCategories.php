<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class ModelCategories extends Model
{
    use SoftDeletes;
    protected $table = 'categories';
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($typo, $row, $sort)
    {
        $order_by = empty($sort) ? "categories.id" : "categories.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('categories')
        ->select(
            'categories.id as id',
            'categories.name as name',
            'categories.parent_id as parent_id',
            'categories.status as status'
        )
        ->where("name", "LIKE", "%{$typo}%")
        ->whereNull('deleted_at')
        ->orderBy($order_by, $sort)
        ->paginate($row);

        return $data;
    }
    public static function readDataByPagination($row, $sort)
    {
        $order_by = empty($sort) ? "categories.id" : "categories.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('categories')
        ->select(
            'categories.id as id',
            'categories.name as name',
            'categories.parent_id as parent_id',
            'categories.status as status'
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
        $data = DB::table('categories')
        ->select(
            'categories.id as id',
            'categories.name as name',
            'categories.parent_id as parent_id',
            'categories.status as status'
        )
        ->where('categories.id', $id)
        ->whereNull('deleted_at')
        ->orderBy('categories.id', 'desc')
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
        $data = DB::table('categories')
        ->select(
            'categories.id as id',
            'categories.name as name',
            'categories.parent_id as parent_id',
            'categories.status as status'
        )
        ->whereNull('deleted_at')
        ->orderBy('categories.id', 'desc')
        ->get();
        return $data;
    }
}
