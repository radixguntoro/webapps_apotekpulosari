<?php

namespace App\Http\Controllers\Backend\Master;

use App\Model\ModelPhones;

trait PhonesCtrl 
{
    protected $phones = [];
    protected $person_id = '';
    /*
    |--------------------------------------------------------------------------
    | Fungsi Insert or Update data
    |--------------------------------------------------------------------------
    */
    public static function createOrUpdate($phones, $person_id)
    {
        foreach ($phones as $val_phone) {
            if ($val_phone->status == 'create') {
                $m_phones_create = new ModelPhones();
                $m_phones_create->number = $val_phone->number;
                $m_phones_create->persons_id = $person_id;
                $m_phones_create->save();
            } else {
                /*
                |--------------------------------------------------------------------------
                | Update table phones
                |--------------------------------------------------------------------------
                */
                if($val_phone->id == 'null') {
                    $m_phones_create = new ModelPhones();
                    $m_phones_create->number = $val_phone->number;
                    $m_phones_create->persons_id = $person_id;
                    $m_phones_create->save();
                } else {
                    ModelPhones::where('id', $val_phone->id)->update([
                        'number' => $val_phone->number
                    ]);
                }
            }
        }
        return;
    }
}
