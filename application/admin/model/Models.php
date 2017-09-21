<?php
namespace app\admin\model;

use think\Model;

class Models extends Model
{
	protected static function init()
	{
		Models::beforeInsert(function($Models){
			$Models->create_at = time();
		});

		Models::beforeUpdate(function($Models){
			$Models->update_at = time();
		});
	} 

	 
}