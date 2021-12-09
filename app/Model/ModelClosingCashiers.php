<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;

class ModelClosingCashiers extends Model
{
    protected $table = 'closing_cashiers';

    public function closingCashierDetils()
    {
        $data = $this->hasMany(ModelClosingCashierDetails::class, 'closing_cashiers_id')->with('transaction');
        return $data;
    }

    public static function queryIncomeApp()
    {
        $query = '(
            closing_cashiers.income_app - COALESCE(
                (
                    SELECT 
                        SUM((rts.price * rts.qty) - ((rts.price * rts.qty) * (rts.discount / 100))) as total_return 
                    FROM returns as r
                    JOIN returns_tr_sales as rts on rts.returns_id = r.id
                    WHERE DATE_FORMAT(r.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                    AND DATE_FORMAT(r.created_at, "%H:%i") < DATE_FORMAT(closing_cashiers.created_at, "%H:%i")
                ), 0)
        ) as income_app';
        return $query;
    }

    public static function queryIncomeDiff()
    {
        $query = 'closing_cashiers.income_real - (
            closing_cashiers.income_app - COALESCE(
                (
                    SELECT 
                        SUM((rts.price * rts.qty) - ((rts.price * rts.qty) * (rts.discount / 100))) as total_return 
                    FROM returns as r
                    JOIN returns_tr_sales as rts on rts.returns_id = r.id
                    WHERE DATE_FORMAT(r.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                    AND DATE_FORMAT(r.created_at, "%H:%i") < DATE_FORMAT(closing_cashiers.created_at, "%H:%i")
                    AND DATE_FORMAT(r.created_at, "%H:%i") > (
                        SELECT 
                                CASE
                                    WHEN closing_cashiers.shift = 1 THEN "00:01" 
                                    ELSE DATE_FORMAT(ccs_1.created_at, "%H:%i") 
                                    END
                                    as time_at
                            FROM closing_cashiers as ccs_1
                            WHERE DATE_FORMAT(ccs_1.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                            LIMIT 1
                    )
                ), 0)
        ) as income_diff';
        return $query;
    }

    public static function queryTotalReturn()
    {
        $query = 'COALESCE(
            (
                SELECT 
                    SUM((rts.price * rts.qty) - ((rts.price * rts.qty) * (rts.discount / 100))) as total_return 
                FROM returns as r
                JOIN returns_tr_sales as rts on rts.returns_id = r.id
                WHERE DATE_FORMAT(r.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                AND DATE_FORMAT(r.created_at, "%H:%i") < DATE_FORMAT(closing_cashiers.created_at, "%H:%i")
                AND DATE_FORMAT(r.created_at, "%H:%i") > (
                    SELECT 
                            CASE
                                WHEN closing_cashiers.shift = 1 THEN "00:01" 
                                ELSE DATE_FORMAT(ccs_1.created_at, "%H:%i") 
                                END
                                as time_at
                        FROM closing_cashiers as ccs_1
                        WHERE DATE_FORMAT(ccs_1.created_at, "%Y-%m-%d") = DATE_FORMAT(closing_cashiers.created_at, "%Y-%m-%d")
                        LIMIT 1
                )
            ), 0) 
        as total_return';
        return $query;
    }
}
