<?php
namespace app\admin\model;

use think\Model;

class Sys_user extends Model
{
	protected static function init()
	{
		Sys_user::beforeInsert(function($Sys_user){
			$Sys_user->create_at = time();
		});

		Sys_user::beforeUpdate(function($Sys_user){
			$Sys_user->update_at = time();
		});
	} 

	 
}