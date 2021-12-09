<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelSuppliers extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'persons_id';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($search, $row, $sort)
    {
        $order_by = empty($sort) ? "suppliers.persons_id" : "persons.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('suppliers')
            ->select(
                'suppliers.persons_id as personsId',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.identity_number as identityNumber',
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
            ->join('persons', 'persons.id', '=', 'suppliers.persons_id')
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
        $order_by = empty($sort) ? "suppliers.persons_id" : "persons.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('suppliers')
            ->select(
                'suppliers.persons_id as personsId',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.identity_number as identityNumber',
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
            ->join('persons', 'persons.id', '=', 'suppliers.persons_id')
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
        $data = DB::table('suppliers')
            ->select(
                'suppliers.persons_id as personsId',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.identity_number as identityNumber',
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
            ->join('persons', 'persons.id', '=', 'suppliers.persons_id')
            ->where('suppliers.persons_id', $id)
            ->whereNull('persons.deleted_at')
            ->orderBy('suppliers.persons_id', 'desc')
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
        $data = DB::table('suppliers')
            ->select(
                'suppliers.persons_id as personsId',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.identity_number as identityNumber',
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
            ->join('persons', 'persons.id', '=', 'suppliers.persons_id')
            ->whereNull('persons.deleted_at')
            ->orderBy('suppliers.persons_id', 'desc')
            ->get();

        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by autocomplete
    |--------------------------------------------------------------------------
    */
    public static function readDataByAutocomplete($type)
    {
        $data = DB::table('suppliers')
            ->select(
                'suppliers.persons_id as id',
                'persons.name as name',
                'persons.status as status'
            )
            ->join('persons', 'persons.id', '=', 'suppliers.persons_id')
            ->where("persons.status", 'active')
            ->where("persons.name", "LIKE", "%{$type}%")
            ->whereNull('persons.deleted_at')
            ->orderBy('suppliers.persons_id', 'desc')
            ->paginate(10);

        return $data;
    }

    public function person()
    {
        $data = $this->belongsTo(ModelPersons::class, 'persons_id');
        return $data;
    }

    public function trPurchases()
    {
        $data = $this->hasMany(ModelTrPurchases::class, 'suppliers_persons_id');

        return $data;
    }
}
