<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ModelPhones extends Model
{
    protected $table = 'phones';
    protected $primaryKey = 'persons_id';
    public $timestamps = false;
}
