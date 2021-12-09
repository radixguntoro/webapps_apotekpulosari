<?php

namespace App\Http\Controllers\Backend\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Model\ModelPersons;
use App\Http\Controllers\Controller;

abstract class PersonsCtrl extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Variable Declaration
    |--------------------------------------------------------------------------
    */
    protected $id = '';
    protected $name = '';
    protected $address = '';
    protected $city = '';
    protected $identity_number = '';
    protected $email = '';
    protected $status = '';
    protected $codes_id = '';

    abstract public function index(Request $request);
    /*
    |--------------------------------------------------------------------------
    | Insert to table persons
    |--------------------------------------------------------------------------
    */
    protected static function createPersons($person, $id, $get_init_code)
    {
        $m_persons = new ModelPersons();
        $m_persons->id = $id;
        $m_persons->name = Str::upper($person->name);
        $m_persons->address = isset($person->address) ? $person->address : null;
        $m_persons->city = isset($person->city) ? Str::title($person->city) : null;
        $m_persons->status = $person->status;
        $m_persons->codes_id = $get_init_code;
        $m_persons->save();
    }
    /*
    |--------------------------------------------------------------------------
    | Update to table persons
    |--------------------------------------------------------------------------
    */
    protected static function updatePersons($person)
    {
        $m_persons = ModelPersons::find($person->persons_id);
        $m_persons->name = Str::upper($person->name);
        $m_persons->address = isset($person->address) ? $person->address : null;
        $m_persons->city = isset($person->city) ? Str::title($person->city) : null;
        $m_persons->status = $person->status;
        $m_persons->save();
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data in table persons permanently
    |--------------------------------------------------------------------------
    */
    protected function softDeletePersons($request)
    {
        ModelPersons::find($request->id)->delete();
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data in table persons temporarily
    |--------------------------------------------------------------------------
    */
    protected function forceDeletePersons($request)
    {
        ModelPersons::find($request->id)->forceDelete();
    }
    /*
    |--------------------------------------------------------------------------
    | Getting persons
    |--------------------------------------------------------------------------
    */
    protected function getInfoPersons($id, $join)
    {
        if ($join == 'partners') {
            $result = ModelPersons::where('id', $id)->join('partners', 'partners.persons_id', '=', 'persons.id')->first();
        } elseif ($join == 'users') {
            $result = ModelPersons::where('id', $id)->join('users', 'users.persons_id', '=', 'persons.id')->first();
        } elseif ($join == 'customers') {
            $result = ModelPersons::where('id', $id)->join('customers', 'customers.persons_id', '=', 'persons.id')->first();
        } else {
            $result = ModelPersons::find($id);
        }
        return $result;
    }
    /*
    |--------------------------------------------------------------------------
    | Set Status
    |--------------------------------------------------------------------------
    */
    protected static function updateDataStatus($user)
    {
        $m_persons = ModelPersons::find($user->id);
        $m_persons->status = $user->status;
        $m_persons->save();
    }
}
