<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelPaymentsTrSalesNetto extends Model
{
    protected $table = 'payments_tr_sales_netto';
    protected $primaryKey = 'payments_id';
    public $timestamps = false;
}
