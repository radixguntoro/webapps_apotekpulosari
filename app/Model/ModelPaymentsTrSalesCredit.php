<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelPaymentsTrSalesCredit extends Model
{
    protected $table = 'payments_tr_sales_credit';
    protected $primaryKey = 'payments_id';
    public $timestamps = false;
}
