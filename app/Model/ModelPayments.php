<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelPayments extends Model
{
    use SoftDeletes;
    protected $table = 'payments';

    public function payment_tr_purchase()
    {
        $data = $this->belongsTo(ModelPaymentsTrPurchases::class, 'id');
        return $data;
    }
}
