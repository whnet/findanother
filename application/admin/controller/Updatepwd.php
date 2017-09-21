<?php
namespace app\admin\controller;
use think\Controller;
use app\index\model;
use think\Session;
class Updatepwd extends Controller
{
    public function index()
    {
        if(Session::has('username'))
        {
            $user=new model\User();
            if($user->checkidentity(Session::get('username')))
            {
                return $this->fetch('updatepwd');
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
    public function changepwd()
    {
        $pwd=input('title');
        $result=\think\Db::name('user')->update(['password'=>md5($pwd)]);
        if($result)
        {
            echo "<script>alert('修改成功');</script>";
            $this->redirect('mainpage/index');
        }
        else
        {
            echo "<script>alert('修改失败');</script>";
            $this->redirect('mainpage/index');
        }
    }
}