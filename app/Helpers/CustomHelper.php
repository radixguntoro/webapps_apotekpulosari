<?php

function rupiah($number) {
	$rupiah = number_format($number);
	return $rupiah;
}

function mediumDate($date) {
	$medium_date = date('d M Y H:i:s', strtotime($date));
	return $medium_date;
}

function shortDate($date) {
	$long_date = date('d-m-Y H:i:s', strtotime($date));
	return $long_date;
}

function onlyDate($date) {
	$long_date = date('d M Y', strtotime($date));
	return $long_date;
}

function phpDate($date) {
	$php_date = date('Y-m-d', strtotime($date));
	return $php_date;
}

function cetak_r($params) {
	echo "<pre>";
	$cetak = print_r($params);
	return $cetak;
}
