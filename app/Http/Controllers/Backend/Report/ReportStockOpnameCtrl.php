<?php

namespace App\Http\Controllers\Backend\Report;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Libraries\ArrayManage;
use App\Http\Controllers\Controller;
use App\Model\ModelMedicines;
use App\Model\ModelStockOpname;
use App\Model\ModelItems;
use DB;

class ReportStockOpnameCtrl extends Controller
{
    public function index()
    {
        return view('layouts.adminLayout');
    }

    public function readDataList(Request $request)
    {
        $state = $request->get('state');
        $date_start = $request->get('date_start');
        $date_end = $request->get('date_end');
        $item = $request->get('item');
        $status = $request->get('status');
        /*
        |--------------------------------------------------------------------------
        | Grouping state definition
        |--------------------------------------------------------------------------
        |
        | - Code: 0 -> Recap (Rekap)
        | - Code: 1 -> Customer (Pelanggan)
        | - Code: 2 -> Item (Barang)
        | - Code: 3 -> Closing Cashier (Tutup Kasir)
        |
        */
        switch ($state) {
            case 0:
                $data = $this->readDataStockOpnameRecapList($date_start, $date_end, $item);
                break;
            case 1:
                $data = $this->readDataStockOpnameMedicineList($date_start, $date_end, $item, $status);
                break;
            default:
                break;
        }

        return response($data);
    }

    public function readDataStockOpnameRecapList($date_start, $date_end, $item) 
    {
        $data = ModelStockOpname::select(
            'i.id',
            'i.name',
            'c.name as category',
            'p.name as cashier_name',
            'stock_opnames.stock_in_system',
            'stock_opnames.stock_in_physic',
            'stock_opnames.stock_difference',
            'stock_opnames.price_purchase_app',
            'stock_opnames.price_purchase_phx',
            'stock_opnames.price_sell_app',
            'stock_opnames.price_sell_phx',
            DB::raw('(stock_opnames.price_purchase_app * stock_opnames.stock_in_system) as total_purchase_app'),
            DB::raw('(stock_opnames.price_purchase_app * stock_opnames.stock_in_physic) as total_purchase_phx'),
            DB::raw('(stock_opnames.price_purchase_app * stock_opnames.stock_difference) as total_purchase_diff'),
            DB::raw('(stock_opnames.price_sell_app * stock_opnames.stock_in_system) as total_sell_app'),
            DB::raw('(stock_opnames.price_sell_app * stock_opnames.stock_in_physic) as total_sell_phx'),
            DB::raw('(stock_opnames.price_sell_app * stock_opnames.stock_difference) as total_sell_diff'),
            'stock_opnames.created_at'
        )
        ->join('items as i', 'i.id', '=', 'stock_opnames.medicines_items_id')
        ->join('categories as c', 'c.id', '=', 'i.categories_id')
        ->join('persons as p', 'p.id', '=', 'stock_opnames.users_persons_id')
        ->whereNull('i.deleted_at')
        ->whereNull('stock_opnames.deleted_at')
        ->where(function($q) use ($date_start, $date_end) {
            if(!empty($date_start) && !empty($date_end)) {
                $q->whereBetween(DB::raw('DATE_FORMAT(stock_opnames.created_at, "%Y-%m-%d")'), [$date_start, $date_end]);
            }
        })
        ->where(function($q) use ($item) {
            if(!empty($item)) {
                $q->where("i.name", "LIKE", "%{$item}%");
            }
        })
        ->orderBy('i.name', 'asc')
        ->orderBy('stock_opnames.created_at', 'desc')
        ->paginate(25);

        return $data;
    }

    public function readDataStockOpnameMedicineList($date_start, $date_end, $item, $status) 
    {
        $temp = ModelItems::select(
            'items.id',
            'items.name',
            'c.name as category',
            'u.name as unit',
            DB::raw('(
                select 
                    md.price_sell
                FROM medicine_details md
                where md.medicines_items_id = items.id
                order by md.id desc
                limit 1
            ) as price_sell'),
            DB::raw('(
                select 
                    p.name 
                FROM stock_opnames so
                join persons p on p.id = so.users_persons_id
                where so.deleted_at is null
                and DATE_FORMAT(so.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" and "'.$date_end.'"
                and so.medicines_items_id = items.id
                limit 1
            ) as cashier_name'),
            DB::raw('(
                select 
                    so.created_at 
                FROM stock_opnames so
                where so.deleted_at is null
                and DATE_FORMAT(so.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" and "'.$date_end.'"
                and so.medicines_items_id = items.id
                limit 1
            ) as created_at'),
            DB::raw('(
                select 
                    so.stock_in_system 
                FROM stock_opnames so
                where so.deleted_at is null
                and DATE_FORMAT(so.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" and "'.$date_end.'"
                and so.medicines_items_id = items.id
                limit 1
            ) as stock_in_system'),
            DB::raw('(
                select 
                    so.stock_in_physic 
                FROM stock_opnames so
                where so.deleted_at is null
                and DATE_FORMAT(so.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" and "'.$date_end.'"
                and so.medicines_items_id = items.id
                limit 1
            ) as stock_in_physic'),
            DB::raw('(
                select 
                    so.stock_difference 
                FROM stock_opnames so
                where so.deleted_at is null
                and DATE_FORMAT(so.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" and "'.$date_end.'"
                and so.medicines_items_id = items.id
                limit 1
            ) as stock_difference'),
            DB::raw('(
                CASE
                WHEN (
                    select 
                        so.stock_in_system 
                    FROM stock_opnames so
                    where so.deleted_at is null
                    and DATE_FORMAT(so.created_at, "%Y-%m-%d") BETWEEN "'.$date_start.'" and "'.$date_end.'"
                    and so.medicines_items_id = items.id
                    limit 1
                ) IS NOT NULL THEN "done"
                ELSE "none"
                END
            ) as status')
        )
        ->join('medicines as m', 'm.items_id', '=', 'items.id')
        ->join('categories as c', 'c.id', '=', 'items.categories_id')
        ->join('units as u', 'u.id', '=', 'm.units_id')
        ->whereNull('items.deleted_at')
        ->orderBy('items.name', 'asc')
        ->get();

        $items = [];

        foreach ($temp as $key => $value) {
            switch ($status) {
                case 'all':
                    $items[] = $value;
                    break;
                case 'done':
                    if ($value->status == 'done') {
                        $items[] = $value;
                    }
                    break;
                case 'none':
                    if ($value->status == 'none') {
                        $items[] = $value;
                    }
                    break;
                default:
                    break;
            }
        }

        $data = [
            "total" => count($items),
            "data" => $items
        ];

        return $data;
    }
}
