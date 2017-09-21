<?php
namespace app\admin\model;

use think\Model;
use think\Db;
class BackMessage extends Model
{
	
	protected static function init()
	{
		BackMessage::beforeInsert(function($BackMessage){
			$BackMessage->create_at = time();
		});
	} 
	
}