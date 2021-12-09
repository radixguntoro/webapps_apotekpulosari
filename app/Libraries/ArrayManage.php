<?php

namespace App\Libraries;

class ArrayManage
{
    /*
    |--------------------------------------------------------------------------
    | Sorting desc manual pagination
    |--------------------------------------------------------------------------
    */
    public static function sortDesc($data, $tableAttrSort)
    {
        $id = [];
        foreach ($data as $key => $value) {
            $id[$key] = $value[$tableAttrSort];
        }
        array_multisort($id, SORT_DESC, $data);
        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Sorting asc manual pagination
    |--------------------------------------------------------------------------
    */
    public static function sortAsc($data, $tableAttrSort)
    {
        $id = [];
        foreach ($data as $key => $value) {
            $id[$key] = $value[$tableAttrSort];
        }
        array_multisort($id, SORT_ASC, $data);
        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Except Array
    |--------------------------------------------------------------------------
    */
    public static function except($array, Array $excludeKeys){
        return array_diff($array, $excludeKeys);
    }
}
