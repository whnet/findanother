<?php
namespace app\admin\controller;
use think\Controller;
use app\index\model;
use think\Session;
class Manageorder extends Controller
{
    public function index()
    {
        if(Session::has('username'))
        {
            $user=new model\User();
            if($user->checkidentity(Session::get('username')))
            {
                $order=new model\Order();
                $result=$order->getall();
                $this->assign('result',$result);
                $this->assign('empty','<span style="color: red">暂时没有数据</span>');

                return $this->fetch('manageorder');
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
    public function deleteorder($id)
    {
        $order=new model\Order();
        $result=$order->deleteorder($id);
        $this->redirect('manageorder/index');
    }
    public function updateorder()
    {
        $id=input('id');
        $commandname=input('commandname');
        $weight=input('weight');
        $this->assign('id',$id);
        $this->assign('commandname',$commandname);
        $this->assign('weight',$weight);
        return $this->fetch('updateorder');
    }
    public function xiugai()
    {
        $data=[
            'commandname'=>input('commandname'),
            'weight'=>input('weight')
        ];

        $id=input('id');

        $order=new model\Order();
        $result=$order->updateorder($data,$id);
        if($result)
        {
            echo "<script>alert('修改成功！');window.location.href='index';</script>";
        }
        else
        {
            echo "<script>alert('修改失败！');window.location.href='index';</script>";
        }
    }
}
