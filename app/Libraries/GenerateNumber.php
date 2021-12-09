<?php

namespace App\Libraries;

use DB;

class GenerateNumber
{
	public static function romawiNumber($month)
	{
		switch ($month) {
			case 1:
				$month = "I";
				break;
			case 2:
				$month = "II";
				break;
			case 3:
				$month = "III";
				break;
			case 4:
				$month = "IV";
				break;
			case 5:
				$month = "V";
				break;
			case 6:
				$month = "VI";
				break;
			case 7:
				$month = "VII";
				break;
			case 8:
				$month = "VIII";
				break;
			case 9:
				$month = "IX";
				break;
			case 10:
				$month = "X";
				break;
			case 11:
				$month = "XI";
				break;
			case 12:
				$month = "XII";
				break;
			default:
		}
		return $month;
	}

	public static function numberRomawi($month)
	{
		switch ($month) {
			case 'I':
				$month = 1;
				break;
			case 'II':
				$month = 2;
				break;
			case 'III':
				$month = 3;
				break;
			case 'IV':
				$month = 4;
				break;
			case 'V':
				$month = 5;
				break;
			case 'VI':
				$month = 6;
				break;
			case 'VII':
				$month = 7;
				break;
			case 'VII':
				$month = 8;
				break;
			case 'IX':
				$month = 9;
				break;
			case 'X':
				$month = 10;
				break;
			case 'XI':
				$month = 11;
				break;
			case 'XII':
				$month = 12;
				break;
			default:
		}
		return $month;
	}
	
	public static function digitCount($tableName, $tablePrimary, $initCode)
	{
		$nextInvoiceNumber = '';
		$month = self::romawiNumber(date('m'));
		$query = DB::table($tableName)->latest($tablePrimary)->get();
		$year = date("y");
		if (count($query) > 0) {
			if ($year > substr($query[0]->$tablePrimary, -2)) {
				$digit = 1;
				$nextInvoiceNumber = str_pad($digit, 5, '0', STR_PAD_LEFT) . '/' . $initCode . '/' . $month . '/' . $year;
			} else {
				$expNum = substr($query[0]->$tablePrimary, 0, 5);
				$digit = $expNum + 1;
				$nextInvoiceNumber = str_pad($digit, 5, '0', STR_PAD_LEFT) . '/' . $initCode . '/' . $month . '/' . $year;
			}
		} else {
			$digit = 1;
			$nextInvoiceNumber = str_pad($digit, 5, '0', STR_PAD_LEFT) . '/' . $initCode . '/' . $month . '/' . $year;
		}
		return $nextInvoiceNumber;
	}

