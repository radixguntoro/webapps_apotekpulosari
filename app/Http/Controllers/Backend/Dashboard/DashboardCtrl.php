<?php

namespace App\Http\Controllers\Backend\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ModelMedicines;
use App\Model\ModelItems;
use App\Model\ModelTrSalesRegular;
use App\Model\ModelTrSalesRegularDetails;
use App\Model\ModelTrSalesRecipe;
use App\Model\ModelTrSalesRecipeDetails;
use App\Model\ModelTrSalesMix;
use App\Model\ModelTrSalesMixDetails;
use App\Model\ModelTrSalesLab;
use App\Model\ModelTrSalesLabDetails;
use App\Model\ModelTrSalesNetto;
use App\Model\ModelTrSalesNettoDetails;
use App\Model\ModelTrSalesCredit;
use App\Model\ModelTrSalesCreditDetails;
use App\Model\ModelTrPurchases;
use App\Model\ModelTrPurchaseDetails;
use DB;

class DashboardCtrl extends Controller
{
    public function index()
    {
        return view('layouts.adminLayout');
    }
    
    public function readDataTotalAsset()
    {
        // $date_start = date('Y-m-d');
        // $items = ModelItems::select(
        //             'items.id', 
        //             'items.name',
        //             'items.status',
        //             DB::raw('(
        //                 SELECT so.stock_in_physic
        //                 FROM stock_opnames as so 
        //                 WHERE so.medicines_items_id = items.id
        //                 AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                 ORDER BY so.created_at desc
        //                 LIMIT 1
        //             ) as qty_stock_opname'),
        //             DB::raw('(
        //                 SELECT created_at 
        //                 FROM stock_opnames as so 
        //                 WHERE so.medicines_items_id = items.id
        //                 AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                 ORDER BY so.created_at desc
        //                 LIMIT 1
        //             ) as so_date'),
        //             // DB::raw('
        //             //     (
        //             //         SELECT MAX(t.created_at)
        //             //         FROM tr_sales_regular_details tsrd 
        //             //         join transactions t on t.id = tsrd.tr_sales_regular_id
        //             //         WHERE tsrd.medicines_items_id = items.id
        //             //         AND t.deleted_at is null
        //             //         AND t.created_at BETWEEN (
        //             //             SELECT created_at 
        //             //             FROM stock_opnames as so 
        //             //             WHERE so.medicines_items_id = items.id
        //             // ORDER BY so.created_at
        //             // LIMIT 1
        //             //         ) AND "'.$date_start.' 23:59:59"
        //             //     ) as sales_regular_date
        //             // '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(rtp.qty), 0)
        //                     FROM returns_tr_purchases rtp 
        //                     join tr_purchase_details tpd on tpd.id = rtp.tr_purchase_details_id
        //                     join returns r on r.id = rtp.returns_id
        //                     join transactions t on t.id = rtp.tr_purchases_transactions_id
        //                     WHERE tpd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND r.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_ret_purchases
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(rtsn.qty), 0)
        //                     FROM returns_tr_sales_netto rtsn 
        //                     join tr_sales_netto_details tsnd on tsnd.id = rtsn.tr_sales_details_id
        //                     join returns r on r.id = rtsn.returns_id
        //                     join transactions t on t.id = rtsn.tr_sales_transactions_id
        //                     WHERE tsnd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND r.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_ret_sales_netto
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(rts.qty), 0)
        //                     FROM returns_tr_sales rts 
        //                     join returns r on r.id = rts.returns_id
        //                     WHERE rts.medicines_items_id = items.id
        //                     AND r.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_ret_sales
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(tpd.qty), 0)
        //                     FROM stock_adjustments_tr_purchases satp 
        //                     join tr_purchase_details tpd on tpd.id = satp.tr_purchase_details_id
        //                     join stock_adjustments sa on sa.id = satp.stock_adjustments_id
        //                     join transactions t on t.id = satp.tr_purchases_transactions_id
        //                     WHERE tpd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND sa.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_sa_purchases
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(tsnd.qty), 0)
        //                     FROM stock_adjustments_tr_sales_netto satsn 
        //                     join tr_sales_netto_details tsnd on tsnd.id = satsn.tr_sales_transactions_id
        //                     join stock_adjustments sa on sa.id = satsn.stock_adjustments_id
        //                     join transactions t on t.id = satsn.tr_sales_transactions_id
        //                     WHERE tsnd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND sa.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_sa_sales_netto
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(sats.qty), 0)
        //                     FROM stock_adjustments_tr_sales sats 
        //                     join stock_adjustments r on r.id = sats.stock_adjustments_id
        //                     WHERE sats.medicines_items_id = items.id
        //                     AND r.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_sa_sales
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(tsrd.qty), 0)
        //                     FROM tr_sales_regular_details tsrd 
        //                     join transactions t on t.id = tsrd.tr_sales_regular_id
        //                     WHERE tsrd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND t.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_sales_regular
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(tsmd.qty), 0)
        //                     FROM tr_sales_mix_details tsmd 
        //                     join tr_sales_mix_medicines tsmm on tsmm.id = tsmd.tr_sales_mix_medicines_id
        //                     join transactions t on t.id = tsmm.tr_sales_mix_id
        //                     WHERE tsmd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND t.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_sales_mix
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(tsrd.qty), 0)
        //                     FROM tr_sales_recipe_details tsrd 
        //                     join tr_sales_recipe_medicines tsrm on tsrm.id = tsrd.tr_sales_recipe_medicines_id
        //                     join transactions t on t.id = tsrm.tr_sales_recipe_id
        //                     WHERE tsrd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND t.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_sales_recipe
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(tsld.qty), 0)
        //                     FROM tr_sales_lab_details tsld 
        //                     join transactions t on t.id = tsld.tr_sales_lab_id
        //                     WHERE tsld.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND t.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_sales_lab
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(tsnd.qty), 0)
        //                     FROM tr_sales_netto_details tsnd 
        //                     join transactions t on t.id = tsnd.tr_sales_netto_id
        //                     WHERE tsnd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND t.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_sales_netto
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(tscd.qty), 0)
        //                     FROM tr_sales_credit_details tscd 
        //                     join transactions t on t.id = tscd.tr_sales_credit_id
        //                     WHERE tscd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND t.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_sales_credit
        //             '),
        //             DB::raw('
        //                 (
        //                     SELECT COALESCE(SUM(tpd.qty), 0)
        //                     FROM tr_purchase_details tpd 
        //                     join transactions t on t.id = tpd.tr_purchases_transactions_id
        //                     WHERE tpd.medicines_items_id = items.id
        //                     AND t.deleted_at is null
        //                     AND t.created_at BETWEEN (
        //                         SELECT created_at 
        //                         FROM stock_opnames as so 
        //                         WHERE so.medicines_items_id = items.id
        //                         AND DATE_FORMAT(created_at, "%Y-%m-%d") <= "'.$date_start.'"
        //                         ORDER BY so.created_at desc
        //                         LIMIT 1
        //                     ) AND "'.$date_start.' 23:59:59"
        //                 ) as qty_purchases
        //             ')
        //         )
        //         ->with('medicines')
        //         ->whereNull('deleted_at')
        //         ->whereNotIn('name', ['BATAL'])
        //         ->where('items.status', 'active')
        //         // ->where('id', '2012000615')
        //         ->orderBy('name', 'asc')
        //         ->get();

        // $data = [];

        // foreach ($items as $key => $value) {
        //     $total_qty_sales = $value->qty_sales_regular + $value->qty_sales_mix + $value->qty_sales_recipe + $value->qty_sales_lab + $value->qty_sales_netto + $value->qty_sales_credit;
        //     $total_qty_purchases = $value->qty_purchases;
        //     $total_qty_so = $value->qty_stock_opname;
        //     $total_qty_stock_adjustments = $value->qty_sa_purchases + $value->qty_sa_sales_netto + $value->qty_sa_sales;
        //     $total_qty_ret_purchases = $value->qty_ret_purchases;
        //     $total_qty_ret_sales = $value->qty_ret_sales + $value->qty_ret_sales_netto;

        //     $data = collect($data);
        //     $data->push([
        //         "number" => $key + 1,
        //         "id" => $value->id,
        //         "name" => $value->name,
        //         "status" => $value->status,
        //         "stock" => ($total_qty_so + $total_qty_purchases + $total_qty_ret_sales + $total_qty_stock_adjustments) - ($total_qty_ret_purchases + $total_qty_sales),
        //         "price" => $value->medicines->medicineDetails->price_sell,
        //         "subtotal" => (($total_qty_so + $total_qty_purchases + $total_qty_ret_sales + $total_qty_stock_adjustments) - ($total_qty_ret_purchases + $total_qty_sales)) * $value->medicines->medicineDetails->price_sell
        //     ]);
        //     $data->all();
        // }
        
        $total_asset = ModelItems::select(
                            'items.id',
                            'items.name',
                            'm.qty_total as stock',
                            'md.price_sell as price',
                            DB::raw('(md.price_sell * m.qty_total) as subtotal'),
                        )
                        ->join('medicines as m', 'm.items_id', '=', 'items.id')
                        ->join('medicine_details as md', 'md.medicines_items_id', '=', 'items.id')
                        ->whereNull('items.deleted_at')
                        ->whereNotIn('items.name', ['BATAL'])
                        ->where('items.status', 'active')
                        ->where('md.unit', 'Tablet')
                        ->orderBy('items.name', 'asc')
                        ->get();
        // $not_match = [];
        // foreach ($data as $k_i => $v_i) {
        //     foreach ($total_asset as $k_t => $v_t) {
        //         if ($v_i['id'] == $v_t->id && ($v_i['price'] != $v_t->price)) {
        //             array_push($not_match, $v_i);
        //         }
        //     }
        // }
                        // return response()->json($items);
        $medicine_sales_regular = ModelTrSalesRegularDetails::select(DB::raw('SUM(tr_sales_regular_details.price * tr_sales_regular_details.qty) as total'))
                                    ->join('transactions as t', 't.id', '=', 'tr_sales_regular_details.tr_sales_regular_id')
                                    ->whereDate('t.created_at', '>', '2020-12-25')
                                    ->first();
        $medicine_sales_recipe = ModelTrSalesRecipeDetails::select(DB::raw('SUM(tr_sales_recipe_details.price * tr_sales_recipe_details.qty) as total'))
                                    ->join('tr_sales_recipe_medicines as tsrm', 'tsrm.id', '=', 'tr_sales_recipe_details.tr_sales_recipe_medicines_id')
                                    ->join('transactions as t', 't.id', '=', 'tsrm.tr_sales_recipe_id')
                                    ->whereDate('t.created_at', '>', '2020-12-25')
                                    ->first();
        $medicine_sales_mix = ModelTrSalesMixDetails::select(DB::raw('SUM(tr_sales_mix_details.price * tr_sales_mix_details.qty) as total'))
                                    ->join('tr_sales_mix_medicines as tsmm', 'tsmm.id', '=', 'tr_sales_mix_details.tr_sales_mix_medicines_id')
                                    ->join('transactions as t', 't.id', '=', 'tsmm.tr_sales_mix_id')
                                    ->whereDate('t.created_at', '>', '2020-12-25')
                                    ->first();
        $medicine_sales_lab = ModelTrSalesLabDetails::select(DB::raw('SUM(tr_sales_lab_details.price * tr_sales_lab_details.qty) as total'))
                                    ->join('transactions as t', 't.id', '=', 'tr_sales_lab_details.tr_sales_lab_id')
                                    ->whereDate('t.created_at', '>', '2020-12-25')
                                    ->first();
        $medicine_sales_netto = ModelTrSalesNettoDetails::select(DB::raw('SUM(tr_sales_netto_details.price * tr_sales_netto_details.qty) as total'))
                                    ->join('transactions as t', 't.id', '=', 'tr_sales_netto_details.tr_sales_netto_id')
                                    ->whereDate('t.created_at', '>', '2020-12-25')
                                    ->first();
        $medicine_sales_credit = ModelTrSalesCreditDetails::select(DB::raw('SUM(tr_sales_credit_details.price * tr_sales_credit_details.qty) as total'))
                                    ->join('transactions as t', 't.id', '=', 'tr_sales_credit_details.tr_sales_credit_id')
                                    ->whereDate('t.created_at', '>', '2020-12-25')
                                    ->first();
        $medicine_purchase = ModelTrPurchaseDetails::select(DB::raw('SUM(price * qty) as total'))
                                ->join('tr_purchases as tp', 'tp.transactions_id', '=', 'tr_purchase_details.tr_purchases_transactions_id')
                                ->whereDate('tp.date', '>', '2020-12-25')
                                ->first();

        $data = [
            'medicine_sales_regular' => is_null($medicine_sales_regular->total) ? $medicine_sales_regular->total = '0' : $medicine_sales_regular->total,
            'medicine_sales_recipe' => is_null($medicine_sales_recipe->total) ? $medicine_sales_recipe->total = '0' : $medicine_sales_recipe->total,
            'medicine_sales_mix' => is_null($medicine_sales_mix->total) ? $medicine_sales_mix->total = '0' : $medicine_sales_mix->total,
            'medicine_sales_lab' => is_null($medicine_sales_lab->total) ? $medicine_sales_lab->total = '0' : $medicine_sales_lab->total,
            'medicine_sales_netto' => is_null($medicine_sales_netto->total) ? $medicine_sales_netto->total = '0' : $medicine_sales_netto->total,
            'medicine_sales_credit' => is_null($medicine_sales_credit->total) ? $medicine_sales_credit->total = '0' : $medicine_sales_credit->total,
            'medicine_purchase' => is_null($medicine_purchase->total) ? $medicine_purchase->total = '0' : $medicine_purchase->total,
            'total_asset' => (String)$total_asset->sum('subtotal')
            // 'total_asset' => '0'
        ];

        return response()->json($data);
    }

    public function readDataTransactionPerDay()
    {
        $sales_regular = ModelTrSalesRegular::select(DB::raw('COALESCE(SUM(t.grand_total), 0) as total'))
                            ->join('transactions as t', 't.id', '=', 'tr_sales_regular.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->first();
        $sales_regular_count = ModelTrSalesRegular::select('*')
                            ->join('transactions as t', 't.id', '=', 'tr_sales_regular.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->count();
        $sales_recipe = ModelTrSalesRecipe::select(DB::raw('COALESCE(SUM(t.grand_total), 0) as total'))
                            ->join('transactions as t', 't.id', '=', 'tr_sales_recipe.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->first();
        $sales_recipe_count = ModelTrSalesRecipe::select('*')
                            ->join('transactions as t', 't.id', '=', 'tr_sales_recipe.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->count();
        $sales_mix = ModelTrSalesMix::select(DB::raw('COALESCE(SUM(t.grand_total), 0) as total'))
                            ->join('transactions as t', 't.id', '=', 'tr_sales_mix.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->first();
        $sales_mix_count = ModelTrSalesMix::select('*')
                            ->join('transactions as t', 't.id', '=', 'tr_sales_mix.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->count();
        $sales_lab = ModelTrSalesLab::select(DB::raw('COALESCE(SUM(t.grand_total), 0) as total'))
                            ->join('transactions as t', 't.id', '=', 'tr_sales_lab.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->first();
        $sales_lab_count = ModelTrSalesLab::select('*')
                            ->join('transactions as t', 't.id', '=', 'tr_sales_lab.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->count();
        $sales_netto = ModelTrSalesNetto::select(DB::raw('COALESCE(SUM(t.grand_total), 0) as total'))
                            ->join('transactions as t', 't.id', '=', 'tr_sales_netto.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->first();
        $sales_netto_count = ModelTrSalesNetto::select('*')
                            ->join('transactions as t', 't.id', '=', 'tr_sales_netto.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->count();
        $sales_credit = ModelTrSalesCredit::select(DB::raw('COALESCE(SUM(t.grand_total), 0) as total'))
                            ->join('transactions as t', 't.id', '=', 'tr_sales_credit.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->first();
        $sales_credit_count = ModelTrSalesCredit::select('*')
                            ->join('transactions as t', 't.id', '=', 'tr_sales_credit.tr_sales_transactions_id')
                            ->whereDate('t.created_at', date('Y-m-d'))
                            ->count();
        $purchase = ModelTrPurchases::select(DB::raw('COALESCE(SUM(t.grand_total), 0) as total'))
                        ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
                        ->whereDate('t.created_at', date('Y-m-d'))
                        ->first();
        $purchase_count = ModelTrPurchases::select('*')
                        ->join('transactions as t', 't.id', '=', 'tr_purchases.transactions_id')
                        ->whereDate('t.created_at', date('Y-m-d'))
                        ->count();
        
        $sales = $sales_regular->total + $sales_recipe->total + $sales_mix->total + $sales_lab->total + $sales_netto->total + $sales_credit->total;

        $data = [
            'sales_regular' => is_null($sales_regular->total) ? $sales_regular->total = '0' : $sales_regular->total,
            'sales_regular_count' => $sales_regular_count,
            'sales_recipe' => is_null($sales_recipe->total) ? $sales_recipe->total = '0' : $sales_recipe->total,
            'sales_recipe_count' => $sales_recipe_count,
            'sales_mix' => is_null($sales_mix->total) ? $sales_mix->total = '0' : $sales_mix->total,
            'sales_mix_count' => $sales_mix_count,
            'sales_lab' => is_null($sales_lab->total) ? $sales_lab->total = '0' : $sales_lab->total,
            'sales_lab_count' => $sales_lab_count,
            'sales_netto' => is_null($sales_netto->total) ? $sales_netto->total = '0' : $sales_netto->total,
            'sales_netto_count' => $sales_netto_count,
            'sales_credit' => is_null($sales_credit->total) ? $sales_credit->total = '0' : $sales_credit->total,
            'sales_credit_count' => $sales_credit_count,
            'sales' => $sales_regular->total + $sales_recipe->total + $sales_mix->total + $sales_lab->total + $sales_netto->total + $sales_credit->total,
            'purchase' => is_null($purchase->total) ? $purchase->total = '0' : $purchase->total,
            'purchase_count' => $purchase_count,
            'percentage' => $purchase->total == '0.00' || $sales == 0 ? 0 : round(($purchase->total / $sales) * 100)
        ];

        return response()->json($data);
    }
}
