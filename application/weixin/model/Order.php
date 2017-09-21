<?php
namespace app\index\model;

use think\Model;
use think\Db;
class Order extends Model
{
    public function search()
    {
        $result=Db::name('order')->select();
        return $result;
    }
}