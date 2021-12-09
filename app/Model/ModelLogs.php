<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelLogs extends Model
{
    use SoftDeletes;
    protected $table = 'logs';

    public function cashier()
    {
        $data = $this->belongsTo(ModelPersons::class, 'users_persons_id');
        return $data;
    }
}
