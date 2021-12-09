<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelTransactions extends Model
{
    use SoftDeletes;
    protected $table = 'transactions';

    public function cashier()
    {
        $data = $this->belongsTo(ModelPersons::class, 'users_persons_id')->withTrashed();
        return $data;
    }

    public function closingCashierDetails()
    {
        $data = $this->hasOne(ModelClosingCashierDetails::class, 'tr_sales_id');
        return $data;
    }
}
