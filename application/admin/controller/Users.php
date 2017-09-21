<?php
namespace app\admin\controller;
use think\Controller;
use think\Session;
use app\admin\model;
use app\admin\model\User;

class Users extends BaseController 
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
		
		
		$db=user::alias('a')
            ->field('a.*,a.ID as suid,c.*,b.*')
            ->join('weixin b','b.id=a.wid')
			->join('mfind c','c.uid=a.ID')
			->order('a.ID desc')
			->where('b.nickname','like',$keyword.'%');
		
		if(!empty($start_at) && !empty($end_at)){
        $db->whereTime('a.Regtime', 'between', [$start_at, $end_at]); 
        }
		
		$list=$db->paginate('10',false,$pageParam);
		
		foreach($list as $k => $v){
			
			$uid = $v['tuijianid'];
			$dbdata=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$uid)
				->find();
				
			$list[$k]['tuijianname'] = $dbdata['nickname'];
			
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
			
            $db=new User();
            $data=[
                'isVip'=>input('isVip'),
                'isShow'=>input('isShow'),
                'isSign'=>input('isSign'),
            ];
            $db->save($data,['id' => $id]);
			
            $this->success('编辑成功！', 'index');
        }else{
			$uid = input('id');
			$db=user::alias('a')
            ->field('a.*,a.ID as suid,b.*')
            ->join('weixin b','b.id=a.wid')
			->where('a.ID',$uid)
			->find();
			
			$uuid = $db['tuijianid'];
			$data=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$uuid)
				->find();
				
			$db['tuijianname'] = $data['nickname'];
			$this->assign('item',$db);
			return $this->fetch();
		}
    }
}
