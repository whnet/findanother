<?php
namespace app\admin\controller;
use think\Controller;
use think\Session;
use app\admin\model;
use app\admin\model\User;
use app\admin\model\Tousu;

class Tousus extends BaseController 
{
    public function index()
    {
        $keyword=input('get.keyword');
        $this->assign('keyword',$keyword);
        $start_at= input('get.start_at');
        $end_at= input('get.end_at');
        $this->assign('start_at',$start_at);
        $this->assign('end_at',$end_at);
        $pageParam['query']['keyword']=$keyword;
        $pageParam['query']['start_at'] = $start_at;
        $pageParam['query']['end_at'] = $end_at;
		
		
		$db=tousu::order('id desc')
			->where('title','like',$keyword.'%');
		
		if(!empty($start_at) && !empty($end_at)){
        $db->whereTime('create_at', 'between', [$start_at, $end_at]); 
        }
		
		$list=$db->paginate('10',false,$pageParam);
		
		foreach($list as $key =>$val){
			$tid = $val['tuid'];
			$btid = $val['btuid'];
			
			$tdata=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$tid)
				->find();
				
			$list[$key]['tname'] = $tdata['nickname'];

			$btdata=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$btid)
				->find();
				
			$list[$key]['btname'] = $btdata['nickname'];			
			
		}

        $page=$list->render();
        $this->assign('page',$page);

        $this->assign('result',$list);
        return $this->fetch();

    }
	
	
    public function edit()
    {
		
		if(request()->ispost()){
			$id = input('id');
			
            $db=new tousu();
            $data=[
                'flag'=>1,
            ];
            $db->save($data,['id' => $id]);
			
            $this->success('处理成功！', 'index');
        }else{
			$uid = input('id');
			$db=tousu::order('id desc')
			->where('id',$uid)
			->find();
			
			$tid = $db['tuid'];
			$btid = $db['btuid'];
			
			$tdata=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$tid)
				->find();
				
			$db['tname'] = $tdata['nickname'];

			$btdata=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$btid)
				->find();
				
			$db['btname'] = $btdata['nickname'];
			$this->assign('item',$db);
			return $this->fetch();
		}
    }
}