	public static function digitCountDay($tableName, $tablePrimary, $key)
	{
		$nextInvoiceNumber = '';
		$date = date("d");
		$month = self::romawiNumber(date('m'));
		$year = date("Y");
		$query = DB::table($tableName)->latest($tablePrimary)->get();

		if (count($query) > 0) {
			$d_date = str_pad(substr($query[0]->invoice_number, 6, 2), 2, '0', STR_PAD_LEFT);
			$d_month = str_pad(self::numberRomawi(substr($query[0]->invoice_number, 9, -5)), 2, '0', STR_PAD_LEFT);
			$d_year = substr($query[0]->invoice_number, -4);
	
			if (count($query) > 0 && $d_year.'-'.$d_month.'-'.$d_date == date('Y-m-d')) {
				$expNum = substr($query[0]->invoice_number, 0, 5);
				$nextInvoiceNumber = str_pad($expNum+1, 5, '0', STR_PAD_LEFT) . '/' . $date . '/' . $month . '/' . $year;
			} else {
				if ($d_year.'-'.$d_month.'-'.$d_date == date('Y-m-d')) {
					$expNum = substr($query[0]->invoice_number, 0, 5);
					$nextInvoiceNumber = str_pad($expNum+1, 5, '0', STR_PAD_LEFT) . '/' . $date . '/' . $month . '/' . $year;
				} else {
					$nextInvoiceNumber = str_pad(1, 5, '0', STR_PAD_LEFT) . '/' . $date . '/' . $month . '/' . $year;
					// echo $nextInvoiceNumber;die;
				}
			}
		} else {
			$nextInvoiceNumber = str_pad(1, 5, '0', STR_PAD_LEFT) . '/' . $date . '/' . $month . '/' . $year;
		}

		return $nextInvoiceNumber;
	}
	/*
    |--------------------------------------------------------------------------
    | Generate Nomor Invoice
    |--------------------------------------------------------------------------
    */
	public static function generateInvoiceNumber($tableName, $tablePrimary)
	{
		$initCode = 'INV';
		$get_id = self::digitCountDay($tableName, $tablePrimary, $initCode);
		return $get_id;
	}
	/*
    |--------------------------------------------------------------------------
    | Generate Nomor Kwitansi
    |--------------------------------------------------------------------------
    */
	public static function generateReceiptNumber($tableName, $tablePrimary)
	{
		$initCode = 'KW';
		$get_id = self::digitCount($tableName, $tablePrimary, $initCode);
		return $get_id;
	}
	/*
    |--------------------------------------------------------------------------
    | Generate Nomor Order
    |--------------------------------------------------------------------------
    */
	public static function generateOrderNumber($tableName, $tablePrimary)
	{
		$initCode = 'DO';
		$get_id = self::digitCount($tableName, $tablePrimary, $initCode);
		return $get_id;
	}
	/*
    |--------------------------------------------------------------------------
    | Generate ID Reset per Tahun
    |--------------------------------------------------------------------------
    */
	public static function generatePrimaryCode($tableName, $tablePrimary, $key, $initCode)
	{
		$nextInvoiceNumber = '';
		$query = DB::table($tableName)->latest($tablePrimary)->get();
		$year = date("y");

		if (count($query) > 0) {
			if ($year > substr($query[0]->$tablePrimary, 3, -5)) {
				$nextInvoiceNumber = $initCode.$year.(str_pad(1, 5, '0', STR_PAD_LEFT));
			} else {
				$expNum = substr($query[0]->$tablePrimary, -5);
				$digit = $expNum+1;
				$nextInvoiceNumber = $initCode.$year.(str_pad($digit, 5, '0', STR_PAD_LEFT));
			}
		} else {
			$nextInvoiceNumber = $initCode.$year.(str_pad(1, 5, '0', STR_PAD_LEFT));
		}
		// echo $nextInvoiceNumber;die;
		return $nextInvoiceNumber;
	}
	/*
    |--------------------------------------------------------------------------
    | Generate ID Reset per Tahun dengan perbedaan code id
    |--------------------------------------------------------------------------
    */
	public static function generatePrimaryDiffCode($tableName, $tablePrimary, $key, $initCode)
	{
		$nextInvoiceNumber = '';
		$query = DB::table($tableName)->where('codes_id', $initCode)->latest($tablePrimary)->get();
		$year = date("y");
		
		if (count($query) > 0) {
			if ($year > substr($query[0]->$tablePrimary, 3, -5)) {
				$nextInvoiceNumber = $initCode.$year.(str_pad(1, 5, '0', STR_PAD_LEFT));
			} else {
				$expNum = substr($query[0]->$tablePrimary, -5);
				$digit = $expNum+1;
				$nextInvoiceNumber = $initCode.$year.(str_pad($digit, 5, '0', STR_PAD_LEFT));
			}
		} else {
			$nextInvoiceNumber = $initCode.$year.(str_pad(1, 5, '0', STR_PAD_LEFT));
		}
		// echo $nextInvoiceNumber;die;
		return $nextInvoiceNumber;
	}
	/*
    |--------------------------------------------------------------------------
    | Generate ID Reset per Hari
    |--------------------------------------------------------------------------
    */
	public static function generateDayCode($tableName, $tablePrimary, $key, $initCode)
	{
		$query = DB::table($tableName)->where('codes_id', $initCode)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();

		if ($query && substr($query->id, 3, -5) == date('ymd')) {
		    $expNum = substr($query->id, -5);
			if (date('l',strtotime(date('Y-01-01')))){
				$digit = $expNum+1;
				$nextInvoiceNumber = $initCode.date('ymd').(str_pad($digit, 5, '0', STR_PAD_LEFT));
			}
		} else {
			if (date('l',strtotime(date('Y-01-01')))) {
				$nextInvoiceNumber = $initCode.date('ymd').(str_pad($key, 5, '0', STR_PAD_LEFT));
			} else {
			    $expNum = substr($query->id, -5);
				$nextInvoiceNumber = $initCode.date('ymd').($expNum+1);
			}
		}
		return $nextInvoiceNumber;
	}
	/*
    |--------------------------------------------------------------------------
    | Generate ID Random by Timestamp
    |--------------------------------------------------------------------------
    */
	public static function generateTimeStampCode($tableName, $primary)
	{
		$query = DB::table($tableName)->select(DB::raw('MAX(RIGHT('.$primary.', 3)) as kode_max'));
		$date = date("ymdhis");

		if ($query->count()>0) {
			$kode = $date;
		} else {
			$kode = $date;
		}

		return $kode;
	}
}
