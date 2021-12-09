<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesNettoDetails extends Model
{
    protected $table = 'tr_sales_netto_details';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public function medicine()
    {
        $data = $this->belongsTo(ModelMedicines::class, 'medicines_items_id')->with('item');
        return $data;
    }
    public function returnSum()
    {
        $data = $this->hasMany(ModelReturnTrSalesNetto::class, 'tr_sales_details_id');
        return $data;
    }
    public function returnDetail()
    {
        $data = $this->hasMany(ModelReturnTrSalesNetto::class, 'tr_sales_details_id')->with('return');
        return $data;
    }
    public function adjustmentsSum()
    {
        $data = $this->hasMany(ModelStockAdjustmentsTrSalesNetto::class, 'tr_sales_details_id');
        return $data;
    }

    public function adjustmentsDetail()
    {
        $data = $this->hasMany(ModelStockAdjustmentsTrSalesNetto::class, 'tr_sales_details_id')->with('stockAdjustments');
        return $data;
    }
}
