<?php
namespace app\weixin\controller;

use think\Controller;
use think\Config;
use \think\Session;
use \think\Cookie;
use EasyWeChat\Foundation\Application;
use app\weixin\model\Access_token;
use \think\exception\Handle;
use app\weixin\model\Sys_set;
use think\Request;

class Callback extends Controller
{
    public function index()
    {
	
		$options = Config::get('wechat');
		$app = new Application($options);
		$oauth = $app->oauth;
		// 获取 OAuth 授权结果用户信息
		$user = $oauth->user(); 
		Cookie::set('wechat_user',$user->toArray(),2419200);
		$this->redirect(session('target_url'));
	}
}