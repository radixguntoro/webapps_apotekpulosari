<?php

namespace App\Libraries;

use DB;

class RespMessages
{
	public static function successCreate()
    {
        return "Data berhasil disimpan.";
    }
	public static function failErrorSystem()
    {
        return "Silakan hubungi Developer.";
    }
	public static function failEmptyCart()
    {
        return "Keranjang masih kosong.";
    }
	public static function failRequest()
    {
        return "Silakan coba lagi. Jaringan tidak stabil";
    }
}
