<?php
namespace app\admin\controller;
use think\Controller;
use app\index\model;
use think\Session;
use app\admin\model\Record;

class Seejifen extends BaseController
{
    public function index()
    {
       	$keyword=input('get.keyword');
        $this->assign('keyword',$keyword);

        $pageParam['query']['keyword']=$keyword;

        $db=Record::alias('a')
        ->field('a.jifen,a.id as aid,b.*')
        ->join('user b','b.username = a.username','left')
        ->where('a.username','like',$keyword.'%');

        $db->order('a.id desc');
        $list=$db->paginate('10',false,$pageParam);
		
        $page=$list->render();
        $this->assign('page',$page);
        $this->assign('list',$list);
		
		return $this->fetch();
    }
	
	public function del(){
		$id = input($id);
		$db = Record::where('id',$id)->delete(); //删除消息
		if($db){
			$this->success('删除成功','index');
		}else{
			$this->error('删除失败','index');
		}
	}

}
