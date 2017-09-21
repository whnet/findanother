<?php
namespace app\admin\model;

use think\Model;

class Patch extends Model
{
	protected static function init()
	{
		Patch::beforeInsert(function($Patch){
			$Patch->create_at = time();
		});

		Patch::beforeUpdate(function($Patch){
			$Patch->update_at = time();
		});
	} 

	 
}