<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Model\ModelTransactions;

abstract class TransactionsCtrl extends Controller
{
    abstract public function index(Request $request);
    /*
    |--------------------------------------------------------------------------
    | Insert into transactions
    |--------------------------------------------------------------------------
    */
    protected static function createData($data, $transactions_id, $tbl_init_code)
    {
        $m_tr = new ModelTransactions();
        $m_tr->id = $transactions_id;
        $m_tr->total = $data->total;
        $m_tr->discount = $data->discount;
        $m_tr->grand_total = $data->grand_total;
        $m_tr->codes_id = $tbl_init_code;
        $m_tr->users_persons_id = Auth::user()->persons_id;
        $m_tr->save();
    }
}
