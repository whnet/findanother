<?php
namespace app\admin\model;

use think\Model;
use think\Db;
class Designs extends Model
{
	
	protected static function init()
	{
		Designs::beforeInsert(function($Designs){
			$Designs->create_at = time();
		});
	} 
	
}