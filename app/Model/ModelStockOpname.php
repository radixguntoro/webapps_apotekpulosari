<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use DB;

class ModelStockOpname extends Model
{
    protected $table = 'stock_opnames';
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearchMedicine($search, $row, $sort, $filter)
    {
        $month = date('m');
        $order_by = empty($sort) ? "medicines.items_id" : "items.name";
        $sort = empty($sort) ? "desc" : $sort;
        $filter = empty($filter) ? [] : $filter;

        $item_new = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.deleted_at as deleted_at',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'units.name as unitName',
                DB::raw('(
                    SELECT medicine_details.price_sell
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchase_details.price
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchase_details.price
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as tabletPricePurchase')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('medicine_details', 'medicine_details.medicines_items_id', '=', 'medicines.items_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->join('units', 'units.id', '=', 'medicines.units_id')
            ->whereNotExists(function ($query) {
                $query->select('*')
                ->from('stock_opnames as so')
                ->whereRaw('so.medicines_items_id = items.id');
            })
            ->where('medicine_details.unit', '=', 'Tablet')
            ->where("items.name", "LIKE", "%{$search}%")
            ->whereNull('items.deleted_at');

        $data_so_done = DB::table('medicines')
            ->select(
                'items.id as id',
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('stock_opnames', 'stock_opnames.medicines_items_id', '=', 'items.id')
            ->whereNull('items.deleted_at')
            ->whereYear('stock_opnames.created_at', '=', date('Y'))
            ->whereMonth('stock_opnames.created_at', '=', date('m'))
            ->where("items.name", "LIKE", "%{$search}%")
            ->groupBy(
                'items.id'
            )
            ->get();
        
        $so_done = [];

        for ($i=0; $i < count($data_so_done); $i++) { 
            $so_done[$i] = $data_so_done[$i]->id;
        }

        $data = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.deleted_at as deleted_at',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'units.name as unitName',
                DB::raw('(
                    SELECT medicine_details.price_sell
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchase_details.price
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchase_details.price
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as tabletPricePurchase')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('medicine_details', 'medicine_details.medicines_items_id', '=', 'medicines.items_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->join('units', 'units.id', '=', 'medicines.units_id')
            ->join('stock_opnames', 'stock_opnames.medicines_items_id', '=', 'items.id')
            ->where('medicine_details.unit', '=', 'Tablet')
            ->whereNull('items.deleted_at')
            ->whereNotIn('stock_opnames.medicines_items_id', $so_done)
            ->where("items.name", "LIKE", "%{$search}%")
            ->union($item_new)
            ->paginate($row);
    
        return $data;
    }
    public static function readDataByPaginationMedicine($row, $sort, $filter)
    {
        $order_by = empty($sort) ? "medicines.items_id" : "items.name";
        $sort = empty($sort) ? "desc" : $sort;
        $filter = empty($filter) ? [] : $filter;
        $month = date('m');

        $item_new = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.deleted_at as deleted_at',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'units.name as unitName',
                DB::raw('(
                    SELECT medicine_details.price_sell
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchase_details.price
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchase_details.price
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as tabletPricePurchase')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('medicine_details', 'medicine_details.medicines_items_id', '=', 'medicines.items_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->join('units', 'units.id', '=', 'medicines.units_id')
            ->whereNotExists(function ($query) {
                $query->select('*')
                ->from('stock_opnames as so')
                ->whereRaw('so.medicines_items_id = items.id');
            })
            ->where('medicine_details.unit', '=', 'Tablet')
            ->whereNull('items.deleted_at');

        $data_so_done = DB::table('medicines')
            ->select(
                'items.id as id',
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('stock_opnames', 'stock_opnames.medicines_items_id', '=', 'items.id')
            ->whereNull('items.deleted_at')
            ->whereYear('stock_opnames.created_at', '=', date('Y'))
            ->whereMonth('stock_opnames.created_at', '=', date('m'))
            ->groupBy(
                'items.id'
            )
            ->get();
        
        $so_done = [];

        for ($i=0; $i < count($data_so_done); $i++) { 
            $so_done[$i] = $data_so_done[$i]->id;
        }

        $data = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.deleted_at as deleted_at',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'units.name as unitName',
                DB::raw('(
                    SELECT medicine_details.price_sell
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchase_details.price
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchase_details.price
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as tabletPricePurchase')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('medicine_details', 'medicine_details.medicines_items_id', '=', 'medicines.items_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->join('units', 'units.id', '=', 'medicines.units_id')
            ->join('stock_opnames', 'stock_opnames.medicines_items_id', '=', 'items.id')
            ->where('medicine_details.unit', '=', 'Tablet')
            ->whereNull('items.deleted_at')
            ->whereNotIn('stock_opnames.medicines_items_id', $so_done)
            ->orderBy('items.name', 'asc')
            ->union($item_new)
            ->paginate($row);
    
        return $data;
    }

    public function medicine()
    {
        $data = $this->belongsTo(ModelMedicines::class, 'medicines_items_id')->with('item');
        return $data;
    }

    public function person()
    {
        $data = $this->belongsTo(ModelPersons::class, 'users_persons_id');
        return $data;
    }
}
