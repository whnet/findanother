<?php
namespace app\admin\controller;
use think\Request;
use \think\Controller;
use app\admin\model\Sys_set;

class Sysset extends BaseController 
{
    public function index()
    {
		$id=input('id');
		$item=Sys_set::where('id',1)->find();
			
		$this->assign('item',$item);
		
		if(request()->ispost()){
			
			$db=new Sys_set();
	    	if($id){
				$data=[
					'sys_name'=>input('sys_name'),
					'sys_mobile'=>input('sys_mobile'),
					'sys_copyright'=>input('sys_copyright'),
					'sms_key'=>input('sms_key'),
					'sms_secret'=>input('sms_secret'),
					'appid'=>input('appid'),
					'emailhost'=>input('emailhost'),
					'emailuname'=>input('emailuname'),
					'emailpwd'=>input('emailpwd'),
					'appsecret'=>input('appsecret'),
				];
				
	    		$db->save($data,['id'=>$id]);
				$this->success('编辑成功','index');
	    	}else{
				$data=[
					'sys_name'=>input('sys_name'),
					'sys_mobile'=>input('sys_mobile'),
					'sys_copyright'=>input('sys_copyright'),
					'sms_key'=>input('sms_key'),
					'sms_secret'=>input('sms_secret'),
					'appid'=>input('appid'),
					'emailhost'=>input('emailhost'),
					'emailuname'=>input('emailuname'),
					'emailpwd'=>input('emailpwd'),
					'appsecret'=>input('appsecret'),
				];
	    		$db->save($data);
	    	}
			
	    	$this->success('添加成功','index');
   		}
		
		return $this->fetch();
    }
	
}