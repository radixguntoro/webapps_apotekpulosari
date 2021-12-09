<?php

namespace App\Libraries;

use DB;

class TelegramBot
{
	public static function sendError($log)
    {
        $apiToken = '1875099057:AAFX5xvxXuvHLeqtGFFk009NGdJPBIUSx50';
        $msg = $log;
        $data = [
            'chat_id' => '1045295036',
            'text' => $msg
        ];

        $response = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($data) );
    }
}