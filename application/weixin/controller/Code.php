<?php
namespace app\weixin\controller;
use think\Controller;
use think\Exception;
use think\Session;
use think\Cookie;
use app\index\model;
class Code extends BaseController
{
    public function index()
    {
		if(Session::has('uid')){
			$jihuoma="";
			//$this->assign('jihuoma',$jihuoma);
			$this->assign('code1','');
			$this->assign('code2','');
			return $this->fetch("code");
		}else{
			$this->error('请先登录绑定账号！','login/index');
		}
        
    }
    public function getcode()
    {
		if(Session::has('uid')){
			$username=\think\Session::get('username');
			$jihuoma=md5($username);
			$code1=substr($jihuoma,0,-16);
			$code2=substr($jihuoma,16);
			//$this->assign('jihuoma',$jihuoma);
			$this->assign('code1',$code1);
			$this->assign('code2',$code2);
			return $this->fetch("code");
		}else{
			$this->error('请先登录绑定账号！','login/index');
		}
    }

}
