<?php
namespace app\weixin\controller;

use think\Controller;
use think\Config;
use \think\Session;
use \think\Cookie;
use EasyWeChat\Foundation\Application;
use \think\exception\Handle;
use think\Request;
use app\weixin\model\Alternative;
use app\weixin\model\Know;
use app\weixin\model\Hulue;
use app\weixin\model\Mfind;
use app\weixin\model\Weixin;
use app\weixin\model\User;
use app\weixin\model\Friends;
use app\weixin\model\Message;
use app\weixin\model\Tousu;
use app\weixin\model\Article;

class Bus extends BaseController
{

    public function index()
    {

        return $this->fetch();
    }
    public function beixuan(){

        return $this->fetch();
    }
    public function ajaxlikes(){

        $snum = input('page');

        if(Cookie::has('openid')) {
            $openid =Cookie::get('openid');
        }else{
            $options = Config::get('wechat');
            $app = new Application($options);
            $oauth = $app->oauth;
            session('target_url','bus/savecookie');
            $oauth->redirect()->send();
        }

        $data = weixin::where('openid',$openid)->find();
        $wid = $data['id'];
        $val = user::where('wid',$wid)->find();
        if($val['ID']){
            $uid = $val['ID'];
        }else{
            $msg = '请先填写你的出生年月日！';
            $error_code = 404;
            $url='/index.php/weixin/info/birthday';
            echo json_encode(['error_code'=>$error_code,'data'=>'','msg'=>$msg,'url'=>$url]);
        }
        $know_data = know::where('suid',$uid)->where('flag','1')->order('create_at desc')->select();

        foreach($know_data as $know_k => $know_v){

            if(!empty($know_v['addtime']) && time()-$know_v['addtime']>7*24*60*60){   //判断单方添加好友是否超过7天 是 可以继续添加
                $id = $know_v['id'];
                $db = new know();
                $lab_data=[
                    'flag'=>0,
                ];
                $db ->save($lab_data,['id' => $id]);
            }
        }

        $values = know::where('suid',$uid)->where('flag','0')->order('create_at desc')->select();

        if(!empty($values[$snum])){
            $ssuid = $values[$snum]['uid'];
            $likeid = $values[$snum]['id'];
            $flag = $values[$snum]['flag'];


            $renshi=user::alias('a')
                ->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header')
                ->join('weixin b','b.id=a.wid')
                ->where('a.ID',$ssuid)
                ->limit(1)
                ->find();

            if(empty($renshi['Province']) || empty($renshi['City'])){
                $renshi['xianju'] = '未填写';
            }else{
                $renshi['xianju'] = $renshi['Province'].'-'.$renshi['City'];
            }

            $year=date("Y",time());

            $ymd = explode(' ',date("Y-m-d",$renshi['Birthday']));
            $bymd = explode('-',$ymd[0]);
            $renshiage=$year-$bymd[0];

            $data = array(
                'flag' => $flag,
                'likeid' => $likeid,
                'renshiage' =>$renshiage,
                'renshival' =>$values[$snum],
                'renshi' => $renshi,
                'uid' => $uid,
                'renshistart' => $this->birthext(date("Y-m-d",$renshi['Birthday'])),
                'renshishuxing' => $this->birthshuxing(date("Y-m-d",$renshi['Birthday']))
            );
            $msg = '成功';
            $error_code = 0;
            echo json_encode(['error_code'=>$error_code,'data'=>$data,'msg'=>$msg]);

        }else{
            $msg = '暂无数据';
            $error_code = 0;
            echo json_encode(['error_code'=>$error_code,'data'=>'','msg'=>$msg]);
        }
    }

