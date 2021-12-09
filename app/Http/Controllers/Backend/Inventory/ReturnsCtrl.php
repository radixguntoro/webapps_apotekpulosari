<?php

namespace App\Http\Controllers\Backend\Inventory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateNumber;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Backend\History\HistoriesCtrl;
use App\Model\ModelReturns;
use App\Model\ModelReturnDetails;
use App\Model\ModelReturnTrPurchases;
use App\Model\ModelReturnTrPurchasesDetails;
use App\Model\ModelReturnSales;
use App\Model\ModelTrPurchases;
use App\Model\ModelTrPurchaseDetails;
use App\Model\ModelMedicines;
use DB;

class ReturnsCtrl extends Controller
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
        $state = $request->get('state');
        $filter = explode(',', trim($request->get('filter')));

        switch ($state) {
            case 'trPurchase':
                $data = $this->readDataByStateTrPurchase($search, $row, $sort, $filter);
                break;
            case 'trSales':
                $data = $this->readDataByStateTrSales($search, $row, $sort, $filter);
                break;
            default:
                break;
        }
        return $data;
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
            $tbl_name = "returns";
            $tbl_primary_key = "id";
            $tbl_init_code = "401";
            $returns_id = GenerateNumber::generateDayCode($tbl_name, $tbl_primary_key, 1, $tbl_init_code);
            $return = json_decode(json_encode($request->return));
            // dd($return);
            /*
            |--------------------------------------------------------------------------
            | Insert data at table return
            |--------------------------------------------------------------------------
            */
            $m_return = new ModelReturns();
            $m_return->id = $returns_id;
            $m_return->codes_id = $tbl_init_code;
            $m_return->users_persons_id = Auth::user()->persons_id;
            $m_return->total = $return->total;
            $m_return->discount = $return->discount;
            $m_return->ppn = $return->ppn;
            $m_return->grand_total = $return->grandTotal;
            $m_return->save();
            /*
            |--------------------------------------------------------------------------
            | Insert data at table return_tr_purchases or return_tr_sales
            |--------------------------------------------------------------------------
            */
            if ($return->state == 'trPurchase') {
                $m_return_purchase = new ModelReturnTrPurchases;
                $m_return_purchase->returns_id = $returns_id;
                $m_return_purchase->tr_purchases_transactions_id = $return->trPurchaseId;
                $m_return_purchase->save();
            } else {
                $m_return_sales = new ModelReturnSales;
                $m_return_sales->returns_id = $returns_id;
                $m_return_sales->tr_sales_transactions_id = $return->trSalesId;
                $m_return_sales->save();
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table return_details
            |--------------------------------------------------------------------------
            */
            foreach ($return->details as $key => $val) {
                $m_return_detail = new ModelReturnDetails;
                $m_return_detail->qty = $val->qty;
                $m_return_detail->note = empty($val->note) || isset($val->note) ? '' : $val->note;
                $m_return_detail->returns_id = $returns_id;
                $m_return_detail->medicines_items_id = $val->medicineId;
                $m_return_detail->save();
                if ($return->state == 'trPurchase') {
                    ModelMedicines::where('items_id', $val->medicineId)->decrement('qty_total', $val->qty);
                } else {
                    ModelMedicines::where('items_id', $val->medicineId)->increment('qty_total', $val->qty);
                }
            }
            /*
            |--------------------------------------------------------------------------
            | Insert data at table histories
            |--------------------------------------------------------------------------
            */
            $action = [
                "action" => "Membuat retur pembelian",
                "returns_id" => $returns_id,
                "content" => $return
            ];

            if ($return->state == 'trPurchase') {
                $initial = "return_purchase_created";
            } else {
                $initial = "return_sales_created";
            }

            HistoriesCtrl::createData(json_encode($action), $initial);
            
            DB::commit();
            return response()->json($resp = ["status" => 1, "result" => 'success', "id" => $returns_id]);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by state TrPurchase
    |--------------------------------------------------------------------------
    */
    protected function readDataByStateTrPurchase($search, $row, $sort, $filter)
    {
        if ($search) {
            $data = ModelReturnTrPurchases::readDataBySearch($search, $row, $sort, $filter);
        } else {
            $data = ModelReturnTrPurchases::readDataByPagination($row, $sort, $filter);
        }
        return response($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by state TrSales
    |--------------------------------------------------------------------------
    */
    protected function readDataByStateTrSales($search, $row, $sort, $filter)
    {
        if ($search) {
            $data = ModelReturnSales::readDataBySearch($search, $row, $sort, $filter);
        } else {
            $data = ModelReturnSales::readDataByPagination($row, $sort, $filter);
        }
        return response($data);
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by state and id
    |--------------------------------------------------------------------------
    */
    protected function readDataById(Request $request)
    {
        $id = $request->get('id');
        $state = $request->get('state');

        switch ($state) {
            case 'trPurchase':
                $data = $this->readDataByStateTrPurchaseWithId($id);
                break;
            case 'trSales':
                $data = $this->readDataByStateTrSalesWithId($id);
                break;
            default:
                break;
        }
        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by state TrPurchase and id
    |--------------------------------------------------------------------------
    */
    protected function readDataByStateTrPurchaseWithId($id)
    {
        $rtn_parent = ModelReturnTrPurchases::readDataById($id);
        $rtn_detail = ModelReturnTrPurchasesDetails::readDataById($id);

        $trp_parent = ModelTrPurchases::readDataById($rtn_parent->trPurchaseId);
        $trp_detail = ModelTrPurchaseDetails::readDataById($rtn_parent->trPurchaseId);

        $tr_purchase['invoiceNumber'] = $trp_parent->invoiceNumber;
        $tr_purchase['supplierName'] = $trp_parent->supplierName;
        $tr_purchase['supplierPhone'] = $trp_parent->supplierPhone;
        $tr_purchase['date'] = $trp_parent->date;
        $tr_purchase['createdAt'] = $trp_parent->createdAt;
        $tr_purchase['timeAt'] = $trp_parent->timeAt;
        $tr_purchase['discount'] = $trp_parent->discount;
        $tr_purchase['ppn'] = $trp_parent->ppn;
        $tr_purchase['grandTotal'] = $trp_parent->grandTotal;
        $tr_purchase['id'] = $trp_parent->id;
        $tr_purchase['total'] = $trp_parent->total;
        $tr_purchase['userName'] = $trp_parent->userName;
        $tr_purchase['details'] = $trp_detail;
        
        $return['invoiceNumber'] = $rtn_parent->invoiceNumber;
        $return['supplierName'] = $rtn_parent->supplierName;
        $return['supplierPhone'] = $rtn_parent->supplierPhone;
        $return['date'] = $rtn_parent->date;
        $return['discount'] = $rtn_parent->discount;
        $return['ppn'] = $rtn_parent->ppn;
        $return['grandTotal'] = $rtn_parent->grandTotal;
        $return['id'] = $rtn_parent->id;
        $return['total'] = $rtn_parent->total;
        $return['userName'] = $rtn_parent->userName;
        $return['details'] = $rtn_detail;

        $data = [
            'trPurchase' => $tr_purchase,
            'trPurchaseReturn' => $return,
        ];
        
        return response($data);
    }
}
