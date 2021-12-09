<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelReturnTrSalesNetto extends Model
{
    protected $table = 'returns_tr_sales_netto';
    protected $primaryKey = 'returns_id';
    public $timestamps = false;

    public function return()
    {
        $data = $this->belongsTo(ModelReturns::class, 'returns_id')->with('cashier');
        return $data;
    }

    public function trSalesNetto()
    {
        $data = $this->belongsTo(ModelTrSalesNetto::class, 'tr_sales_transactions_id')->with('trSales');
        return $data;
    }

    public function trSalesNettoDetails()
    {
        $data = $this->belongsTo(ModelTrSalesNettoDetails::class, 'tr_sales_details_id')->with('medicine');
        return $data;
    }
}