    public function ajaxbeis(){

        $num = input('page');

        if(Cookie::has('openid')) {
            $openid =Cookie::get('openid');
        }else{
            $options = Config::get('wechat');
            $app = new Application($options);
            $oauth = $app->oauth;
            session('target_url','bus/savecookie');
            $oauth->redirect()->send();
        }

        $data = weixin::where('openid',$openid)->find();
        $wid = $data['id'];
        $val = user::where('wid',$wid)->find();
        if($val['ID']){
            $uid = $val['ID'];
        }else{
            $msg = '请先填写你的出生年月日！';
            $error_code = 404;
            $url='/index.php/weixin/info/birthday';
            echo json_encode(['error_code'=>$error_code,'data'=>'','msg'=>$msg,'url'=>$url]);
        }

        $value = alternative::where('suid',$uid)->where('flag','0')->order('create_at desc')->select();
        if(empty($value[$num])){
            $msg = '暂无数据';
            $error_code = 0;
            echo json_encode(['error_code'=>$error_code,'data'=>'','msg'=>$msg]);
        }else{
            $unameid = $value[$num]['uid'];
            $beixuan=user::alias('a')
                ->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header')
                ->join('weixin b','b.id=a.wid')
                ->where('a.ID',$unameid)
                ->limit(1)
                ->find();

            if(empty($beixuan['Province']) || empty($beixuan['City'])){
                $beixuan['xianju'] = '未填写';
            }else{
                $beixuan['xianju'] = $beixuan['Province'].'-'.$beixuan['City'];
            }

            $year=date("Y",time());
            $beixuanymd = explode(' ',date("Y-m-d",$beixuan['Birthday']));
            $beibymd = explode('-',$beixuanymd[0]);
            $beixuanage=$year-$beibymd[0];

            $data = array(
                'beixuanage' =>$beixuanage,
                'beixuanval' =>$value[$num],
                'beixuan' => $beixuan,
                'uid' => $uid,
                'num' => $num+1,
                'beixuanstart' => $this->birthext(date("Y-m-d",$beixuan['Birthday'])),
                'beixuanshuxing' => $this->birthshuxing(date("Y-m-d",$beixuan['Birthday']))
            );
            $msg = '成功';
            $error_code = 0;
            echo json_encode(['error_code'=>$error_code,'data'=>$data,'msg'=>$msg]);
        }
    }

    public function savecookie(){
        $user = Cookie::get('wechat_user');
        $open_id = $user['original']['openid'];
        Cookie::set('openid',$open_id,2419200);
        $this->redirect('index');
    }

    public function birthext($birth)
    {
        if(strstr($birth,'-')===false&&strlen($birth)!==8){

            $birth=date("Y-m-d",$birth);
        }
        if(strlen($birth)===8){
            if(eregi('([0-9]{4})([0-9]{2})([0-9]{2})$',$birth,$bir))
                $birth="{$bir[1]}-{$bir[2]}-{$bir[3]}";
        }
        if(strlen($birth)<8){
            return false;
        }
        $tmpstr= explode('-',$birth);
        if(count($tmpstr)!==3){
            return false;
        }
        $y=(int)$tmpstr[0];
        $m=(int)$tmpstr[1];
        $d=(int)$tmpstr[2];
        $result=array();
        $xzdict=array('摩羯','水瓶','双鱼','白羊','金牛','双子','巨蟹','狮子','处女','天秤','天蝎','射手');
        $zone=array(1222,122,222,321,421,522,622,722,822,922,1022,1122,1222);
        if((100*$m+$d)>=$zone[0]||(100*$m+$d)<$zone[1]){
            $i=0;
        }else{
            for($i=1;$i<12;$i++){
                if((100*$m+$d)>=$zone[$i]&&(100*$m+$d)<$zone[$i+1]){ break; }
            }
        }

        return $xzdict[$i].'座';
    }

    function birthshuxing($birth){

        if(strstr($birth,'-')===false&&strlen($birth)!==8){
            $birth=date("Y-m-d",$birth);
        }
        if(strlen($birth)===8){
            if(eregi('([0-9]{4})([0-9]{2})([0-9]{2})$',$birth,$bir))
                $birth="{$bir[1]}-{$bir[2]}-{$bir[3]}";
        }
        if(strlen($birth)<8){
            return false;
        }
        $tmpstr= explode('-',$birth);
        if(count($tmpstr)!==3){
            return false;
        }
        $y=(int)$tmpstr[0];
        $m=(int)$tmpstr[1];
        $d=(int)$tmpstr[2];

        $sxdict=array('鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪');

        return $sxdict[(($y-4)%12)];
    }

    function hulueb(){
        $num = input('num');
        $uid = input('uid');
        $suid = input('suid');
        $id = input('id');

        $db = new hulue();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
        ];

