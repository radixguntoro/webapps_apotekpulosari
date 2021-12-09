<?php

namespace App\Http\Controllers\Backend\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Libraries\GenerateImage;
use App\Model\ModelItems;
use App\Http\Controllers\Controller;
use DB;

abstract class itemsCtrl extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Variable Declaration
    |--------------------------------------------------------------------------
    */
    protected $id = '';
    protected $name = '';
    protected $status = '';
    protected $codes_id = '';

    abstract public function index(Request $request);
    /*
    |--------------------------------------------------------------------------
    | Insert table items
    |--------------------------------------------------------------------------
    */
    protected static function createData($item, $id, $get_init_code)
    {
        $m_item = new ModelItems();
        $m_item->id = $id;
        $m_item->name = Str::upper($item->name);
        $m_item->description = '-';
        $m_item->status = $item->status;
        $m_item->codes_id = $get_init_code;
        $m_item->categories_id = $item->categories_id;
        $m_item->image_cover = $item->image_cover;
        $m_item->users_persons_id = ($item->device == 'desktop') ? Auth::user()->persons_id : $item->users_persons_id;
        $m_item->save();
    }
    /*
    |--------------------------------------------------------------------------
    | Update table items
    |--------------------------------------------------------------------------
    */
    protected static function updateData($item)
    {
        $m_item = ModelItems::find($item->id);
        $m_item->name = Str::upper($item->name);
        $m_item->description = '-';
        $m_item->status = $item->status;
        $m_item->categories_id = $item->categories_id;
        $m_item->image_cover = $item->image_cover;
        $m_item->users_persons_id = ($item->device == 'desktop') ? Auth::user()->persons_id : $item->users_persons_id;
        $m_item->save();
    }
    /*
    |--------------------------------------------------------------------------
    | Delete table items
    |--------------------------------------------------------------------------
    */
    protected function deleteItems($item)
    {
        ModelItems::find($item->id)->delete();
    }
    /*
    |--------------------------------------------------------------------------
    | Fungsi set data active atau non active
    |--------------------------------------------------------------------------
    */
    protected static function updateDataStatus($item)
    {
        $m_item = ModelItems::find($item->id);
        $m_item->status = $item->status;
        $m_item->save();
    }
}
