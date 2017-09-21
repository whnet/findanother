<?php
namespace app\admin\controller;
use think\Request;
use \think\Controller;
use app\admin\model\Article;

class Help extends BaseController  
{
    public function index()
    {
    	$keyword=input('get.keyword');
    	$this->assign('keyword',$keyword);
    	$pageParam['query']['keyword']=$keyword;
    	$list=Article::order('id desc')
    	->paginate('10',false,$pageParam);
    	$this->assign('list',$list);
    	$page=$list->render();
    	$this->assign('page',$page);
    	return $this->fetch();
    }
    //用户edit,add
    public function article_add($id='')
    {
		if(request()->ispost()){
			$db=new Article();
			$id=input('id');
	    	if($id){
	    		$db->save($_POST,['id'=>$id]);
	    	}else{
	    		$db->save($_POST);
	    	}
	    	$this->redirect('index');
   		}else{
			
			$item=Article::where('id',$id)->find();
			$this->assign('item',$item);
			return $this->fetch();
		}
    	
    }
}