<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelTrSalesRecipeMedicines extends Model
{
    protected $table = 'tr_sales_recipe_medicines';
    public $timestamps = false;
    /*
    |--------------------------------------------------------------------------
    | Read data by id
    |--------------------------------------------------------------------------
    */
    public static function readDataById($id)
    {
        $data = DB::table('tr_sales_recipe_medicines')
            ->select(
                'tr_sales_recipe_medicines.id as id',
                'tr_sales_recipe_medicines.name as medicineName',
                'tr_sales_recipe_medicines.price as price',
                'tr_sales_recipe_medicines.tuslah as tuslah',
                'tr_sales_recipe_medicines.subtotal as subtotal'
            )
            ->join('tr_sales_recipe', 'tr_sales_recipe.tr_sales_transactions_id', '=', 'tr_sales_recipe_medicines.tr_sales_recipe_id')
            ->join('transactions', 'transactions.id', '=', 'tr_sales_recipe_medicines.tr_sales_recipe_id')
            ->where('tr_sales_recipe_medicines.tr_sales_recipe_id', $id)
            ->whereNull('transactions.deleted_at')
            ->get();

        return $data;
    }

    public function trSalesRecipeDetails()
    {
        $data = $this->hasMany(ModelTrSalesRecipeDetails::class, 'tr_sales_recipe_medicines_id')->with('items');
        return $data;
    }
}
