<?php
namespace app\weixin\model;

use think\Model;
use think\Db;
class Know extends Model
{
	protected static function init()
	{
		Know::beforeInsert(function($Know){
			$Know->create_at = time();
		});
	}
}