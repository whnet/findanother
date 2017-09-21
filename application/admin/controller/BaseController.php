<?php
namespace app\admin\controller;
use think\Request;
use \think\Controller;
use app\index\model\user;
use \think\Session;

class BaseController extends Controller 
{
    public function _initialize()
    {
		if(!Session::has('usernamea'))
        {
			$this->error('请先登录！', 'index/index');
        }
    }
}
