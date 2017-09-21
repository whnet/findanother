<?php
namespace app\weixin\model;

use think\Model;

class Hulue extends Model
{
	protected static function init()
	{
		Hulue::beforeInsert(function($Hulue){
			$Hulue->create_at = time();
		});
	}
}