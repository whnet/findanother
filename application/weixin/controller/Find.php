<?php
namespace app\weixin\controller;

use think\Controller;
use think\Config;
use \think\Session;
use \think\Cookie;
use EasyWeChat\Foundation\Application;
use \think\exception\Handle;
use think\Request;
use app\weixin\model\Mfind; 
use app\weixin\model\Weixin; 
use app\weixin\model\User; 

class Find extends BaseController
{
	public function index()
    {
		if(Cookie::has('openid')) {
			$openid = Cookie::get('openid');
		}else{
			$options = Config::get('wechat');
			$app = new Application($options);
			$oauth = $app->oauth;
			session('target_url','find/savecookie');
			$oauth->redirect()->send();
		}
		$data = weixin::where('openid',$openid)->find();
		$wid = $data['id'];
		$val = user::where('wid',$wid)->find();
		if($val['ID']){
			$uid = $val['ID'];
		}else{
			$flag = 1;
			$flag2 = 1;
			$url = "-index.php-weixin-info-birthday";
			$message = "请先填写你的出生日期！";
		
			$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);

			exit();
		}
		$mfindda = mfind::where('uid',$uid)->find();
		if(!empty($mfindda)){
			$flag = 1;
			$flag2 = 1;
			$url = "-index.php-weixin-pipei-index";
			$message = "寻找资料已填写,现在开始你的寻爱旅程吧！";
		
			$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);

			exit();
		}else{
			if(request()->ispost()){
			
			   $db = new mfind();
			   $lab_data=[
					'uid'=>$uid,
					'start'=> input("start"),
					'boold'=>input("boold"),
					'height'=>input("height"),
			   ];

			   $bool = $db ->save($lab_data);
			   
			   $dbu = new user();
			   $data=[
					'Loveage'=>input("xiwang"),
			   ];			  
			  
			  $boolu = $dbu ->save($data,['id' => $uid]);

			   if($bool && $boolu){
				$flag = 1; 
				$flag2 = 1;
				$url = "-index.php-weixin-pipei-index";
				$message = "保存成功,现在开始你的寻找旅程吧！";
				$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);

				exit();
			   }
			}else{
				return $this->fetch();
			}
		}
    }
	
	public function savecookie(){
		$user = Cookie::get('wechat_user');
		$open_id = $user['original']['openid'];
		Cookie::set('openid',$open_id,2419200);
		$this->redirect('index');
	}

	public function zhezhao($flag=0,$flag2=0,$url='',$message=''){
		
		$url = (strstr($url,'-')!==false)?str_replace('-','/',$url):$url;
		$this->assign('flag',$flag);
		$this->assign('flag2',$flag2);
		$this->assign('url',$url);
		$this->assign('message',$message);
		return $this->fetch('zhezhao');
	}	
}