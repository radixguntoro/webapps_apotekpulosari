<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class ModelItems extends Model
{
	use SoftDeletes;
	protected $table = 'items';

	public function category()
    {
        $data = $this->belongsTo(ModelCategories::class, 'categories_id');
        return $data;
    }

	public function medicines()
    {
        $data = $this->hasOne(ModelMedicines::class, 'items_id')->with('medicineDetails');
        return $data;
    }

	public function stockOpname()
    {
        $data = $this->hasOne(ModelStockOpname::class, 'medicines_items_id')
            ->orderBy('created_at', 'desc');
        return $data;
    }

	public function trPurchases()
    {
        $data = $this->hasMany(ModelTrPurchaseDetails::class, 'medicines_items_id')
            ->join('tr_purchases as tp', 'tp.transactions_id', '=', 'tr_purchase_details.tr_purchases_transactions_id')
            ->join('transactions as t', 't.id', '=', 'tp.transactions_id');
        return $data;
    }

    public function trSalesRegular()
    {
        $data = $this->hasMany(ModelTrSalesRegularDetails::class, 'medicines_items_id')
            ->join('transactions as t', 't.id', '=', 'tr_sales_regular_details.tr_sales_regular_id');
        return $data;
    }

    public function trSalesMix()
    {
        $data = $this->hasMany(ModelTrSalesMixDetails::class, 'medicines_items_id')
            ->join('tr_sales_mix_medicines as tsmm', 'tsmm.id', '=', 'tr_sales_mix_details.tr_sales_mix_medicines_id')
            ->join('transactions as t', 't.id', '=', 'tsmm.tr_sales_mix_id');
        return $data;
    }

    public function trSalesRecipe()
    {
        $data = $this->hasMany(ModelTrSalesRecipeDetails::class, 'medicines_items_id')
            ->join('tr_sales_recipe_medicines as tsrm', 'tsrm.id', '=', 'tr_sales_recipe_details.tr_sales_recipe_medicines_id')
            ->join('transactions as t', 't.id', '=', 'tsrm.tr_sales_recipe_id');
        return $data;
    }

    public function trSalesLab()
    {
        $data = $this->hasMany(ModelTrSalesLabDetails::class, 'medicines_items_id')
            ->join('transactions as t', 't.id', '=', 'tr_sales_lab_details.tr_sales_lab_id');
        return $data;
    }
    
    public function trSalesNetto()
    {
        $data = $this->hasMany(ModelTrSalesNettoDetails::class, 'medicines_items_id')
            ->join('transactions as t', 't.id', '=', 'tr_sales_netto_details.tr_sales_netto_id');
        return $data;
    }

    public function trSalesCredit()
    {
        $data = $this->hasMany(ModelTrSalesCreditDetails::class, 'medicines_items_id')
            ->join('transactions as t', 't.id', '=', 'tr_sales_credit_details.tr_sales_credit_id');
        return $data;
    }

    public function returnTrSales()
    {
        $data = $this->hasMany(ModelReturnTrSales::class, 'medicines_items_id')
            ->join('returns as r', 'r.id', '=', 'returns_tr_sales.returns_id');
        return $data;
    }

    public function returnTrPurchases()
    {
        $data = $this->hasMany(ModelTrPurchaseDetails::class, 'medicines_items_id')
            ->join('returns_tr_purchases as rtp', 'rtp.tr_purchase_details_id', '=', 'tr_purchase_details.id')
            ->join('returns as r', 'r.id', '=', 'rtp.returns_id');
        return $data;
    }

    public function stockAdjustmentTrSales()
    {
        $data = $this->hasMany(ModelStockAdjustmentsTrSales::class, 'medicines_items_id')
            ->join('stock_adjustments as sa', 'sa.id', '=', 'stock_adjustments_tr_sales.stock_adjustments_id');
        return $data;
    }
}
