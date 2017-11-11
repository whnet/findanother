<?php
namespace app\weixin\model;

use think\Model;

class Beixuan extends Model
{
    protected static function init()
    {
        Beixuan::beforeInsert(function($Beixuan){
            $Beixuan->create_at = time();
        });
    }
}