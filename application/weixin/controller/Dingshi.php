<?php
namespace app\weixin\controller;

use think\Controller;
use think\Config;
use \think\Session;
use \think\Cookie;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Image;
use \think\exception\Handle;
use think\Request;
use app\weixin\model\Mfind; 
use app\weixin\model\Weixin; 
use app\weixin\model\User; 
use app\weixin\model\Know; 
use app\weixin\model\Birthday; 
use app\weixin\model\Hulue; 
use app\weixin\model\Alternative; 
use app\weixin\model\District; 
use app\weixin\model\Constellation; 
use app\weixin\model\Blood; 
use app\weixin\model\Friends;

class Dingshi extends Controller
{
	public function index()
    {
//        echo phpinfo();
//        exit();
		$options = Config::get('wechat');
		$app = new Application($options);
		//所有的会员信息
        $totalString = '';
        //在know表中的会员数据，suid的集合
        $knowUsers = know::select();
        if(!empty($knowUsers)) {
            foreach ($knowUsers as $k => $v) {
                $knowSuids[] = $v['suid'];
                $knowUids[] = $v['uid'];
            }
            $knowUsersIds = array_unique(array_merge($knowSuids, $knowUids));
            $kids = '';
            foreach($knowUsersIds as $k=>$v){
                $kids .= $v.',';
            }
        }else{
            $kids = '';
        }



        //在alternative表中的会员数据
        $alterUsers = Alternative::select();
        if(!empty($alterUsers)){
            foreach($alterUsers as $k=>$v){
                $alterSuids[] = $v['suid'];
                $alterUids[] = $v['uid'];
            }
            $alterUsersIds = array_unique(array_merge($alterSuids, $alterUids));
            $aids = '';
            foreach($alterUsersIds as $k=>$v){
                $aids .= $v.',';
            }

        }else{
            $aids = '';
        }
        //在hulve表中的会员数据
        $hulveUsers = Hulue::select();
        if(!empty($hulveUsers)){
            foreach($hulveUsers as $k=>$v){
                $hulveSuids[] = $v['suid'];
                $hulveUids[] = $v['uid'];
            }
            $hulveUsersIds = array_unique(array_merge($hulveSuids, $hulveUids));
            $hids = '';
            foreach($hulveUsersIds as $k=>$v){
                $hids .= $v.',';
            }
        }else{
            $hids = '';
        }
        //将三个表中的会员id 去重后组成一个字符串
        $totalUsers = substr($kids.$aids.$hids,0,-1);
        $newlists = explode(',', $totalUsers);
        //去除重复的值
        $lists = array_unique($newlists);
        $strings = join(',',$lists);
        //排除 $strings 中的会员
        $alluser = user::alias('a')
            ->field('a.*,a.ID as suid,b.*')
            ->join('weixin b','b.id=a.wid')
            ->where('a.ID','not in',$strings)
            ->select();
        //从alluser 中根据规则进行筛选： 同城 AND 异性 AND 男》=女的年龄 AND 最佳夫妻 AND 最糟不是夫妻 并且只推送一个。

        var_dump($alluser);




	}









		
}