<?php
namespace app\weixin\model;

use think\Model;
use think\Db;
class Record extends Model
{
     public function check($username)
     {
          $result=Db::name('record')->where('username',$username)->find();
          return $result;
     }
     public function updaterecord($username,$json,$jifen)//更新记录，积分
     {
          $result=Db::name('record')->where('username',$username)->update(['json'=>$json]);
          Db::name('record')->where('username',$username)->setInc('jifen',$jifen);
          return $result;
     }
     public function insertrecord($username,$json,$jifen)//插入一条记录
     {
          $data=[
              'username'=>$username,
              'json'=>$json,
              'jifen'=>$jifen
          ];
          $result=Db::name('record')->insert($data);
          return $result;
     }

     //按积分取前十
     public function selectbyjifen()
     {
          $result=Db::name('record')->order('jifen desc')->limit(10)->paginate(5);
          return $result;
     }
}