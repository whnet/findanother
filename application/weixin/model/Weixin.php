<?php
namespace app\weixin\model;

use think\Model;
use think\Db;
class Weixin extends Model
{
    protected static function init()
	{
		Weixin::beforeInsert(function($Weixin){
			$Weixin->create_at = time();
		});
	}
	
	public static function check_login($openid)
    {
        $result=Db::name('weixin')->where('openid',$openid)->find();
        return $result;
    }
}
