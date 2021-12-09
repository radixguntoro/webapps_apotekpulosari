<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelClosingCashierDetails extends Model
{
    protected $table = 'closing_cashier_details';
    public $timestamps = false;

    public function transaction()
    {
        $data = $this->belongsTo(ModelTransactions::class, 'tr_sales_id')->with('cashier');
        return $data;
    }
}
