<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\index\model;
use think\Db;
use think\Exception;
use think\Session;
use app\index\model\User;

class Integral extends Controller
{
    public function index()
    {
		
		//$file = input('');
		//$data = file($file);
		
		//echo $_FILES["file"]["tmp_name"];
		
/* 		move_uploaded_file($_FILES["file"]["tmp_name"], 'uploads/'. $_FILES["file"]["name"]);*/
		
		$streamData = file_get_contents($_FILES["file"]["tmp_name"]);
		
		if(@$fp = fopen($_FILES["file"]["name"], 'w+')){
			fwrite($fp, $streamData);
			fclose($fp);
		}
		
/* 		$streamData = isset($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : '';  
		if(empty($streamData)){
			$streamData = file_get_contents('php://input');
			//$streamData = iconv('windows-1250', 'utf-8', $streamData); 
		}
		
		if($streamData!=''){
			file_put_contents("errorlog.txt", $streamData, true);   */
			/* if(@$fp = fopen("errorlog.txt", 'w+')){
				fwrite($fp, $streamData);
				fclose($fp);
			} */
		//}
		 
    }
	
}