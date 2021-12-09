<?php

namespace App\Http\Controllers\Backend\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Libraries\RespMessages;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelTrSales;
use App\Model\ModelClosingCashiers;
use App\Model\ModelClosingCashierDetails;
use App\Model\ModelReturns;
use DB;

class ClosingCashierCtrl extends Controller
{
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
        $filter = explode(',', trim($request->get('filter')));

        if ($search) {
            $data = ModelClosingCashiers::select(
                "closing_cashiers.id as id",
                DB::raw(ModelClosingCashiers::queryIncomeApp()),
                "closing_cashiers.income_real as income_real",
                DB::raw(ModelClosingCashiers::queryIncomeDiff()),
                "closing_cashiers.shift as shift",
                "closing_cashiers.created_at as created_at",
                DB::raw('DATE_FORMAT(closing_cashiers.created_at, "%H:%i") as time_at'),
                "closing_cashiers.updated_at as updated_at",
                "closing_cashiers.deleted_at as deleted_at",
                "closing_cashiers.users_persons_id as users_persons_id",
                DB::raw('(
                    SELECT count(closing_cashiers_id) as total
                    FROM closing_cashier_details
                    WHERE closing_cashier_details.closing_cashiers_id = closing_cashiers.id
                ) as nota'),
                DB::raw(ModelClosingCashiers::queryTotalReturn())
            )
            ->orderBy('closing_cashiers.id', 'desc')
            ->paginate(10);
        } else {
            $data = ModelClosingCashiers::select(
                    "closing_cashiers.id as id",
                    DB::raw(ModelClosingCashiers::queryIncomeApp()),
                    "closing_cashiers.income_real as income_real",
                    DB::raw(ModelClosingCashiers::queryIncomeDiff()),
                    "closing_cashiers.shift as shift",
                    "closing_cashiers.created_at as created_at",
                    DB::raw('DATE_FORMAT(closing_cashiers.created_at, "%H:%i") as time_at'),
                    "closing_cashiers.updated_at as updated_at",
                    "closing_cashiers.deleted_at as deleted_at",
                    "closing_cashiers.users_persons_id as users_persons_id",
                    DB::raw('(
                        SELECT count(closing_cashiers_id) as total
                        FROM closing_cashier_details
                        WHERE closing_cashier_details.closing_cashiers_id = closing_cashiers.id
                    ) as nota'),
                    DB::raw('(
                        SELECT 
                            CASE
                                WHEN closing_cashiers.shift = 1 THEN "00:01" 
                                ELSE DATE_FORMAT(ccs_1.created_at, "%H:%i") 
                                END
                                as time_at
                        FROM closing_cashiers as ccs_1
                        WHERE DATE_FORMAT(ccs_1.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                        LIMIT 1
                    ) as last_shift'),
                    DB::raw(ModelClosingCashiers::queryTotalReturn())
                )
                // ->with('closingCashierDetils')
                ->orderBy('closing_cashiers.id', 'desc')
                ->paginate(10);
        }
        return response($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Create data
    |--------------------------------------------------------------------------
    */
    protected function create(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
            |--------------------------------------------------------------------------
            | Generate Code
            |--------------------------------------------------------------------------
            */
            $tbl_name = "closing_cashiers";
            $tbl_primary_key = "id";
            $tbl_init_code = "300";
            $transactions_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            
            if ($request->segment(1) == 'api') {
                $closing_cashier = json_decode($request->closingCashier);
            } else {
                $closing_cashier = json_decode(json_encode($request->closingCashier));
            }
            // return response()->json($closing_cashier);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table closing_cashiers
            |--------------------------------------------------------------------------
            */
            $m_closing_cashier = new ModelClosingCashiers();
            $m_closing_cashier->id = $transactions_id;
            $m_closing_cashier->income_app = $closing_cashier->income_app;
            $m_closing_cashier->income_real = $closing_cashier->income_real;
            $m_closing_cashier->income_diff = $closing_cashier->income_real - $closing_cashier->income_app;
            $m_closing_cashier->shift = $closing_cashier->shift;
            $m_closing_cashier->users_persons_id = Auth::user()->persons_id;
            $m_closing_cashier->codes_id = $tbl_init_code;
            $m_closing_cashier->save();
            
            $success_saved = 0;

            foreach ($closing_cashier->sales as $val) {
                $m_closing_cashier_detail = new ModelClosingCashierDetails();
                $m_closing_cashier_detail->closing_cashiers_id = $transactions_id;
                $m_closing_cashier_detail->tr_sales_id = $val->transactions_id;
                $m_closing_cashier_detail->save();

                if ($m_closing_cashier_detail->id != null || $m_closing_cashier_detail->id != '') {
                    $success_saved ++;
                }
            }

            if (($success_saved) != count($closing_cashier->sales)) {
                return response()->json($resp = ["status" => 0, "result" => "error", "msg" => RespMessages::failRequest(), "id" => ""]);
            }

            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $transactions_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => ""]);
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Read data sales
    |--------------------------------------------------------------------------
    */
    public function readDataSalesAll(Request $request)
    {
        $row = $request->get('row');
        // $row = 1000;
        $sort = $request->get('sort');
        $date_yesterday = date('Y-m-d',strtotime(date('Y-m-d') . "-1 days"));
        $date_today = date('Y-m-d');

        $return = ModelReturns::select(
                    DB::raw('COALESCE(SUM((rts.price * rts.qty) - ((rts.price * rts.qty) * (rts.discount / 100))), 0) as total_return')
                )
                ->join('returns_tr_sales as rts', 'rts.returns_id', '=', 'returns.id')
                ->whereDate('returns.created_at', $date_yesterday)
                ->orWhereDate('returns.created_at', $date_today)
                ->first();

        // echo $return;die;
        
        $data = ModelTrSales::select(
            'tr_sales.transactions_id',
            'transactions.id',
            'transactions.total',
            'transactions.discount',
            'transactions.grand_total',
            'transactions.created_at',
            'transactions.updated_at',
            'transactions.deleted_at',
            DB::raw('DATE_FORMAT(transactions.created_at, "%H:%i") as time_at'),
            'transactions.codes_id',
            'transactions.users_persons_id',
            'closing_cashier_details.closing_cashiers_id',
        )
        ->join('transactions', 'transactions.id', '=', 'tr_sales.transactions_id')
        ->leftJoin('closing_cashier_details', 'closing_cashier_details.tr_sales_id', '=', 'tr_sales.transactions_id')
        ->leftJoin('tr_sales_credit', 'tr_sales_credit.tr_sales_transactions_id', '=', 'tr_sales.transactions_id')
        ->whereDate('transactions.created_at', $date_yesterday)
        ->whereNull('closing_cashier_details.closing_cashiers_id')
        ->whereIn('transactions.codes_id', ['301', '302', '303', '304'])
        ->orWhereIn('transactions.codes_id', ['307'])
        ->where('tr_sales_credit.status', '=', 'paid')
        ->whereNull('closing_cashier_details.closing_cashiers_id')
        ->orWhereDate('transactions.created_at', $date_today)
        ->whereNull('closing_cashier_details.closing_cashiers_id')
        ->whereIn('transactions.codes_id', ['301', '302', '303', '304'])
        ->orWhereIn('transactions.codes_id', ['307'])
        ->whereNull('closing_cashier_details.closing_cashiers_id')
        ->where('tr_sales_credit.status', '=', 'paid')
        ->orderBy('tr_sales.transactions_id', 'desc')
        ->paginate($row);

        return response($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read shift
    |--------------------------------------------------------------------------
    */
    public function readShift(Request $request)
    {
        $shift = 0;
        $date = date('Y-m-d');
        
        $data = ModelTrSales::join('transactions', 'transactions.id', '=', 'tr_sales.transactions_id')
        ->join('closing_cashier_details', 'closing_cashier_details.tr_sales_id', '=', 'tr_sales.transactions_id')
        ->join('closing_cashiers', 'closing_cashiers.id', '=', 'closing_cashier_details.closing_cashiers_id')
        ->whereDate('closing_cashiers.created_at', $date)
        ->whereNotNull('closing_cashier_details.closing_cashiers_id')
        ->orderBy('closing_cashiers.id', 'desc')
        ->first();

        if(!empty($data)) {
            $shift = intval($data->shift) + 1;
        } else {
            $shift = 1;
        }

        return response($shift);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by cashier
    |--------------------------------------------------------------------------
    */
    public function readDataByCashier(Request $request)
    {
        $closing_cashier_id = $request->get('closing_cashier_id');
        // echo $closing_cashier_id;die;
        $result = [];
        $detail = ModelClosingCashierDetails::select('*')
        ->join('transactions', 'closing_cashier_details.tr_sales_id', '=', 'transactions.id')
        ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
        ->where('closing_cashier_details.closing_cashiers_id', '=', $closing_cashier_id)
        ->get();

        $grouped = $detail->groupBy('users_persons_id');

        $grouped->toArray();

        $data = $grouped->values();

        $data->all();

        foreach($data as $key => $val) {
            $result[$key]['codes_id'] = $val->first()->codes_id;
            $result[$key]['cashier_name'] = $val->first()->name;
            $result[$key]['total'] = round($val->sum('grand_total'), 2);
            $result[$key]['details'] = $val;
        }

        $return = ModelClosingCashierDetails::select(
            DB::raw('(
                    SELECT
                        r.codes_id
                    FROM returns as r
                    JOIN returns_tr_sales as rts on rts.returns_id = r.id
                    JOIN persons as p on p.id = r.users_persons_id
                    WHERE DATE_FORMAT(r.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                    AND DATE_FORMAT(r.created_at, "%H:%i") < DATE_FORMAT(closing_cashiers.created_at, "%H:%i")
                    LIMIT 1
                ) 
            as codes_id'),
            DB::raw('(
                    SELECT
                        p.name
                    FROM returns as r
                    JOIN returns_tr_sales as rts on rts.returns_id = r.id
                    JOIN persons as p on p.id = r.users_persons_id
                    WHERE DATE_FORMAT(r.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                    AND DATE_FORMAT(r.created_at, "%H:%i") < DATE_FORMAT(closing_cashiers.created_at, "%H:%i")
                    LIMIT 1
                ) 
            as cashier_name'),
            DB::raw('COALESCE(
                (
                    SELECT 
                        SUM((rts.price * rts.qty) - ((rts.price * rts.qty) * (rts.discount / 100))) as total_return 
                    FROM returns as r
                    JOIN returns_tr_sales as rts on rts.returns_id = r.id
                    WHERE DATE_FORMAT(r.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                    AND DATE_FORMAT(r.created_at, "%H:%i") < DATE_FORMAT(closing_cashiers.created_at, "%H:%i")
                    AND DATE_FORMAT(r.created_at, "%H:%i") > (
                        SELECT 
                                CASE
                                    WHEN closing_cashiers.shift = 1 THEN "00:01" 
                                    ELSE DATE_FORMAT(ccs_1.created_at, "%H:%i") 
                                    END
                                    as time_at
                            FROM closing_cashiers as ccs_1
                            WHERE DATE_FORMAT(ccs_1.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                            LIMIT 1
                    )
                ), 0) 
            as total'),
            DB::raw('null as detail'),
        )
        ->join('transactions', 'closing_cashier_details.tr_sales_id', '=', 'transactions.id')
        ->join('closing_cashiers', 'closing_cashiers.id', '=', 'closing_cashier_details.closing_cashiers_id')
        ->where('closing_cashier_details.closing_cashiers_id', '=', $closing_cashier_id)
        ->first();

        $collection = collect($result);

        if ($return->total > 0) {
            $collection->push($return);
        }
        
        $collection->all();

        return response($collection);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by sales type
    |--------------------------------------------------------------------------
    */
    public function readDataSalesType(Request $request)
    {
        $closing_cashier_id = $request->get('closing_cashier_id');
        // echo $closing_cashier_id;die;
        $result = [];

        $detail = ModelClosingCashierDetails::select(
            'transactions.id as id',
            'transactions.total as total',
            'transactions.discount as discount',
            DB::raw('transactions.grand_total grand_total'),
            'transactions.created_at as created_at',
            'transactions.updated_at as updated_at',
            'transactions.deleted_at as deleted_at',
            'transactions.codes_id as codes_id',
            'closing_cashier_details.closing_cashiers_id as closing_cashiers_id'
        )
        ->join('transactions', 'closing_cashier_details.tr_sales_id', '=', 'transactions.id')
        ->join('persons', 'persons.id', '=', 'transactions.users_persons_id')
        ->where('closing_cashier_details.closing_cashiers_id', '=', $closing_cashier_id)
        ->get();

        $grouped = $detail->groupBy('codes_id');

        $grouped->toArray();

        $data = $grouped->values();

        $data->all();

        foreach($data as $key => $val) {
            $result[$key]['codes_id'] = $val->first()->codes_id;
            $result[$key]['title'] = $this->readSalesTypeCode($val->first()->codes_id);
            $result[$key]['total'] = $val->sum('grand_total');
            $result[$key]['details'] = $val;
        }

        $return = ModelClosingCashierDetails::select(
            DB::raw('(
                    SELECT
                        r.codes_id
                    FROM returns as r
                    JOIN returns_tr_sales as rts on rts.returns_id = r.id
                    WHERE DATE_FORMAT(r.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                    AND DATE_FORMAT(r.created_at, "%H:%i") < DATE_FORMAT(closing_cashiers.created_at, "%H:%i")
                    LIMIT 1
                ) 
            as codes_id'),
            DB::raw('"Retur" as title'),
            DB::raw('COALESCE(
                (
                    SELECT 
                        SUM((rts.price * rts.qty) - ((rts.price * rts.qty) * (rts.discount / 100))) as total_return 
                    FROM returns as r
                    JOIN returns_tr_sales as rts on rts.returns_id = r.id
                    WHERE DATE_FORMAT(r.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                    AND DATE_FORMAT(r.created_at, "%H:%i") < DATE_FORMAT(closing_cashiers.created_at, "%H:%i")
                    AND DATE_FORMAT(r.created_at, "%H:%i") > (
                        SELECT 
                                CASE
                                    WHEN closing_cashiers.shift = 1 THEN "00:01" 
                                    ELSE DATE_FORMAT(ccs_1.created_at, "%H:%i") 
                                    END
                                    as time_at
                            FROM closing_cashiers as ccs_1
                            WHERE DATE_FORMAT(ccs_1.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                            LIMIT 1
                    )
                ), 0) 
            as total'),
            DB::raw('null as detail'),
        )
        ->join('transactions', 'closing_cashier_details.tr_sales_id', '=', 'transactions.id')
        ->join('closing_cashiers', 'closing_cashiers.id', '=', 'closing_cashier_details.closing_cashiers_id')
        ->where('closing_cashier_details.closing_cashiers_id', '=', $closing_cashier_id)
        ->first();

        $collection = collect($result);

        if ($return->total > 0) {
            $collection->push($return);
        }

        $collection->all();
        
        return response($collection);

    }
    /*
    |--------------------------------------------------------------------------
    | Read sales type by codes_id
    |--------------------------------------------------------------------------
    */
    public function readSalesTypeCode($codes_id) {
        switch ($codes_id) {
			case '301':
				$codes_id = "Reguler";
				break;
			case '302':
				$codes_id = "Racik";
				break;
			case '303':
				$codes_id = "Resep";
				break;
			case '304':
				$codes_id = "Lab";
				break;
			case '307':
				$codes_id = "Kredit";
				break;
			default:
		}
		return $codes_id;
    }
    /*
    |--------------------------------------------------------------------------
    | Create data
    |--------------------------------------------------------------------------
    */
    protected function update(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($request->segment(1) == 'api') {
                $closing_cashier = json_decode($request->closingCashier);
            } else {
                $closing_cashier = json_decode(json_encode($request->closingCashier));
            }
            // return response()->json($closing_cashier);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table closing_cashiers
            |--------------------------------------------------------------------------
            */
            $m_closing_cashier = ModelClosingCashiers::find($closing_cashier->id);
            $m_closing_cashier->income_real = $closing_cashier->income_real;
            $m_closing_cashier->income_diff = $closing_cashier->income_real - $closing_cashier->income_app;
            $m_closing_cashier->save();
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "msg" => RespMessages::successCreate(), "id" => $transactions_id]);
        } catch (\Exception $e) {
            return response()->json($resp = ["status" => 0, "result" => $e->getMessage(), "msg" => RespMessages::failErrorSystem(), "id" => ""]);
            DB::rollback();
        }
    }
}
