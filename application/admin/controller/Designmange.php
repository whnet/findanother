<?php
namespace app\admin\controller;
use think\Controller;
use app\index\model;
use think\Session;
use app\admin\model\Designs;

class Designmange extends BaseController
{
    public function index()
    {
       	$keyword=input('get.keyword');
        $this->assign('keyword',$keyword);

        $pageParam['query']['keyword']=$keyword;

        $db=Designs::where('username','like','%'.$keyword.'%');

        $db->order('id desc');
        $list=$db->paginate('10',false,$pageParam);
		
        $page=$list->render();
        $this->assign('page',$page);
        $this->assign('list',$list);
		
		return $this->fetch();
    }
	
	public function show_img(){
		$id = input('id');
		$img = Designs::field('imgurl')->where("id",$id)->find();
		$data = explode(",",$img['imgurl']);
		$this->assign('list',$data);
		return $this->fetch();
	}
	
	public function del(){
		$id = input('id');
		$db = Designs::where('id',$id)->delete(); //删除设计数据
		if($db){
			$this->success('删除成功','index');
		}else{
			$this->error('删除失败','index');
		}
	}

}
