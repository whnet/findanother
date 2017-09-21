<?php
namespace app\index\model;

use think\Model;
use think\Db;
class Download extends Model
{
    public function lastdown()//查询最新发布版本
    {
        $result=Db::name('download')->order('time desc')->limit(1)->find();
        return $result;
    }

    public function selectdownload()
    {
        $result=Db::name('download')->paginate(10);
        return $result;
    }
    public function insertdownload($data)
    {
        $result=Db::name('download')->insert($data);
        return $result;
    }
}