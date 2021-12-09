<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    public function readStatus()
    {
        return response()->json('connected');
    }
}
