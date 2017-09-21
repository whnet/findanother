<?php
namespace app\admin\controller;
use think\Controller;
use think\Session;
use app\index\model;

class Error extends Controller
{
    public function index()
    {
        echo "<h1 align='center'>404,出错了,请<a href=\"{:url('index/login/index')}\">重新登录</a></h1>";
        echo "<p align='center'><a href=\"{:url('index/index/index')}\">返回主页</a></p>";
        //return $this->fetch('error');
    }

}