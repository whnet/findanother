<?php
namespace app\weixin\model;

use think\Model;

class Sys_set extends Model
{
	protected static function init()
	{
		Sys_set::beforeInsert(function($Sys_set){
			$Sys_set->create_at = time();
		});

		Sys_set::beforeUpdate(function($Sys_set){
			$Sys_set->update_at = time();
		});
	} 

	 
}