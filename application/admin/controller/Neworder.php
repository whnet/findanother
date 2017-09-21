<?php
namespace app\admin\controller;
use think\Controller;
use app\index\model;
use think\Session;
class Neworder extends BaseController
{
    public function index()
    {
        
        return $this->fetch('neworder');
          
    }
    public function addorder()
    {
        $data=[
            'commandname'=>input('order'),
            'weight'=>input('weight')
        ];
        $order=new model\Order();
        $result=$order->add($data);
        if($result)
        {
            echo "<script>alert('添加成功！');window.location.href='index'</script>";
        }
        else
        {
            echo "<script>alert('添加失败！');window.location.href='index'</script>";
        }
    }

}