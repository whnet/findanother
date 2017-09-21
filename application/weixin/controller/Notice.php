<?php
namespace app\weixin\controller;
use think\Request;
use \think\Controller;
use app\weixin\model\Alternative; 
use app\weixin\model\Know;
use app\weixin\model\Mfind; 
use app\weixin\model\Weixin; 
use app\weixin\model\User;
use app\weixin\model\Friends;
use \think\Session;
use \think\Cookie;
use app\weixin\model\Message as info;

class Notice extends Controller 
{
	
    public function ajaxorder() {
		$notice_array = array();
        $k = 0;
		//提示 
		$openid = Cookie::get('openid');
		$data = weixin::where('openid',$openid)->find();
		$wid = $data['id'];
		$uval = user::where('wid',$wid)->find();
		$uid = $uval['ID'];
		
		
		$notice_log = info::where('flag',0)
		->where('toid',$uid)
    	->select();
		
		if(!empty($notice_log)) {
			foreach ($notice_log as $key => $val) {
			    $notice_array['list'][$k]['title'] = $val['message'];
				$notice_array['list'][$k]['notice'] = '系统提示';
				$notice_array['list'][$k]['log_id'] = $val['id'];
				$k++;
			}
		
        $notice_array['order_log_num'] = count($notice_array['list']);
        $json_data = json_encode($notice_array);
        echo $json_data;
        die;
		}
    }
	
		//取消显示通知
    function ajaxorderdrop(Request $request) {
		
		$log_id=$request->param('log_id');
		
        if(empty($log_id)) {
            return;
        }

        $data=[
            'flag'=>1,  //改变状态
            ];
			
		$sett = new info;
		$edit_rows = $sett->save($data,['id' => $log_id]);

        if ($edit_rows) {
            $json_data = json_encode(array('done' => 'true'));
        } else {
            $json_data = json_encode(array('done' => 'false'));
        }
        echo $json_data;
        die;
    }
	
}