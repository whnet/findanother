<?php
namespace app\admin\model;

use think\Model;
use think\Db;
class Errlogs extends Model
{
	
	protected static function init()
	{
		Errlogs::beforeInsert(function($Errlogs){
			$Errlogs->create_at = time();
		});
	} 
	
}