<?php
namespace app\weixin\rules;

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
use app\weixin\model\Photos;
use app\weixin\model\Friends;

$uid = $request->param('id');
$num = $request->param('num');
$num = !empty($num)?$num:0;
$limit = 1;
$openid = Cookie::get('openid');
//找到自己的信息
$self=user::alias('a')
    ->field('a.*,a.ID as suid,b.*')
    ->join('weixin b','b.id=a.wid')
    ->where('b.openid',$openid)
    ->find();
//echo user::getLastSql();

//user表中的id
$suid = $self['suid'];
$lastdb=new User();
$lastdata=[
    'Lasttime'=>time(),
];
$lastdb->save($lastdata,['ID' => $suid]);
//是否在认识表
$get_val = know::where('suid',$suid)->select();
if(!empty($get_val)){
$usersid="";
foreach($get_val as $key => $val){
$usersid.=$val['uid'].",";
}
$usersid = substr($usersid,0,-1);
$where1 = "a.ID not in ($usersid)";
}else{
$where1 = '1=1';
}
//是否在忽略列表中
$hget_val = hulue::where('suid',$suid)->select();
if(!empty($hget_val)){
$huusersid="";
foreach($hget_val as $hkey => $hval){
$huusersid .=$hval['uid'].",";
}
$huusersid = substr($huusersid,0,-1);
$where3 = "a.ID not in ($huusersid)";
}else{
$where3 = '1=1';
}
//是否在备选列表中
$get_alval = Alternative::where('suid',$suid)->select();
if(!empty($get_alval)){
$ausersid="";
foreach($get_alval as $alkey => $alval){
$ausersid.=$alval['uid'].",";
}
$ausersid = substr($ausersid,0,-1);
$where2 = "a.ID not in ($ausersid)";
}else{
$where2 = '1=1';
}
//是否在好友列表中
$get_frval = Friends::where('uid',$suid)->select();
if(!empty($get_frval)){
$userfsid="";
foreach($get_frval as $key => $val){
$userfsid.=$val['fid'].",";
}
$userfsid = substr($userfsid,0,-1);
$where4 = "a.ID not in ($userfsid)";
}else{
$where4 = '1=1';
}
//判断是同性还是异性，这里应该改成user表中，而不是weixin表
if($self['Sex']=='1'){
    $yi = 2;
}else{
    $yi = 1;
}

//如果在就
$istxyx = "异性";
$newlist=user::alias('a')
    ->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header,b.*')
    ->join('weixin b','b.id=a.wid')
    ->where('a.Sex',$yi)
    ->where($where1)
    ->where($where2)
    ->where($where3)
    ->where($where4)
    ->where('a.ID','<>',$suid)
    ->order('a.ID asc')
    ->select();

/*
 * 朋友、夫妻、情侣这三个关系，是同城且异性，就匹配！否则不推荐的
 */
$str = '';
if($newlist){
    foreach($newlist as $k=>$v){

        //得出自己和其他人的星座
        $myYmd = explode('-',date('Y-m-d',$self['Birthday']));
        $myData = '2008-'.$myYmd[1].'-'.$myYmd[2];
        $myConstellation = $this->getConstellation($myData);
        $otherYmd = explode('-',date('Y-m-d',$newlist[$k]['Birthday']));
        $otherData = '2008-'.$otherYmd[1].'-'.$otherYmd[2];
        $otherConstellation = $this->getConstellation($otherData);

        //查出他们的最佳为朋友、夫妻、情侣的数据
        $Constellation = Constellation::where("C_1='".$myConstellation."' and C_2='".$otherConstellation."'")->whereOr("C_1='".$otherConstellation."' and C_2='".$myConstellation."'")->find();
        $best = $Constellation['best'];
        $ifExist = (strpos($best,'朋友') !== false || strpos($best,'夫妻') !== false || strpos($best,'情侣') !== false);
        //得出自己和其他人的星座 END
        if($ifExist){
            $str .= $v['id'].',';
        }


    }
}
//去除最后一个字符串
$listStr = substr($str,0,-1);
$list=user::alias('a')
    ->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header,b.*')
    ->join('weixin b','b.id=a.wid')
    ->where('a.Sex',$yi)
    ->where($where1)
    ->where($where2)
    ->where($where3)
    ->where($where4)
    ->where('a.wid','in',$listStr)
    ->order('a.ID asc')
    ->find();