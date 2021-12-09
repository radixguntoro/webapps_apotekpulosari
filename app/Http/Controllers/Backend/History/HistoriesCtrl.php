<?php

namespace App\Http\Controllers\Backend\History;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Model\ModelHistories;

class HistoriesCtrl extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Declare
    |--------------------------------------------------------------------------
    */
    protected $id = '';
    protected $action = '';
    protected $created_at = '';
    protected $users_persons_id = '';
    /*
    |--------------------------------------------------------------------------
    | Menyimpan data
    |--------------------------------------------------------------------------
    */
    public static function createData($action, $initial)
    {
        /*
        |--------------------------------------------------------------------------
        | Generate Code Histories
        |--------------------------------------------------------------------------
        */
        $tbl_name = "histories";
        $tbl_primary_key = "id";
        $tbl_init_code = "501";
        $history_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
        /*
        |--------------------------------------------------------------------------
        | Insert to table histories
        |--------------------------------------------------------------------------
        */
        $m_history = new ModelHistories();
        $m_history->id = $history_id;
        $m_history->action = $action;
        $m_history->initial = $initial;
        $m_history->codes_id = $tbl_init_code;
        $m_history->users_persons_id = Auth::user()->persons_id;
        $m_history->save();

        return $history_id;
    }
}
