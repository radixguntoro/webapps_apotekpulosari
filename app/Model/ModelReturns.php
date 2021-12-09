<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelReturns extends Model
{
    use SoftDeletes;
    protected $table = 'returns';

    public function returnSales()
    {
        $data = $this->hasOne(ModelReturnTrSales::class, 'returns_id')->with('medicine');
        return $data;
    }

    public function returnPurchase()
    {
        $data = $this->hasOne(ModelReturnTrPurchases::class, 'id');
        return $data;
    }

    public function cashier()
    {
        $data = $this->belongsTo(ModelPersons::class, 'users_persons_id');
        return $data;
    }
}
