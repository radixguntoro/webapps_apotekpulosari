<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelPersons extends Model
{
    use SoftDeletes;
    protected $table = 'persons';

    public function user()
    {
        $data = $this->hasOne(ModelUsers::class, 'persons_id');
        return $data;
    }

    public function phones()
    {
        $data = $this->hasOne(ModelPhones::class, 'persons_id');
        return $data;
    }
}
