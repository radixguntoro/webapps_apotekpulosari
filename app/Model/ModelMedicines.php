<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelMedicines extends Model
{
    protected $table = 'medicines';
    protected $primaryKey = 'items_id';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearch($search, $row, $sort_name, $filter_category, $filter_unit)
    {
        $order_by = empty($sort_name) ? "medicines.items_id" : "items.name";
        $sort_name = empty($sort_name) ? "desc" : $sort_name;
        $filter_category = empty($filter_category) ? [] : $filter_category;
        $filter_unit = empty($filter_unit) ? [] : $filter_unit;

        $data = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.image_cover as image',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'units.name as unitName',
                'units.id as unitId',
                'persons.id as supplierPersonsId',
                'persons.name as supplierPersonsName',
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as barcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.id) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletId'),
                DB::raw('(
                    SELECT MAX(medicine_details.unit) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletUnit'),
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletBarcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.qrcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQrcode'),
                DB::raw('(
                    SELECT medicine_details.price_sell
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('(
                    SELECT medicine_details.price_purchase
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPricePurchaseInItem'),
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
                as tabletPricePurchase'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchase_details.qty
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchase_details.qty
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as tabletQty'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_percent) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitPercent'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_value) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitValue'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchase_details.discount
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchase_details.discount
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as discount'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchases.ppn
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchases.ppn
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as ppn')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('persons', 'persons.id', '=', 'medicines.suppliers_persons_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->join('units', 'units.id', '=', 'medicines.units_id')
            ->join('medicine_details', 'medicine_details.medicines_items_id', '=', 'medicines.items_id')
            ->where("items.name", "LIKE", "%{$search}%")
            ->where('medicine_details.unit', 'Tablet')
            ->whereNull('items.deleted_at')
            ->orWhere("medicine_details.barcode", "LIKE", "%{$search}%")
            ->where('medicine_details.unit', 'Tablet')
            ->whereNull('items.deleted_at')
            ->orWhere("medicines.items_id", "LIKE", "%{$search}%")
            ->where('medicine_details.unit', 'Tablet')
            ->whereNull('items.deleted_at')
            ->whereNotIn('categories.id', $filter_category)
            ->whereNotIn('units.id', $filter_unit)
            ->orderBy($order_by, $sort_name)
            ->paginate($row);

        return $data;
    }
    public static function readDataByPagination($row, $sort_name, $filter_category, $filter_unit)
    {
        $order_by = empty($sort_name) ? "medicines.items_id" : "items.name";
        $sort_name = empty($sort_name) ? "desc" : $sort_name;
        $filter_category = empty($filter_category) ? [] : $filter_category;
        $filter_unit = empty($filter_unit) ? [] : $filter_unit;
        // echo $filter;
        $data = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.image_cover as image',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'units.name as unitName',
                'units.id as unitId',
                'persons.id as supplierPersonsId',
                'persons.name as supplierPersonsName',
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as barcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.id) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletId'),
                DB::raw('(
                    SELECT MAX(medicine_details.unit) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletUnit'),
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletBarcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.qrcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQrcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.price_sell) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('(
                    SELECT MAX(medicine_details.price_purchase) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPricePurchase'),
                DB::raw('(
                    SELECT MAX(medicine_details.qty) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQty'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_percent) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitPercent'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_value) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitValue')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('persons', 'persons.id', '=', 'medicines.suppliers_persons_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->join('units', 'units.id', '=', 'medicines.units_id')
            ->join('medicine_details', 'medicine_details.medicines_items_id', '=', 'medicines.items_id')
            ->whereNull('items.deleted_at')
            ->where('medicine_details.unit', 'Tablet')
            // ->whereNull('medicine_details.barcode')
            // ->where('medicines.qty_total', '=', 0)
            ->whereNotIn('categories.id', $filter_category)
            ->whereNotIn('units.id', $filter_unit)
            ->orderBy($order_by, $sort_name)
            ->paginate($row);

        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public static function readDataById($id)
    {
        $data = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.image_cover as image',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'units.name as unitName',
                'units.id as unitId',
                'persons.id as supplierPersonsId',
                'persons.name as supplierPersonsName',
                DB::raw('(
                    SELECT MAX(medicine_details.id) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletId'),
                DB::raw('(
                    SELECT MAX(medicine_details.unit) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletUnit'),
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletBarcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.qrcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQrcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.price_sell) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('(
                    SELECT MAX(medicine_details.price_purchase) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPricePurchase'),
                DB::raw('(
                    SELECT MAX(medicine_details.qty) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQty'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_percent) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitPercent'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_value) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitValue')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('persons', 'persons.id', '=', 'medicines.suppliers_persons_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->join('units', 'units.id', '=', 'medicines.units_id')
            ->where('medicines.items_id', $id)
            ->whereNull('items.deleted_at')
            ->orderBy('medicines.items_id', 'desc')
            ->first();
        return $data;
    }
    /*
    |--------------------------------------------------------------------------
    | Read data by autocomplete
    |--------------------------------------------------------------------------
    */
    public static function readDataByAutocomplete($type)
    {
        $data = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.image_cover as image',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'persons.id as supplierPersonsId',
                'persons.name as supplierPersonsName',
                DB::raw('(
                    SELECT MAX(medicine_details.id) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletId'),
                DB::raw('(
                    SELECT MAX(medicine_details.unit) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletUnit'),
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletBarcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.qrcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQrcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.price_sell) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('(
                    SELECT MAX(medicine_details.price_purchase) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPricePurchase'),
                DB::raw('(
                    SELECT MAX(medicine_details.qty) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQty'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_percent) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitPercent'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_value) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitValue')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('persons', 'persons.id', '=', 'medicines.suppliers_persons_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->where("items.name", "LIKE", "%{$type}%")
            ->whereNull('items.deleted_at')
            ->orderBy('medicines.items_id', 'desc')
            ->paginate(10);

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Read data by pagination and filter
    |--------------------------------------------------------------------------
    */
    public static function readDataBySearchClient($search, $row, $sort_name, $filter_category, $filter_unit)
    {
        $order_by = empty($sort_name) ? "items.name" : "items.name";
        $sort_name = empty($sort_name) ? "asc" : $sort_name;
        $filter_category = empty($filter_category) ? [] : $filter_category;
        $filter_unit = empty($filter_unit) ? [] : $filter_unit;

        $data = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.image_cover as image',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'units.name as unitName',
                'units.id as unitId',
                'persons.id as supplierPersonsId',
                'persons.name as supplierPersonsName',
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as barcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.id) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletId'),
                DB::raw('(
                    SELECT MAX(medicine_details.unit) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletUnit'),
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletBarcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.qrcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQrcode'),
                DB::raw('(
                    SELECT medicine_details.price_sell
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('(
                    SELECT medicine_details.price_purchase
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPricePurchaseInItem'),
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
                as tabletPricePurchase'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchase_details.qty
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchase_details.qty
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as tabletQty'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_percent) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitPercent'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_value) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitValue'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchase_details.discount
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchase_details.discount
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as discount'),
                DB::raw('CASE 
                WHEN (
                    SELECT tr_purchases.ppn
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) is NULL THEN 0
                ELSE (
                    SELECT tr_purchases.ppn
                    FROM tr_purchase_details 
                    JOIN tr_purchases on tr_purchases.transactions_id = tr_purchase_details.tr_purchases_transactions_id 
                    WHERE tr_purchase_details.medicines_items_id = medicines.items_id
                    AND tr_purchase_details.unit = "tablet"
                    ORDER BY tr_purchases.date desc
                    LIMIT 1
                ) END
                as ppn')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('persons', 'persons.id', '=', 'medicines.suppliers_persons_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->join('units', 'units.id', '=', 'medicines.units_id')
            ->join('medicine_details', 'medicine_details.medicines_items_id', '=', 'medicines.items_id')
            ->where("items.name", "LIKE", "%{$search}%")
            ->where('medicine_details.unit', 'Tablet')
            ->whereNull('items.deleted_at')
            ->orWhere("medicine_details.barcode", "LIKE", "%{$search}%")
            ->where('medicine_details.unit', 'Tablet')
            ->whereNull('items.deleted_at')
            ->orWhere("medicines.items_id", "LIKE", "%{$search}%")
            ->where('medicine_details.unit', 'Tablet')
            ->whereNull('items.deleted_at')
            ->where('medicine_details.unit', 'Tablet')
            ->whereIn('categories.id', [1002000005])
            ->orderBy($order_by, $sort_name)
            ->paginate($row);

        return $data;
    }
    public static function readDataByPaginationClient($row, $sort_name, $filter_category, $filter_unit)
    {
        $order_by = empty($sort_name) ? "items.name" : "items.name";
        $sort_name = empty($sort_name) ? "asc" : $sort_name;
        $filter_category = empty($filter_category) ? [] : $filter_category;
        $filter_unit = empty($filter_unit) ? [] : $filter_unit;
        // echo $filter;
        $data = DB::table('medicines')
            ->select(
                'medicines.items_id as id',
                'medicines.qty_total as qtyTotal',
                'medicines.qty_min as qtyMin',
                'items.name as name',
                'items.status as status',
                'items.image_cover as image',
                'categories.name as categoryName',
                'categories.id as categoryId',
                'units.name as unitName',
                'units.id as unitId',
                'persons.id as supplierPersonsId',
                'persons.name as supplierPersonsName',
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as barcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.id) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletId'),
                DB::raw('(
                    SELECT MAX(medicine_details.unit) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletUnit'),
                DB::raw('(
                    SELECT MAX(medicine_details.barcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletBarcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.qrcode) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQrcode'),
                DB::raw('(
                    SELECT MAX(medicine_details.price_sell) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPriceSell'),
                DB::raw('(
                    SELECT MAX(medicine_details.price_purchase) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletPricePurchase'),
                DB::raw('(
                    SELECT MAX(medicine_details.qty) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletQty'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_percent) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitPercent'),
                DB::raw('(
                    SELECT MAX(medicine_details.profit_value) 
                    FROM medicine_details 
                    WHERE medicine_details.medicines_items_id = medicines.items_id
                    AND medicine_details.unit = "tablet"
                ) as tabletProfitValue')
            )
            ->join('items', 'items.id', '=', 'medicines.items_id')
            ->join('persons', 'persons.id', '=', 'medicines.suppliers_persons_id')
            ->join('categories', 'categories.id', '=', 'items.categories_id')
            ->join('units', 'units.id', '=', 'medicines.units_id')
            ->join('medicine_details', 'medicine_details.medicines_items_id', '=', 'medicines.items_id')
            ->whereNull('items.deleted_at')
            ->where('medicine_details.unit', 'Tablet')
            ->whereIn('categories.id', [1002000005])
            ->orderBy($order_by, $sort_name)
            ->paginate($row);

        return $data;
    }

    public function item()
    {
        $data = $this->belongsTo(ModelItems::class, 'items_id')->with('category');
        return $data;
    }
    public function medicineDetails()
    {
        $data = $this->hasOne(ModelMedicineDetails::class, 'medicines_items_id')->where('unit', 'Tablet');
        return $data;
    }
    public function trSalesRegularDetails()
    {
        $data = $this->hasMany(ModelTrSalesRegularDetails::class, 'medicines_items_id')->orderBy('qty', 'desc');
        return $data;
    }
    public function trSalesMixDetails()
    {
        $data = $this->hasMany(ModelTrSalesMixDetails::class, 'medicines_items_id');
        return $data;
    }
    public function trSalesRecipeDetails()
    {
        $data = $this->hasMany(ModelTrSalesRecipeDetails::class, 'medicines_items_id');
        return $data;
    }
    public function trSalesLabDetails()
    {
        $data = $this->hasMany(ModelTrSalesLabDetails::class, 'medicines_items_id');
        return $data;
    }
    public function trSalesNettoDetails()
    {
        $data = $this->hasMany(ModelTrSalesNettoDetails::class, 'medicines_items_id');
        return $data;
    }
    public function trSalesCreditDetails()
    {
        $data = $this->hasMany(ModelTrSalesCreditDetails::class, 'medicines_items_id');
        return $data;
    }
    public function trPurchaseDetails()
    {
        $data = $this->hasOne(ModelTrPurchaseDetails::class, 'medicines_items_id');
        return $data;
    }
    public function stockOpname()
    {
        $data = $this->hasOne(ModelStockOpname::class, 'medicines_items_id');
        return $data;
    }
}