        $bool = $db ->save($lab_data);
        alternative::where('id',$id)->delete();
        if($bool){
            $msg = '成功';
            $error_code = 0;
            echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
        }else{
            $msg = '失败';
            $error_code = 404;
            echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
        }
    }

    function jieshib(){
        $num = input('num');
        $suid = input('suid');
        $uid = input('uid');
        $tjly = input('tjly');
        $id = input('id');
        $db = new Know();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>$tjly,
        ];

        $db ->save($lab_data);

        alternative::where('id',$id)->delete();

        $msg = '成功';
        $error_code = 0;
        echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
    }

    function xhhelp(){
        $data = Article::find();
        $this->assign('data',$data);
        return $this->fetch();
    }

    function friend(){
       //待废弃 转移到/index.php/weixin/pipei/renshi
        $uid = input('suid');
        $fid = input('fid');
        $kid = input('kid'); //konw 表id
        $type = input('type');
        $tjly = input('tjly');
        $db = new friends();
        //先判断是否已经发送了添加好友请求
        $isHave = $db->where('uid',$uid)->where('fid',$fid)->count();
        if($isHave){
            $error_code='0';
            $msg="请勿重复请求！";
            die(json_encode(['error_code'=>$error_code,'msg'=>$msg]));
        }

        $frid = 0;
        if($type == 0){
                $kdb = new Know();   //标记这个id已发送添加好友消息
                if($kid=='kid'){
                    $lab_kdata=[
                        'uid'=>$fid,
                        'suid'=>$uid,
                        'tjly'=>$tjly,
                        'flag'=>1,
                        'addtime'=>time(),
                    ];

                    $kid = $db ->insertGetId($lab_kdata);
                }else{
                    $lab_kdata=[
                        'flag'=>1,
                        'addtime'=>time(), //标记添加时间七天后继续添加
                    ];

                    $kdb ->save($lab_kdata,['id' => $kid]);
                }
        }else if($type == 1){
            $kdb = new Alternative();
            $lab_kdata=[
                'flag'=>1,
                'addtime'=>time(), //标记添加时间七天后继续添加
            ];

            $kdb ->save($lab_kdata,['id' => $kid]);
        }

     //发送模板消息
        $data=user::alias('a')
            ->field('b.nickname as name,b.*,a.*')
            ->join('weixin b','b.id=a.wid')
            ->where('a.ID',$uid)
            ->find();


        $fdata=user::alias('a')
            ->field('b.nickname as name,b.*,a.*')
            ->join('weixin b','b.id=a.wid')
            ->where('a.ID',$fid)
            ->find();

        $options = Config::get('wechat');
        $app = new Application($options);
        $notice = $app->notice;
        $userId = $fdata["openid"];
        $templateId = 'CXhc6nO5CRoOWt9LQ05a_8XeDHd_CYqmJPULXl9snPc';
        $url = 'http://weixin.matchingbus.com/index.php/weixin/detail/index/uid/'.$uid.'/suid/'.$fid.'/frid/'.$frid.'/kid/'.$kid.'/jh/1/flag2/2/type/'.$type;
        $data = array(
            "first"  => "有人想认识你一下:",
            "keyword1"   => $data['name'],
            "keyword2"  => "星数奇缘",
            "keyword3"  => date("Y-m-d",time()),
            "remark" => "点击这里查看TA的详细资料，同意后可以互相看到微信号",
        );
        $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
        //发送模板消息END
        $error_code='0';
        $msg="添加好友成功，等待对方同意，七天后可再次请求！";
        echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
    }

    function agree(){
        $fid = input('fid');  //想加为好友那个人的id
        $uid = input('suid'); //同意者的id
        $id = input('frid');  //发起请求者的朋友id
        $kid = input('kid');  //konw 表id
        $type = input('type');  // 添加好友的方式
        $froms = input('froms');  // 同意好友请求的来源 from = 1 从likeme， 其余是从detail中来
        //判断是否填写了微信号
        $currentInfo = User::where('ID',$uid)->find();
        if(!$currentInfo['wxnumber']){
            echo json_encode(['error_code'=>2,'id'=>$currentInfo['ID'],'msg'=>'请填写微信号','url'=>'index']);
            exit();
        }
        //更新对应表中的状态
        //<!--type: 1-myfriend, 2-mylike, 3-likeme, 4-mysaw, 5-knowothers, -->
        //<!--from: 1-myfriend, 2-mylike, 3-likeme, 4-mysaw, 5-knowothers, -->
        if($type == 3){
            $db = new know();
            $isHave = $db->where('uid',$uid)->where('suid',$fid)->find();
        }else if($type == 4){
            $db = new Alternative();
            $isHave = $db->where('uid',$uid)->where('suid',$fid)->find();
        }

        if($froms == 'likeme'){
            if($isHave['flag'] != 1){
                $error_code= 0;
                $msg="请勿重复请求！";
                die(json_encode(['error_code'=>$error_code,'msg'=>$msg]));
            }
        }

        if($type == 3) {
            //同时改变friends中flag状态
            $friendsDb = new Friends();
            $isHaveFriend = $friendsDb->where('uid',$uid)->where('fid',$fid)->find();

            $friendsData = [
                'flag' => 2,
            ];
            $friendsDb->save($friendsData, ['id' => $isHaveFriend['id']]);

            $knowDb = new know();
            $lab_data = [
                'flag' => 2,
                'addtime' => time(),
            ];
            $types = 'likeme';
            $knowDb->save($lab_data, ['id' => $isHave['id']]);
            //如果是从朋友圈中来的，则将朋友圈中的状态也改变，新增一个status状态,他的值为friends中的id

            $knowDb = new Friends();
            $lab_data = [
                'flag' => 2,
            ];
            $types = 'likeme';
            $knowDb->save($lab_data, ['id' => $isHave['status']]);
        }elseif($type == 4){
            $knowDb = new Alternative();
            $lab_data = [
                'flag' => 2,
                'addtime' => time(),
            ];
            $types = 'mysaw';
            $knowDb->save($lab_data, ['id' => $isHave['id']]);
        }
		$data=user::alias('a')
            ->field('b.nickname as name,b.*,a.*')
            ->join('weixin b','b.id=a.wid')
            ->where('a.ID',$uid)
            ->find();

		$mdb = new Message();
        $lab_mdata=[
            'sendid'=>$uid,
            'toid'=>$fid,
            'message'=>'用户'.$data['name'].'，通过了你的好友请求，他的微信号为'.$data['wxnumber'],
            'flag'=>'0',
            'fid'=>$id,
        ];
       $mdb ->save($lab_mdata);

        //给对方发送模板消息
        $fdata=user::alias('a')
            ->field('b.nickname as name,b.*,a.*')
            ->join('weixin b','b.id=a.wid')
            ->where('a.ID', $fid)
            ->find();
        $data=user::alias('a')
            ->field('b.nickname as name,b.*,a.*')
            ->join('weixin b','b.id=a.wid')
            ->where('a.ID',$uid)
            ->find();

        $options = Config::get('wechat');
        $app = new Application($options);
        $notice = $app->notice;
        $userId = $fdata["openid"];
        $templateId = 'CXhc6nO5CRoOWt9LQ05a_8XeDHd_CYqmJPULXl9snPc';
        $url = 'http://weixin.matchingbus.com/index.php/weixin/detail/index/uid/'.$uid.'/suid/'.$fid.'/froms/fromWechatToAgreed/type/'.$type;
        $data = array(
            "first"  => $data['name']."已经同意了您的好友请求",
            "keyword1"   => $data['name'],
            "keyword2"  => "星数奇缘",
            "keyword3"  => date("Y-m-d",time()),
            "remark" => "点击这里查看TA的微信号",
        );
        $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();

		$error_code='0';
	   $msg="相互认识成功";
	   echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
	}

    function tousu(Request $request){

        $tuid = input('tuid');
        $btuid = input('btuid');
        if(empty($tuid) || empty($btuid)){
            $this->redirect('info/index');
        }
        if(request()->ispost()){

            $data = tousu::where('tuid',$tuid)->where('btuid',$btuid)->find();
            if(!empty($data)){
                $flag = 1;
                $flag2 = 1;
                $url = "-index.php-weixin-bus-index";
                $message = "你已经投诉过了,不能重复投诉！";

                $this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);
                exit();
            }

            $db = new tousu();
            $lab_data=[
                'tuid'=>input('tuid'),
                'btuid'=>input("btuid"),
                'title'=>input("title"),
                'content'=>input("content"),
            ];

            $val = $db ->save($lab_data);
            if($val){
                $flag = 1;
                $flag2 = 1;
                $url = "-index.php-weixin-bus-index";
                $message = "投诉成功,等待管理员处理！";

                $this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);
                exit();
            }else{
                $flag = 1;
                $flag2 = 1;
                $url = "-index.php-weixin-bus-index";
                $message = "投诉失败！";

                $this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);

                exit();
            }
        }else{
            $this->assign('tuid',$tuid);
            $this->assign('btuid',$btuid);
            return $this->fetch();
        }
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