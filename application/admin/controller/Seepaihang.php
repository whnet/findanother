<?php
namespace app\admin\controller;
use think\Controller;
use app\index\model;
use think\Session;
class Seepaihang extends Controller
{
    public function index()
    {
        if(Session::has('username'))
        {
            $user=new model\User();
            if($user->checkidentity(Session::get('username')))
            {
                $result=\think\Db::view('record','username,jifen')->
                view('user','id,name,company,job,time','record.username=user.username')->
                where('state',1)->order('jifen desc')->paginate(10);
                $this->assign('result',$result);
                $this->assign('count',1);
                $this->assign('empty','<span style="color: red">暂时没有数据</span>');
                return $this->fetch("seepaihang");
            }
            else
            {
                echo "<script>alert('非法进入!');window.location.href='error';</script>";
            }
        }
        else
        {
            echo "<script>alert('非法进入!');window.location.href='error';</script>";
        }

    }
}