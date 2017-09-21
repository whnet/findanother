<?php
namespace app\admin\controller;
use think\Controller;
use think\Session;

class Exitlogin extends Controller
{
    public function index()
    {
        Session::clear();
		$this->success('退出系统成功', 'index/index');
    }
}