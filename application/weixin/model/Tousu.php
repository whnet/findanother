<?php
namespace app\weixin\model;

use think\Model;

class Tousu extends Model
{
	protected static function init()
	{
		Tousu::beforeInsert(function($Tousu){
			$Tousu->create_at = time();
		});
	}
}