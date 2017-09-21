<?php
namespace app\admin\model;

use think\Model;

class Sys_set extends Model
{
	protected static function init()
	{
		Sys_set::beforeInsert(function($Sys_set){
			$Sys_set->create_at = time();
		});

	} 

	 
}