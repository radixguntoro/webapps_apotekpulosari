<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelUsers extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'persons_id';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($search, $row, $sort)
    {
        $order_by = empty($sort) ? "users.persons_id" : "persons.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('users')
            ->select(
                'users.persons_id as personsId',
                'users.username as username',
                'users.permission as permission',
                'users.email as email',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.identity_number as identityNumber',
                'persons.status as status',
                'roles.id as rolesId',
                'roles.name as rolesName',
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
            ->join('persons', 'persons.id', '=', 'users.persons_id')
            ->join('roles', 'roles.id', '=', 'users.roles_id')
            ->where("persons.name", "LIKE", "%{$search}%")
            ->whereNotIn("users.permission", [99])
            ->whereNull('persons.deleted_at')
            ->orderBy($order_by, $sort)
            ->paginate(10);

        return $data;
    }

    public static function readDataByPagination($row, $sort)
    {
        $order_by = empty($sort) ? "users.persons_id" : "persons.name";
        $sort = empty($sort) ? "desc" : $sort;

        $data = DB::table('users')
            ->select(
                'users.persons_id as personsId',
                'users.username as username',
                'users.permission as permission',
                'users.email as email',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.identity_number as identityNumber',
                'persons.status as status',
                'roles.id as rolesId',
                'roles.name as rolesName',
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
            ->join('persons', 'persons.id', '=', 'users.persons_id')
            ->join('roles', 'roles.id', '=', 'users.roles_id')
            ->whereNotIn("users.permission", [99])
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
        $data = DB::table('users')
            ->select(
                'users.persons_id as personsId',
                'users.username as username',
                'users.permission as permission',
                'users.email as email',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.identity_number as identityNumber',
                'persons.status as status',
                'roles.id as rolesId',
                'roles.name as rolesName',
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
            ->join('persons', 'persons.id', '=', 'users.persons_id')
            ->join('roles', 'roles.id', '=', 'users.roles_id')
            ->where('users.persons_id', $id)
            ->whereNull('persons.deleted_at')
            ->orderBy('users.persons_id', 'desc')
            ->first();
        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by autocomplete
    |--------------------------------------------------------------------------
    */
    public static function readDataByAutocomplete($typing)
    {
        $data = DB::table('users')
            ->select(
                'users.persons_id as personsId',
                'users.username as username',
                'users.permission as permission',
                'users.email as email',
                'persons.id as id',
                'persons.name as name',
                'persons.address as address',
                'persons.city as city',
                'persons.identity_number as identityNumber',
                'persons.status as status',
                'roles.id as rolesId',
                'roles.name as rolesName',
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
            ->join('persons', 'persons.id', '=', 'users.persons_id')
            ->join('roles', 'roles.id', '=', 'users.roles_id')
            ->where("persons.status", 1)
            ->where("persons.name", "LIKE", "%{$typing}%")
            ->whereNull('persons.deleted_at')
            ->orderBy('users.persons_id', 'desc')
            ->paginate(10);

        return $data;
    }

    public function person()
    {
        $data = $this->belongsTo(ModelPersons::class, 'persons_id');
        return $data;
    }
}
