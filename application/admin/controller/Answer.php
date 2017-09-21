<?php
namespace app\admin\controller;
use think\Controller;
use app\index\model;
use think\Session;
use think\Request;
use app\index\model\Message;
use app\admin\model\BackMessage;
use app\index\model\User;

class Answer extends BaseController
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
        $db=Message::alias('a')
        ->field('a.*,b.username as name')
        ->join('user b','b.id=a.uid','left')
        ->where('a.message','like',$keyword.'%');
        if(!empty($start_at) && !empty($end_at)){
        $db->whereTime('a.create_at', 'between', [$start_at, $end_at]);
        }
        $db->order('a.id desc');
        $list=$db->paginate('10',false,$pageParam);
        $page=$list->render();
        $this->assign('page',$page);
        $this->assign('list',$list);

        return $this->fetch();
    }
    public function answer(Request $request)
    {
        $uid=$request->param('uid');//用户id
		$id=$request->param('id'); //留言id
		$data = User::where('id',$uid)->find();

		$this->assign('id',$id);
		$this->assign('name',$data['username']);
        return $this->fetch('answer');
    }
    public function back()
    {
		$id = input('id');
        $data=[
            'mid'=>$id,  //留言id
            'back_message'=>input('content'),
        ];
        $message=new BackMessage();
        $db=$message->save($data);
        if($db)
        {
			Message::where('id',$id)->update(['isadmin'=>1]);
			$this->success('回复成功', 'index');
        }
        else
        {
			$this->error('回复失败', 'index');
        }
    }
    public function del(Request $request)
    {
		
        $id = $request->param('id');
        $db = Message::where('id',$id)->delete(); //删除消息
		BackMessage::where('mid',$id)->delete();  //删除消息回复
       // $id=input('id');

        if($db)
        {
            $this->success('删除成功', 'index');
        }
        else
        {
            $this->error('删除失败', 'index');
        }
    }
}
