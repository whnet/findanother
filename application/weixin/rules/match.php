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

$findval=mfind::where('uid',$list['nuid'])->find();
$year=date("Y",time());
$ymd = date('Y-m-d',$list['Birthday']);
$bymd = explode('-',$ymd);
$age=$year-$bymd[0];
$start = $this->birthext($list['Birthday']);
$selfstart = $this->birthext($self['Birthday']);
$bymd2 = date('Y-m-d',$self['Birthday']);
$selfymd = explode('-',$bymd2);
$selfage=$year-$selfymd[0];

$isnianling = "年龄符合";
if($self['Sex']=='1' && $selfage > $age){
    $fage=true;
    $newage = $selfage-$age;
    $isnianling = "Ta小你".$newage."岁";
}elseif($self['Sex']=='2' && $selfage <= $age){
    $fage=true;
    $newage = $age-$selfage;
    $isnianling = "Ta大你".$newage."岁";
}else{
    $fage=false;
    $isnianling = "年龄相差太大了";
}
//得出自己的星座
$zjdata = '2008-'.$selfymd[1].'-'.$selfymd[2];
$YC = $this->getConstellation($zjdata);
$dfdata = '2008-'.$bymd[1].'-'.$bymd[2];
$NC = $this->getConstellation($dfdata);
$xingcon = Constellation::where("C_1='".$YC."' and C_2='".$NC."'")->whereOr("C_1='".$NC."' and C_2='".$YC."'")->find();
//最糟情况，最佳情况
$worst = $xingcon['worst'];
$best = $xingcon['best'];
//匹配意愿
$data = $this->match_others($xingcon['best'], $self['Wanna']);
$heshiweizhi = $data[0];
$bestfind = $data[1];
if($fage && $bestfind){
    $tuijian = "认识一下";
    $result = "匹配数据";
    $content = "<table>
    <tr><td>城市：</td><td>{$self['Province']}-{$self['City']}</td></tr>
    <tr><td>性别：</td><td>{$istxyx}</td></tr>
    <tr><td>年龄：</td><td>{$isnianling}</td></tr>
    <tr>
    <td>48星区：</td>
    <td>最佳{$best}</td>
    <td><a id='xingquc' href='xingqu/self/".$self['Birthday']."/list/".$list['Birthday']."'>点击查看</a></td>
    </tr>
    </table>";
    $tjly = $best.$worst;
}else{
    $tuijian = "备选观察";
    $result = "匹配数据";
    $content = "<table>
    <tr><td>城市：</td><td>{$self['Province']}-{$self['City']}</td></tr>
    <tr><td>性别：</td><td>{$istxyx}</td></tr>
    <tr><td>年龄：</td><td>{$isnianling}</td></tr>
    <tr>
    <td>48星区：</td>
    <td>最佳{$best}</td>
    <td><a id='xingquc' href='xingqu/self/".$self['Birthday']."/list/".$list['Birthday']."'>点击查看</a></td>
    </tr>
    <tr>
    
    </tr>
    </table>";
    $tjly = $best.$worst;
}
$from = !empty($request->param('from'))?$request->param('from'):0;
$controller = $request->controller();
$this->assign('controller', $controller);
$this->assign('from', $from);
$this->assign('type', 0);
$this->assign('tuijian', $tuijian);
$this->assign('result', $result);
$this->assign('content', $content);
$this->assign('uid', $list['nuid']);
$this->assign('suid', $suid);
$this->assign('num', $num+1);
$this->assign('age', $age);
$this->assign('tjly', $tjly);
$this->assign('start', $start);
$this->assign('list', $list);
$this->assign('fval',$findval);
$xiangce = photos::where('uid',$list['nuid'])->select();
if(!empty($xiangce)){
    $flag = 1;
}else{
    $flag = 2;
}
$intes = explode(",",$list['interest']);
$this->assign('shuxing',$this->birthshuxing(date('Y-m-d',$list['Birthday'])));

$this->assign('intes', $intes);
$this->assign('xc', $xiangce);
$this->assign('flag', $flag);

