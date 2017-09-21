<?php
namespace app\weixin\controller;
use think\Controller;
use think\Session;
use app\index\model;
use think\Cookie;
use app\weixin\model\Record;

class Paihang extends BaseController
{
    
	public function index(){
		if(Session::has('uid')){
			//周排行
			$list=Record::field("username,sum(jifen) as jifen")
			->where('WEEK(FROM_UNIXTIME(add_at,"%Y-%m-%d"))','WEEK(NOW())')
			->group('username')
			->order('sum(jifen) desc')
			->limit('0,10')
			->select();
			//$list=$dbweek->paginate('10');
			//$page=$list->render();
			$this->assign('num',1);
			$this->assign('list',$list);

			//月排行
			$data=Record::field("username,sum(jifen) as jifen")
			->where('MONTH(FROM_UNIXTIME(add_at,"%Y-%m-%d"))','MONTH(NOW())')
			->group('username')
			->order('sum(jifen) desc')
			->limit('0,10')
			->select();
			
			//$data=$dbmonth->paginate('10');
			//$page=$data->render();
			//$this->assign('page',$page);
			$this->assign('data',$data);
			return $this->fetch();
		}else{
			$this->error('请先登录绑定账号！','login/index');
		}
	}
	
	public function ajaxweek(){
		$number = input('num');
		$data = array();
		if($number){
			$pagenum = ($number+1);
			$num = $pagenum*10;
			//周排行
			$wlist=Record::field("username,sum(jifen) as jifen")
			->where('WEEK(FROM_UNIXTIME(add_at,"%Y-%m-%d"))','WEEK(NOW())')
			->group('username')
			->order('sum(jifen) desc')
			->limit('0,'.$num)
			->select();
			
			$data['weeks']=$wlist;
			$data['num']=$pagenum;
	
			echo json_encode($data); 
		}else{
			echo '-1';
		}
		
	}

	public function ajaxmonth(){
		$number = input('num');
		$data = array();
		if($number){
			$pagenum = ($number+1);
			$num = $pagenum*10;

			//月排行
			$mlist=Record::field("username,sum(jifen) as jifen")
			->where('MONTH(FROM_UNIXTIME(add_at,"%Y-%m-%d"))','MONTH(NOW())')
			->group('username')
			->order('sum(jifen) desc')
			->limit('0,'.$num)
			->select();
			
			$data['months']=$mlist;
			$data['num']=$pagenum;
			
			echo json_encode($data); 
		}else{
			echo '-1';
		}
		
	}

    public function go()
    {
        $this->redirect('index/index');
    }
}
