<?php
namespace app\weixin\model;

use think\Model;

class Alternative extends Model
{
	protected static function init()
	{
		Alternative::beforeInsert(function($Alternative){
			$Alternative->create_at = time();
		});
	}
}