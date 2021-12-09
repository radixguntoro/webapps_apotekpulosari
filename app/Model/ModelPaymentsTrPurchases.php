<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelPaymentsTrPurchases extends Model
{
    protected $table = 'payments_tr_purchases';
    protected $primaryKey = 'payments_id';
    public $timestamps = false;
}
