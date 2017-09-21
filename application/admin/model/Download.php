<?php
namespace app\admin\model;

use think\Model;

class Download extends Model
{
	protected static function init()
	{
		Download::beforeInsert(function($Download){
			$Download->create_at = time();
		});

		Download::beforeUpdate(function($Download){
			$Download->update_at = time();
		});
	} 

	 
}