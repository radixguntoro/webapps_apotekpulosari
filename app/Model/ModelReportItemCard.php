<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelReportItemCard extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Read total qty log
    |--------------------------------------------------------------------------
    */
    public static function readLogQty($medicine_id, $date_start, $date_end)
    {
        $sales_reg = DB::table('tr_sales_regular_details as treg_d')
            ->select(
                'treg_d.id as id',
                't.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                DB::raw('"-" as person'),
                'csh.name as cashier',
                'treg_d.qty as qty',
                'treg_d.price as price',
                'treg_d.discount as discount',
                'treg_d.subtotal as subtotal',
                DB::raw('"stock_out" as status'),
                DB::raw('"sales_regular" as title'),
                DB::raw('t.created_at as date')
            )
            ->join('medicines as m', 'm.items_id', '=', 'treg_d.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('transactions as t', 't.id', '=', 'treg_d.tr_sales_regular_id')
            ->join('persons as csh', 'csh.id', '=', 't.users_persons_id')
            ->where('treg_d.medicines_items_id', $medicine_id)
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('t.deleted_at');

        $sales_net = DB::table('tr_sales_netto_details as tnet_d')
            ->select(
                'tnet_d.id as id',
                't.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                'cli.name as person',
                'csh.name as cashier',
                'tnet_d.qty as qty',
                'tnet_d.price as price',
                'tnet_d.discount as discount',
                'tnet_d.subtotal as subtotal',
                DB::raw('"stock_out" as status'),
                DB::raw('"sales_netto" as title'),
                DB::raw('t.created_at as date')
            )
            ->join('medicines as m', 'm.items_id', '=', 'tnet_d.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('tr_sales_netto as tnet', 'tnet.tr_sales_transactions_id', '=', 'tnet_d.tr_sales_netto_id')
            ->join('transactions as t', 't.id', '=', 'tnet_d.tr_sales_netto_id')
            ->join('persons as cli', 'cli.id', '=', 'tnet.customers_persons_id')
            ->join('persons as csh', 'csh.id', '=', 't.users_persons_id')
            ->where('tnet_d.medicines_items_id', $medicine_id)
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('t.deleted_at');

        $sales_cre = DB::table('tr_sales_credit_details as tcre_d')
            ->select(
                'tcre_d.id as id',
                't.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                'cli.name as person',
                'csh.name as cashier',
                'tcre_d.qty as qty',
                'tcre_d.price as price',
                'tcre_d.discount as discount',
                'tcre_d.subtotal as subtotal',
                DB::raw('"stock_out" as status'),
                DB::raw('"sales_credit" as title'),
                DB::raw('t.created_at as date')
            )
            ->join('medicines as m', 'm.items_id', '=', 'tcre_d.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('tr_sales_credit as tcre', 'tcre.tr_sales_transactions_id', '=', 'tcre_d.tr_sales_credit_id')
            ->join('transactions as t', 't.id', '=', 'tcre_d.tr_sales_credit_id')
            ->join('persons as cli', 'cli.id', '=', 'tcre.customers_persons_id')
            ->join('persons as csh', 'csh.id', '=', 't.users_persons_id')
            ->where('tcre_d.medicines_items_id', $medicine_id)
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('t.deleted_at');
            
        $sales_lab = DB::table('tr_sales_lab_details as tlab_d')
            ->select(
                'tlab_d.id as id',
                't.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                DB::raw('COALESCE(tlab.patient, "-") as person'),
                'csh.name as cashier',
                'tlab_d.qty as qty',
                'tlab_d.price as price',
                'tlab_d.discount as discount',
                'tlab_d.subtotal as subtotal',
                DB::raw('"stock_out" as status'),
                DB::raw('"sales_lab" as title'),
                DB::raw('t.created_at as date')
            )
            ->join('medicines as m', 'm.items_id', '=', 'tlab_d.medicines_items_id')
            ->join('tr_sales_lab as tlab', 'tlab.tr_sales_transactions_id', '=', 'tlab_d.tr_sales_lab_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('transactions as t', 't.id', '=', 'tlab_d.tr_sales_lab_id')
            ->join('persons as csh', 'csh.id', '=', 't.users_persons_id')
            ->where('tlab_d.medicines_items_id', $medicine_id)
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('t.deleted_at');

        $sales_rec = DB::table('tr_sales_recipe_details as trec_d')
            ->select(
                'trec_d.id as id',
                't.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                DB::raw('COALESCE(trec.patient, "-") as person'),
                'csh.name as cashier',
                'trec_d.qty as qty',
                'trec_d.price as price',
                'trec_d.discount as discount',
                'trec_d.subtotal as subtotal',
                DB::raw('"stock_out" as status'),
                DB::raw('"sales_recipe" as title'),
                DB::raw('t.created_at as date')
            )
            ->join('tr_sales_recipe_medicines as trec_m', 'trec_m.id', '=', 'trec_d.tr_sales_recipe_medicines_id')
            ->join('tr_sales_recipe as trec', 'trec.tr_sales_transactions_id', '=', 'trec_m.tr_sales_recipe_id')
            ->join('medicines as m', 'm.items_id', '=', 'trec_d.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('transactions as t', 't.id', '=', 'trec_m.tr_sales_recipe_id')
            ->join('persons as csh', 'csh.id', '=', 't.users_persons_id')
            ->where('trec_d.medicines_items_id', $medicine_id)
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('t.deleted_at');

        $sales_mix = DB::table('tr_sales_mix_details as tmix_d')
            ->select(
                'tmix_d.id as id',
                't.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                DB::raw('COALESCE(tmix.patient, "-") as person'),
                'csh.name as cashier',
                'tmix_d.qty as qty',
                'tmix_d.price as price',
                'tmix_d.discount as discount',
                'tmix_d.subtotal as subtotal',
                DB::raw('"stock_out" as status'),
                DB::raw('"sales_mix" as title'),
                DB::raw('t.created_at as date')
            )
            ->join('tr_sales_mix_medicines as tmix_m', 'tmix_m.id', '=', 'tmix_d.tr_sales_mix_medicines_id')
            ->join('tr_sales_mix as tmix', 'tmix.tr_sales_transactions_id', '=', 'tmix_m.tr_sales_mix_id')
            ->join('medicines as m', 'm.items_id', '=', 'tmix_d.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('transactions as t', 't.id', '=', 'tmix_m.tr_sales_mix_id')
            ->join('persons as csh', 'csh.id', '=', 't.users_persons_id')
            ->where('tmix_d.medicines_items_id', $medicine_id)
            ->where('t.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('t.deleted_at');

        $ret_purchase = DB::table('returns_tr_purchases as rtp')
            ->select(
                'rtp.returns_id as id',
                'r.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                'cli.name as person',
                'csh.name as cashier',
                'rtp.qty as qty',
                'tpd.price as price',
                'tpd.discount as discount',
                'tpd.subtotal as subtotal',
                DB::raw('"stock_out" as status'),
                DB::raw('"ret_purchase" as title'),
                DB::raw('DATE_FORMAT(r.created_at, "%Y-%m-%d") as date')
            )
            ->join('returns as r', 'r.id', '=', 'rtp.returns_id')
            ->join('tr_purchase_details as tpd', 'tpd.id', '=', 'rtp.tr_purchase_details_id')
            ->join('tr_purchases as tp', 'tp.transactions_id', '=', 'rtp.tr_purchases_transactions_id')
            ->join('medicines as m', 'm.items_id', '=', 'tpd.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('transactions as t', 't.id', '=', 'rtp.tr_purchases_transactions_id')
            ->join('persons as cli', 'cli.id', '=', 'tp.suppliers_persons_id')
            ->join('persons as csh', 'csh.id', '=', 'r.users_persons_id')
            ->where('tpd.medicines_items_id', $medicine_id)
            ->where('r.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(r.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('r.deleted_at');

        $ret_netto = DB::table('returns_tr_sales_netto as rtn')
            ->select(
                'rtn.returns_id as id',
                'r.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                'cli.name as person',
                'csh.name as cashier',
                'rtn.qty as qty',
                'tsnd.price as price',
                'tsnd.discount as discount',
                'tsnd.subtotal as subtotal',
                DB::raw('"stock_in" as status'),
                DB::raw('"ret_netto" as title'),
                DB::raw('DATE_FORMAT(r.created_at, "%Y-%m-%d") as date')
            )
            ->join('returns as r', 'r.id', '=', 'rtn.returns_id')
            ->join('tr_sales_netto_details as tsnd', 'tsnd.id', '=', 'rtn.tr_sales_details_id')
            ->join('tr_sales_netto as tsn', 'tsn.tr_sales_transactions_id', '=', 'rtn.tr_sales_transactions_id')
            ->join('medicines as m', 'm.items_id', '=', 'tsnd.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('transactions as t', 't.id', '=', 'rtn.tr_sales_transactions_id')
            ->join('persons as cli', 'cli.id', '=', 'tsn.customers_persons_id')
            ->join('persons as csh', 'csh.id', '=', 'r.users_persons_id')
            ->where('tsnd.medicines_items_id', $medicine_id)
            ->where('r.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(r.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('r.deleted_at');

        $ret_sales = DB::table('returns_tr_sales as rts')
            ->select(
                'rts.returns_id as id',
                'r.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                DB::raw('"-" as person'),
                'csh.name as cashier',
                'rts.qty as qty',
                'rts.price as price',
                'rts.discount as discount',
                DB::raw('(rts.price * rts.qty) as subtotal'),
                DB::raw('"stock_in" as status'),
                DB::raw('"ret_sales" as title'),
                DB::raw('DATE_FORMAT(r.created_at, "%Y-%m-%d") as date')
            )
            ->join('returns as r', 'r.id', '=', 'rts.returns_id')
            ->join('medicines as m', 'm.items_id', '=', 'rts.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('persons as csh', 'csh.id', '=', 'r.users_persons_id')
            ->where('rts.medicines_items_id', $medicine_id)
            ->where('r.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(r.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('r.deleted_at');

        $stock_adjustments_purchase = DB::table('stock_adjustments_tr_purchases as sap')
            ->select(
                'sap.stock_adjustments_id as id',
                'sa.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                'cli.name as person',
                'csh.name as cashier',
                DB::raw('ABS(sap.qty) as qty'),
                'tpd.price as price',
                'tpd.discount as discount',
                'tpd.subtotal as subtotal',
                DB::raw('
                    CASE
                        WHEN sap.qty > 0 THEN "stock_in"
                        ELSE "stock_out"
                        END
                    as status
                '),
                DB::raw('"stock_adjustments_purchase" as title'),
                DB::raw('DATE_FORMAT(sa.created_at, "%Y-%m-%d") as date')
            )
            ->join('stock_adjustments as sa', 'sa.id', '=', 'sap.stock_adjustments_id')
            ->join('tr_purchase_details as tpd', 'tpd.id', '=', 'sap.tr_purchase_details_id')
            ->join('tr_purchases as tp', 'tp.transactions_id', '=', 'sap.tr_purchases_transactions_id')
            ->join('medicines as m', 'm.items_id', '=', 'tpd.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('transactions as t', 't.id', '=', 'sap.tr_purchases_transactions_id')
            ->join('persons as cli', 'cli.id', '=', 'tp.suppliers_persons_id')
            ->join('persons as csh', 'csh.id', '=', 'sa.users_persons_id')
            ->where('tpd.medicines_items_id', $medicine_id)
            ->where('sa.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(sa.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('sa.deleted_at');

        $stock_adjustments_sales_netto = DB::table('stock_adjustments_tr_sales_netto as sasn')
            ->select(
                'sasn.stock_adjustments_id as id',
                'sa.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                'cli.name as person',
                'csh.name as cashier',
                DB::raw('ABS(sasn.qty) as qty'),
                'tsnd.price as price',
                'tsnd.discount as discount',
                'tsnd.subtotal as subtotal',
                DB::raw('
                    CASE
                        WHEN sasn.qty > 0 THEN "stock_in"
                        ELSE "stock_out"
                        END
                    as status
                '),
                DB::raw('"stock_adjustments_sales_netto" as title'),
                DB::raw('DATE_FORMAT(sa.created_at, "%Y-%m-%d") as date')
            )
            ->join('stock_adjustments as sa', 'sa.id', '=', 'sasn.stock_adjustments_id')
            ->join('tr_sales_netto_details as tsnd', 'tsnd.id', '=', 'sasn.tr_sales_details_id')
            ->join('tr_sales_netto as tsn', 'tsn.tr_sales_transactions_id', '=', 'sasn.tr_sales_transactions_id')
            ->join('medicines as m', 'm.items_id', '=', 'tsnd.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('transactions as t', 't.id', '=', 'sasn.tr_sales_transactions_id')
            ->join('persons as cli', 'cli.id', '=', 'tsn.customers_persons_id')
            ->join('persons as csh', 'csh.id', '=', 'sa.users_persons_id')
            ->where('tsnd.medicines_items_id', $medicine_id)
            ->where('sa.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(sa.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('sa.deleted_at');

        $stock_adjustments_sales = DB::table('stock_adjustments_tr_sales as sats')
            ->select(
                'sats.stock_adjustments_id as id',
                'sa.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                DB::raw('"-" as person'),
                'csh.name as cashier',
                DB::raw('ABS(sats.qty) as qty'),
                'sats.price as price',
                'sats.discount as discount',
                DB::raw('(sats.price * ABS(sats.qty)) as subtotal'),
                DB::raw('
                    CASE
                        WHEN sats.qty > 0 THEN "stock_in"
                        ELSE "stock_out"
                        END
                    as status
                '),
                DB::raw('
                    CASE
                        WHEN sats.qty > 0 THEN "stock_adjustments_incoming_goods"
                        ELSE "stock_adjustments_exit_goods"
                        END
                    as status
                '),
                DB::raw('DATE_FORMAT(sa.created_at, "%Y-%m-%d") as date')
            )
            ->join('stock_adjustments as sa', 'sa.id', '=', 'sats.stock_adjustments_id')
            ->join('medicines as m', 'm.items_id', '=', 'sats.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('persons as csh', 'csh.id', '=', 'sa.users_persons_id')
            ->where('sats.medicines_items_id', $medicine_id)
            ->where('sa.created_at', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(sa.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('sa.deleted_at');

        $stock_opname = DB::table('stock_opnames as so')
            ->select(
                'so.id as id',
                'so.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                DB::raw('"-" as person'),
                DB::raw('"-" as cashier'),
                'so.stock_in_physic as qty',
                DB::raw('"0" as price'),
                DB::raw('"0" as discount'),
                DB::raw('"0" as subtotal'),
                DB::raw('"stock_opname" as status'),
                DB::raw('"stock_opname" as title'),
                DB::raw('so.created_at as date')
            )
            ->join('items as i', 'i.id', '=', 'so.medicines_items_id')
            ->where('so.medicines_items_id', $medicine_id)
            ->whereBetween(DB::raw('DATE_FORMAT(so.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('so.deleted_at');

        $data = DB::table('tr_purchase_details as tpd')
            ->select(
                'tpd.id as id',
                't.id as transactions_id',
                'i.name as name',
                'i.id as item_id',
                'cli.name as person',
                'csh.name as cashier',
                'tpd.qty as qty',
                'tpd.price as price',
                'tpd.discount as discount',
                'tpd.subtotal as subtotal',
                DB::raw('"stock_in" as status'),
                DB::raw('"purchase" as title'),
                't.created_at as date',
            )
            ->join('medicines as m', 'm.items_id', '=', 'tpd.medicines_items_id')
            ->join('items as i', 'i.id', '=', 'm.items_id')
            ->join('tr_purchases as tp', 'tp.transactions_id', '=', 'tpd.tr_purchases_transactions_id')
            ->join('transactions as t', 't.id', '=', 'tp.transactions_id')
            ->join('persons as cli', 'cli.id', '=', 'tp.suppliers_persons_id')
            ->join('persons as csh', 'csh.id', '=', 't.users_persons_id')
            ->where('tpd.medicines_items_id', $medicine_id)
            ->where('tp.date', '>=', '2020-12-27')
            ->whereBetween(DB::raw('DATE_FORMAT(t.created_at, "%Y-%m-%d")'), [$date_start, $date_end])
            ->whereNull('t.deleted_at')
            ->union($sales_reg)
            ->union($sales_net)
            ->union($sales_cre)
            ->union($sales_lab)
            ->union($sales_rec)
            ->union($sales_mix)
            ->union($stock_opname)
            ->union($stock_adjustments_purchase)
            ->union($stock_adjustments_sales_netto)
            ->union($stock_adjustments_sales)
            ->union($ret_purchase)
            ->union($ret_netto)
            ->union($ret_sales)
            ->orderBy('date', 'asc')
            ->get();

        return $data;
    }
}
