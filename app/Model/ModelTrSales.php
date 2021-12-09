<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSales extends Model
{
    protected $table = 'tr_sales';
    protected $primaryKey = 'transactions_id';
    public $timestamps = false;

    public function transaction()
    {
        $data = $this->belongsTo(ModelTransactions::class, 'transactions_id')->with('cashier');
        return $data;
    }

    public function trSalesRegular()
    {
        $data = $this->hasMany(ModelTrSalesRegular::class, 'tr_sales_transactions_id')->with('trSalesRegularDetails');
        return $data;
    }

    public function trSalesLab()
    {
        $data = $this->hasMany(ModelTrSalesLab::class, 'tr_sales_transactions_id')->with('trSalesLabDetails');
        return $data;
    }
}
