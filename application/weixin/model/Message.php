<?php
namespace app\weixin\model;

use think\Model;
use think\Db;
class Message extends Model
{
	
	protected static function init()
	{
		Message::beforeInsert(function($Message){
			$Message->create_at = time();
		});
	} 
	
}