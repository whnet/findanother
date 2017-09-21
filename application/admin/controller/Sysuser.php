<?php
namespace app\admin\controller;
use think\Request;
use \think\Controller;
use app\admin\model\Sys_user;

class Sysuser extends BaseController  
{
    public function index()
    {
    	$keyword=input('get.keyword');
    	$this->assign('keyword',$keyword);
    	$pageParam['query']['keyword']=$keyword;
    	$list=Sys_user::where('status','neq',2 )
		->where('uname|mobile','like','%'.$keyword.'%')
    	->order('id desc')
    	->paginate('10',false,$pageParam);
    	$this->assign('list',$list);
    	$page=$list->render();
    	$this->assign('page',$page);
    	return $this->fetch();
    }
    //用户edit,add
    public function sys_user_add($id='')
    {
		
		$id=input('id');
		$item=Sys_user::where('id',$id)->find();
			
		$this->assign('item',$item);
		$password=$item['upwd'];
		
		if(request()->ispost()){
			
			$db=new Sys_user();
	    	if($id){
				if($password != input('upwd')){
					$upwd=MD5(input('upwd'));
				}else{
					$upwd = $password;
				}
				
				$data=[
					'uname'=>input('uname'),
					'upwd'=>$upwd,
					'mobile'=>input('mobile'),
					'name'=>input('name'),
					'status'=>0,
				];
				
	    		$db->save($data,['id'=>$id]);
				$this->success('编辑成功','index');
	    	}else{
				$data=[
					'uname'=>input('uname'),
					'upwd'=>MD5(input('upwd')),
					'mobile'=>input('mobile'),
					'name'=>input('name'),
					'status'=>0,
				];
	    		$db->save($data);
	    	}
			
	    	$this->success('添加成功','index');
   		}
		
		return $this->fetch();
    }

 //用户删除
    public function sys_user_del($id='')
    {
    	 $db=Sys_user::get($id);
    	 $db->status=2;
    	 $db->save();
     	 $this->redirect('index');
    }
	
    //验证用户账号是否存在
    public function validateuser($uname='')
    {
    	$item=Sys_user::where('uname',$uname)->find();
    	if($item){
    		echo "登录账号已存在，请重新填写";
   		}else{
    		echo " ";
    	}
 
    }
}