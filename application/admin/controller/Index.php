<?php
namespace app\admin\controller;
use think\Request;
use \think\Controller;
use app\admin\model\Sys_user;
use \think\Session;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function login()
    {
        $username=input('username');
        $pwd=md5(input('password'));
		
		$user=Sys_user::where([
    		'uname' => $username,
    		'upwd' => $pwd,
    		'status' => 0,
    		])
    	->find();
		
		//echo Sys_user::getLastSql();
		
    	if($user){
    		Session::set('login_user_id',$user['id']);
    		Session::set('usernamea',$user['uname']);
    		echo "登录成功";

     	}else{
    		echo "登录失败，请重新登录";
    	}

    }
		 //退出登录
	 public function logout(){
		Session::set('login_user_id',"");
    	Session::set('usernamea',"");
		$this->success('退出系统成功', 'index');
	 }
}
