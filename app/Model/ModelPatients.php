<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelPatients extends Model
{
    protected $table = 'patients';
    protected $primaryKey = 'persons_id';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($search, $row, $sort)
    {
        $order_by = empty($sort) ? "patients.persons_id" : "persons.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('patients')
            ->select(
                'patients.persons_id as personsId',
                'patients.age as age',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.status as status',
                DB::raw('(
                    SELECT MAX(phones.id) 
                    FROM phones 
                    WHERE phones.persons_id = persons.id
                ) as phoneId'),
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = persons.id
                ) as phone')
            )
            ->join('persons', 'persons.id', '=', 'patients.persons_id')
            ->where("name", "LIKE", "%{$search}%")
            ->orWhere("address", "LIKE", "%{$search}%")
            ->orWhere("identity_number", "LIKE", "%{$search}%")
            ->whereNull('persons.deleted_at')
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }

    public static function readDataByPagination($row, $sort)
    {
        $order_by = empty($sort) ? "patients.persons_id" : "persons.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('patients')
            ->select(
                'patients.persons_id as personsId',
                'patients.age as age',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.status as status',
                DB::raw('(
                    SELECT MAX(phones.id) 
                    FROM phones 
                    WHERE phones.persons_id = persons.id
                ) as phoneId'),
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = persons.id
                ) as phone')
            )
            ->join('persons', 'persons.id', '=', 'patients.persons_id')
            ->whereNull('persons.deleted_at')
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public static function readDataById($id)
    {
        $data = DB::table('patients')
            ->select(
                'patients.persons_id as personsId',
                'patients.age as age',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.status as status',
                DB::raw('(
                    SELECT MAX(phones.id) 
                    FROM phones 
                    WHERE phones.persons_id = persons.id
                ) as phoneId'),
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = persons.id
                ) as phone')
            )
            ->join('persons', 'persons.id', '=', 'patients.persons_id')
            ->where('patients.persons_id', $id)
            ->whereNull('persons.deleted_at')
            ->orderBy('patients.persons_id', 'desc')
            ->first();
        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read all data
    |--------------------------------------------------------------------------
    */
    public static function readDataAll()
    {
        $data = DB::table('patients')
            ->select(
                'patients.persons_id as personsId',
                'patients.age as age',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.status as status',
                DB::raw('(
                    SELECT MAX(phones.id) 
                    FROM phones 
                    WHERE phones.persons_id = persons.id
                ) as phoneId'),
                DB::raw('(
                    SELECT MAX(phones.number) 
                    FROM phones 
                    WHERE phones.persons_id = persons.id
                ) as phone')
            )
            ->join('persons', 'persons.id', '=', 'patients.persons_id')
            ->whereNull('persons.deleted_at')
            ->orderBy('patients.persons_id', 'desc')
            ->get();

        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by autocomplete
    |--------------------------------------------------------------------------
    */
    public static function readDataByAutocomplete($typing)
    {
        $data = DB::table('patients')
            ->select(
                'patients.persons_id as personsId',
                'patients.age as age',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.status as status'
            )
            ->join('persons', 'persons.id', '=', 'patients.persons_id')
            ->where("persons.status", 1)
            ->where("persons.name", "LIKE", "%{$typing}%")
            ->whereNull('persons.deleted_at')
            ->orderBy('patients.persons_id', 'desc')
            ->paginate(10);

        return $data;
    }
}
