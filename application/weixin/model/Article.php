<?php
namespace app\weixin\model;

use think\Model;

class Article extends Model
{
	protected static function init()
	{
		Article::beforeInsert(function($Article){
			$Article->create_at = time();
		});

		Article::beforeUpdate(function($Article){
			$Article->update_at = time();
		});
	} 

	 
}