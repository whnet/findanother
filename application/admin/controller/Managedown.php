<?php
namespace app\admin\controller;
use think\Controller;
use app\index\model;
use think\Session;
class Managedown extends Controller
{
    public function index()
    {
        if(Session::has('username'))
        {
            $user=new model\User();
            if($user->checkidentity(Session::get('username')))
            {
                $download=new model\Download();
                $result=$download->selectdownload();
                $this->assign('result',$result);
                $this->assign('empty','<span style="color: red">暂时没有数据</span>');
                return $this->fetch('managedown');
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
    public function deletedown($id)
    {
        $download=new model\Download();
        $result=$download->deletedown($id);
        $this->redirect('managedown/index');
    }
    public function updatedown()
    {
        $id=input('id');
        $version=input('version');
        $name=input('name');
        $url=input('url');
        $this->assign('version',$version);
        $this->assign('name',$name);
        $this->assign('url',$url);
        $this->assign('id',$id);
        return $this->fetch('update');
    }
    public function xiugai()
    {
        $data=[
            'version'=>input('version'),
            'name'=>input('downname'),
            'url'=>input('downurl')

        ];
        $id=input('id');
        $download=new model\Download();
        $result=$download->updatedown($data,$id);
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