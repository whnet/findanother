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
use app\weixin\model\Beixuan;
use app\weixin\model\Alternative;
use app\weixin\model\District;
use app\weixin\model\Constellation;
use app\weixin\model\Blood;
use app\weixin\model\Photos;
use app\weixin\model\Friends;

//获取当前的url 写入session
$request = Request::instance();
$url = $request->url();
Session::set('preurl',$url);

//$list对方的信息，$self自己的信息 sex:2女 1男
$froms = $request->param('froms');
$findval=mfind::where('uid',$list['nuid'])->find();
//判断两者的关系，防止从旧的地址进来
$suid = $self['ID'];
$uid = $list['ID'];
//先判断know中flag的状态
$knowFlag = know::where(['suid'=>$suid,'uid'=>$uid])->whereOr(['uid'=>$suid,'suid'=>$uid])->find();
if(!empty($knowFlag)){
    $frid = $knowFlag['flag'];
}
//查看备选表中是否有此条记录
$alertFlag = Alternative::where(['suid'=>$suid,'uid'=>$uid])->whereOr(['uid'=>$suid,'suid'=>$uid])->find();
if(!empty($alertFlag)){
    $frid = $alertFlag['flag'];
    if($frid == 1){
    }
}


//判断双方信息是否存在
if(!$list){
    $this->redirect('center/index');
}
if(!$self){
    $this->redirect('center/index');
}


//同城 AND 异性 AND 男》=女的年龄 AND 最佳夫妻 AND 最糟不是夫妻
//判断同城
$duifangArear = $list['Province'].$list['City'];
$zijiArear = $self['Province'].$self['City'];

if($duifangArear === $zijiArear){
    $tongcheng = true;
}else{
   $tongcheng = false;
}
//判断性别,必须是异性
$duifangSex = $list['Sex'];
$zijiSex = $self['Sex'];
if($duifangSex != $zijiSex){
    $xingbie = true;
}else{
    $xingbie = false;
}
//判断星座
$start = $this->birthext($list['Birthday']);//对方星座
$selfstart = $this->birthext($self['Birthday']);//自己星座

//判断年龄
$year=date("Y",time());
$ymd = date('Y-m-d',$list['Birthday']);
$bymd = explode('-',$ymd);
$age=$year-$bymd[0];//对方的年龄

$bymd2 = date('Y-m-d',$self['Birthday']);
$selfymd = explode('-',$bymd2);
$selfage=$year-$selfymd[0];//自己的年龄

if($xingbie){//男的年龄大于女的，或者女的大于男的1-2 岁

    if($zijiSex == 1){//我是男的
        if($selfage - $age >= 0){//作为男的，我比女的大，
            $ages = true;
        }elseif($age - $selfage <=2){//或者女的比我的大1-2岁
            $ages = true;
        }else{
            $ages = false;
        }
    }elseif($zijiSex == 2){//我是女的
        if($selfage - $age <= 2){//我比男的大1-2岁
            $ages = true;
        }elseif($age - $selfage >= 0){//或者男的比我大
            $ages = true;
        }else{
            $ages = false;
        }

    }

}else{
    $ages = false;
}
//计算年龄相差多少,
if($self['Sex']=='1' && $selfage >= $age){
    $newage = $selfage-$age;
    $isnianling =  $newage != 0? "Ta小你".$newage."岁":"年龄相等";

}elseif($self['Sex']=='2' && $selfage <= $age){
    $newage = $age-$selfage;
    $isnianling =  $newage != 0? "Ta大你".$newage."岁":"年龄相等";
}else{
    $isnianling = "年龄不符合";
}



//得出自己的星座，最糟情况，最佳情况
$zjdata = '2008-'.$selfymd[1].'-'.$selfymd[2];
$YC = $this->getConstellation($zjdata);
$dfdata = '2008-'.$bymd[1].'-'.$bymd[2];
$NC = $this->getConstellation($dfdata);
$xingcon = Constellation::where("C_1='".$YC."' and C_2='".$NC."'")->whereOr("C_1='".$NC."' and C_2='".$YC."'")->find();

$best = $xingcon['best'];
$worst = $xingcon['worst'];
if(($best == "夫妻" || $best == "朋友" || $best == "情侣") && $worst !="夫妻"){
    $relation = true;
}else{
    $relation = false;
}
//判断婚姻状态
if($list['Gqzt'] != '已婚' || $list['Gqzt'] == ''){//不是已婚的状态就匹配
    $merray = true;
}else{
    $merray = false;
}

//查看是否在相互认识列表中
//$isKnowother = know::where(['uid'=>$uid,'suid'=>$suid])
//    ->whereOr(['suid'=>$uid,'uid'=>$suid])
//    ->find();
//填写完资料, 逛逛里面, 还有 每周推的里面 不显示备选的人， 不显示已经相互认识了的

//问题出在如果不符合就直接终止，不能匹配下一个用户，要做到可以循环，跳过备选观察的,重新组一个数组

if($froms == 'guangyiguang'){
    if(!$ages || !$tongcheng || !$xingbie || !$relation || !$merray) {
        $flag = 1;
        $flag2 = 1;
        $url = "-index.php-weixin-center-index";
        $message = "您的MR.RIGHT还没有出现！";
        //把备选观察的都放到忽略表中
        $db = new Beixuan();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
        ];
        $db ->save($lab_data);

        $this->redirect('ppbirthday/index',['froms'=>'guangyiguang']);
    }
}

//同城 AND 异性 AND 男》=女的年龄 AND 最佳夫妻 AND 最糟不是夫妻
if($ages && $tongcheng && $xingbie && $relation && $merray){
    if($zijiSex == 1){
        $tuijian = "传说中的Ms Right";
    }else{
        $tuijian = "传说中的Mr Right";
    }

    $result = "匹配数据";
    $content = "<table>
    <tr><td>城市：</td><td>{$list['Province']}-{$list['City']}</td></tr>
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
    <tr><td>城市：</td><td>{$list['Province']}-{$list['City']}</td></tr>
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

$isHulve = Hulue::where('uid',$self['ID'])->where('suid',$list['ID'])->count();
$froms = !empty($request->param('froms'))?$request->param('froms'):0;
$controller = $request->controller();
$this->assign('controller', $controller);
$this->assign('isHulve', $isHulve);
$this->assign('froms', $froms);
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
if($froms == 'gxpipei'){//兼容
    $this->assign('frid', $frid);
}
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

