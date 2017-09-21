<?php
namespace app\admin\controller;
use think\Controller;
use think\Session;
use app\index\model;
use app\admin\model\Sys_user;

class Main extends BaseController
{
    public function index()
    {
        if(Session::has('usernamea'))
        {
			$this->assign('uname',Session::get('usernamea'));
            return $this->fetch('index');
        }
        else
        {
            $this->redirect('index/index');
        }

    }
}
