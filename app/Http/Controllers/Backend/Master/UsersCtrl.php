<?php

namespace App\Http\Controllers\Backend\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Libraries\GenerateNumber;
use App\Libraries\RespMessages;
use App\Libraries\TelegramBot;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Http\Controllers\Backend\Master\PersonsCtrl;
use App\Model\ModelPersons;
use App\Model\ModelUsers;
use App\Model\ModelRoles;
use App\Model\ModelPhones;
use DB;

class UsersCtrl extends PersonsCtrl
{
    use PhonesCtrl;
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
            $data = ModelUsers::readDataBySearch($search, $row, $sort);
        } else {
            $data = ModelUsers::readDataByPagination($row, $sort);
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
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Generate Code 
            |--------------------------------------------------------------------------
            */
            $tbl_name = "users";
            $tbl_primary_key = "persons_id";
            $tbl_init_code = "101";
            $user_id = GenerateNumber::generatePrimaryCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Request data
            |--------------------------------------------------------------------------
            */
            if ($request->segment(1) == 'api') {
                $user = json_decode($request->user);
            } else {
                $user = json_decode(json_encode($request->user));
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table persons
            |--------------------------------------------------------------------------
            */
            PersonsCtrl::createPersons($user, $user_id, $tbl_init_code);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table users
            |--------------------------------------------------------------------------
            */
            $m_user = new ModelUsers();
            $m_user->persons_id = $user_id;
            $m_user->username = $user->username;
            $m_user->password = bcrypt($user->password);
            $m_user->email = $user->email;
            $m_user->status = $user->status;
            $m_user->permission = $user->roles_id;
            $m_user->roles_id = $user->roles_id;
            $m_user->save();
            /*
            |--------------------------------------------------------------------------
            | Insert at table phones
            |--------------------------------------------------------------------------
            */
            $m_phone = new ModelPhones();
            $m_phone->number = $user->phone;
            $m_phone->persons_id = $user_id;
            $m_phone->save();

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $user_id]);

        } catch (\Exception $e) {

            TelegramBot::sendError($e->getMessage());

            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => ""]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Read roles
    |--------------------------------------------------------------------------
    */
    public function readDataRoles()
    {
        $data = ModelRoles::whereNotIn('id', [99])->get();
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public function readDataById(Request $request)
    {
        $id = $request->get('id');
        $data = ModelPersons::find($id)
                ->with(['user' => function($q) {
                    $q->select(
                        'users.email',
                        'users.persons_id',
                        'roles.name as role_name'
                    )
                    ->join('roles', 'roles.id', '=', 'users.roles_id')
                    ->first();
                }])
                ->first();
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by check login
    |--------------------------------------------------------------------------
    */
    public function readLogin()
    {
        $data = [
            "users_persons_id" => Auth::user()->persons_id,
            "roles_id" => Auth::user()->roles_id,
            "username" => Auth::user()->username,
        ];
        return response()->json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Update data
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Request data
            |--------------------------------------------------------------------------
            */
            if ($request->segment(1) == 'api') {
                $user = json_decode($request->user);
            } else {
                $user = json_decode(json_encode($request->user));
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table persons
            |--------------------------------------------------------------------------
            */
            PersonsCtrl::updatePersons($user);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table users
            |--------------------------------------------------------------------------
            */
            $m_user = ModelUsers::find($user->persons_id);
            $m_user->permission = $user->roles_id;
            $m_user->roles_id = $user->roles_id;
            $m_user->save();

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $user->persons_id]);

        } catch (\Exception $e) {

            TelegramBot::sendError($e->getMessage());

            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => ""]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Update data status
    |--------------------------------------------------------------------------
    */
    public function updateStatus(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Request data
        |--------------------------------------------------------------------------
        */
        $supplier = json_decode(json_encode($request->supplier));
        /*
        |--------------------------------------------------------------------------
        | Update data at table persons
        |--------------------------------------------------------------------------
        */
        $data = PersonsCtrl::updateDataStatus($supplier);
        return response()->json($resp = ["status" => 1, "result" => 'success']);
    }
    /*
    |--------------------------------------------------------------------------
    | Update password
    |--------------------------------------------------------------------------
    */
    protected function updatePassword(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($request->segment(1) == 'api') {
                $user = json_decode($request->user);
            } else {
                $user = json_decode(json_encode($request->user));
            }

            $m_user = ModelUsers::find($user->persons_id);
            $m_user->password = bcrypt($user->password);
            $m_user->save();

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $user->persons_id]);

        } catch (\Exception $e) {

            TelegramBot::sendError($e->getMessage());

            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => ""]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Delete data
    |--------------------------------------------------------------------------
    */
    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Request data
            |--------------------------------------------------------------------------
            */
            $user = json_decode(json_encode($request->user));
            /*
            |--------------------------------------------------------------------------
            | Delete data at table units
            |--------------------------------------------------------------------------
            */
            $m_user = ModelUsers::find($user->id);
            $m_user->status = 0;
            $m_user->save();
            
            ModelPersons::find($user->id)->delete();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Menghapus data Pengguna",
                "user_id" => $user->id
            ];

            $initial = "user_deleted";

            HistoriesCtrl::createData(json_encode($action), $initial);

            DB::commit();

            return response()->json($resp = ["status" => 1, "result" => 'success']);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
        }
    }
}
