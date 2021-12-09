<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelLogTransactions extends Model
{
    protected $table = 'log_transactions';
    protected $primaryKey = 'logs_id';
    public $timestamps = false;
}
