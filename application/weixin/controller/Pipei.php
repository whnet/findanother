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
use app\weixin\model\Know;
use app\weixin\model\Hulue;
use app\weixin\model\Birthday;
use app\weixin\model\Alternative;
use app\weixin\model\District;
use app\weixin\model\Constellation;
use app\weixin\model\Blood;
use app\weixin\model\Photos;
use app\weixin\model\Friends;
class Pipei extends BaseController
{
    public function index(Request $request)
    {
        // 判断四个表，Gxpipei中不需要这几个，而pipei 和 Ppbirthday 需要
        require_once(dirname(dirname(__FILE__)).'/rules/except.php');
        // 判断四个表 END
        if(empty($list)){
            $flag = 1;
            $flag2 = 1;
            $url = "-index.php-weixin-center-index";
            $message = "您的MR.RIGHT还没有出现！";

            $this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);
        }else{

            //三个文件相同的部分START
            require_once(dirname(dirname(__FILE__)).'/rules/match.php');
            //三个文件相同的部分END
            return $this->fetch('combine/index');
        }
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

        return $xzdict[$i];
    }

    public function hulue(Request $request){
        $num = $request->param('num');
        $uid = $request->param('uid');
        $suid = $request->param('suid');
        $flag = $request->param('flag');

        $val1 = Alternative::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val1)){
            echo json_encode(['error_code'=>1,'msg'=>'已经添加到了备选列表','url'=>'index']);
            exit();
        }

        $val2 = Know::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val2)){
            echo json_encode(['error_code'=>1,'msg'=>'已经添加到了认识列表','url'=>'index']);
            exit();
        }

        $val3 = Hulue::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val3)){
            echo json_encode(['error_code'=>1,'msg'=>'已添加到了忽略列表,不能重复添加！','url'=>'index']);
            exit();
        }


        $db = new Hulue();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
        ];
        $db ->save($lab_data);

        if($flag == 1){
            echo json_encode(['error_code'=>1,'url'=>'info/index','msg'=>'忽略成功,您的资料还未填写完整，请填写完整再来寻找你的Ta吧！']);
        }else{
            echo json_encode(['error_code'=>1,'url'=>'index','msg'=>'忽略成功！']);
        }

        exit();
    }
    public function fromCenterToHulve(Request $request){
        $num = 0;
        $uid = $request->param('uid');
        $suid = $request->param('suid');
        $flag = 0;


        $val3 = Hulue::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val3)){
            echo json_encode(['error_code'=>1,'msg'=>'已添加到了忽略列表,不能重复添加！','url'=>'index']);
            exit();
        }

        $db = new Hulue();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
        ];
        $db ->save($lab_data);
        //同时删除备选表中的信息
        $deAlertNative::where('uid',$uid)->where('suid',$suid)->delete();


        if($flag == 1){
            echo json_encode(['error_code'=>1,'url'=>'info/index','msg'=>'忽略成功,您的资料还未填写完整，请填写完整再来寻找你的Ta吧！']);
        }else{
            echo json_encode(['error_code'=>1,'url'=>'index','msg'=>'忽略成功！']);
        }

        exit();
    }
    public function beixuan(Request $request){
        $num = $request->param('num');
        $uid = $request->param('uid');
        $suid = $request->param('suid');
        $tjly = $request->param('tjly');
        $flag = $request->param('flag');

        $val1 = Hulue::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val1)){
            echo json_encode(['error_code'=>1,'msg'=>'已经添加到了忽略列表！','url'=>'index']);
            exit();
        }


        $val2 = Know::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val2)){
            echo json_encode(['error_code'=>1,'msg'=>'已经添加到了认识列表！','url'=>'index']);
            exit();
        }

        $val3 = Alternative::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val3)){
            echo json_encode(['error_code'=>1,'msg'=>'已添加到了备选列表，不能重复添加！','url'=>'index']);
            exit();
        }

        $db = new Alternative();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>$tjly,
        ];
        $db ->save($lab_data);

        if($flag == 1){
            echo json_encode(['error_code'=>1,'url'=>'info/index','msg'=>'添加成功,您的资料还未填写完整，请填写完整再来寻找你的Ta吧！']);
        }else{
            echo json_encode(['error_code'=>1,'url'=>'index','msg'=>'添加成功！']);
        }
    }

    public function renshi(Request $request){
        $num = $request->param('num');
        $uid = $request->param('uid');
        $suid = $request->param('suid');
        $tjly = $request->param('tjly');
        $flag = $request->param('flag');
        $type = $request->param('type');
        $id = $request->param('kid');
        $kid = $request->param('id');
        //判断是否填写了微信号
        $currentInfo = User::where('ID',$suid)->find();
        if(!$currentInfo['wxnumber']){
            echo json_encode(['error_code'=>2,'id'=>$currentInfo['ID'],'msg'=>'请填写微信号','url'=>'index']);
            exit();
        }

exit();

        $val1 = Hulue::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val1)){
            echo json_encode(['error_code'=>1,'msg'=>'已经添加到了忽略列表！','url'=>'index']);
            exit();
        }

        $val2 = Alternative::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val2)){
            echo json_encode(['error_code'=>1,'msg'=>'已经添加到了备选列表！','url'=>'index']);
            exit();
        }


        $val3 = Know::where('uid',$uid)->where('suid',$suid)->find();

        if(!empty($val3)){
            echo json_encode(['error_code'=>1,'msg'=>'已添加到了认识列表,不能重复添加！','url'=>'index']);
            exit();
        }
        //修改 1-myfriend, 2-mylike, 3-likeme, 4-mysaw, 5-knowothers, 6-guangyiguang,
        $types = 6;
    if($type == 2 || $type == 3 || empty($type)){
        $db = new Know();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>'我想认识',
            'flag'=>1,
            'addtime'=>time(),
        ];
        $types = 3;
        $id = $db ->save($lab_data);
    }elseif($type == 4){
        $db = new Alternative();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>'加入备选',
            'flag'=>1,
            'addtime'=>time(),
        ];
        $types = 4;
        $id = $db ->save($lab_data,['id' => $id]);
    }elseif($type == 1){
        //从朋友全中相加,将数据放到know中
        $db = new Know();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>'从我的朋友圈中添加我想认识',
            'flag'=>1,
            'status'=>$kid,//为know中的id
            'addtime'=>time(),
        ];
        $types = 3;
        $id = $db ->save($lab_data);
        //同时更新 friends对应的flag状态
            $db = new Friends();
            $lab_data=[
                'flag'=>1,
            ];
            $id = $db ->save($lab_data,['id' => $kid]);

    }
    if(!empty($id)){
            if(!$id){
                echo json_encode(['error_code'=>1,'url'=>'','msg'=>'失败']);
                exit();
            }
    }

        if($flag == 1){
            echo json_encode(['error_code'=>1,'url'=>'info/index','msg'=>'您的资料还未填写完整，请填写完整再来寻找你的Ta吧！']);
            exit();
        }else{
            //发送模板消息
            $frid = 1;
            $kid = 0;
            $type = $type;
            $data=user::alias('a')
                ->field('b.nickname as name,b.*,a.*')
                ->join('weixin b','b.id=a.wid')
                ->where('a.ID',$suid)
                ->find();

            $fdata=user::alias('a')
                ->field('b.nickname as name,b.*,a.*')
                ->join('weixin b','b.id=a.wid')
                ->where('a.ID',$uid)
                ->find();
            $options = Config::get('wechat');
            $app = new Application($options);
            $notice = $app->notice;
            $userId = $fdata["openid"];
            $templateId = 'CXhc6nO5CRoOWt9LQ05a_8XeDHd_CYqmJPULXl9snPc';
            //flag2 = 1 等待同意,
            $url = 'http://weixin.matchingbus.com/index.php/weixin/detail/index/suid/'.$uid.'/uid/'.$suid.'/frid/'.$frid.'/from/renshi/status/toagree/kid/'.$kid.'/jh/1/type/'.$types;
            $data = array(
                "first"  => "有人想认识你一下:",
                "keyword1"   => $data['name'],
                "keyword2"  => "星数奇缘",
                "keyword3"  => date("Y-m-d",time()),
                "remark" => "点击这里查看TA的详细资料，同意后可以互相看到微信号",
            );
            $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
            //发送模板消息END
            echo json_encode(['error_code'=>1,'url'=>'index','msg'=>'添加成功！']);
        }


    }

    public function AlertRenshi(Request $request){
        $num = $request->param('num');
        $uid = $request->param('uid');
        $suid = $request->param('suid');
        $tjly = $request->param('tjly');
        $flag = $request->param('flag');
        $type = $request->param('type');
        $id = $request->param('id');

        //修改 将认识一下 改成 直接加为好友 fuck
    if($type == 0){
        $db = new Know();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>'我想认识',
            'flag'=>1,
            'addtime'=>time(),
        ];
        $id = $db ->save($lab_data);
    }elseif($type == 1){
        $db = new Alternative();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>'从备选中添加好友',
            'flag'=>1,
            'addtime'=>time(),
        ];
        $id = $db ->save($lab_data,['id' => $id]);
    }

        if($flag == 1){
            echo json_encode(['error_code'=>1,'url'=>'info/index','msg'=>'您的资料还未填写完整，请填写完整再来寻找你的Ta吧！']);
            exit();
        }else{
            //发送模板消息
            $frid = 1;
            $kid = 0;
            $type = 4;
            $data=user::alias('a')
                ->field('b.nickname as name,b.*,a.*')
                ->join('weixin b','b.id=a.wid')
                ->where('a.ID',$suid)
                ->find();

            $fdata=user::alias('a')
                ->field('b.nickname as name,b.*,a.*')
                ->join('weixin b','b.id=a.wid')
                ->where('a.ID',$uid)
                ->find();
            $options = Config::get('wechat');
            $app = new Application($options);
            $notice = $app->notice;
            $userId = $fdata["openid"];
            $templateId = 'CXhc6nO5CRoOWt9LQ05a_8XeDHd_CYqmJPULXl9snPc';
            //flag2 = 1 等待同意,
            $url = 'http://weixin.matchingbus.com/index.php/weixin/detail/index/suid/'.$uid.'/uid/'.$suid.'/frid/'.$frid.'/from/mysaw/status/toagree/kid/'.$kid.'/jh/1/type/'.$type;
            $data = array(
                "first"  => "有人想认识你一下:",
                "keyword1"   => $data['name'],
                "keyword2"  => "星数奇缘",
                "keyword3"  => date("Y-m-d",time()),
                "remark" => "点击这里查看TA的详细资料，同意后可以互相看到微信号",
            );
            $result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
            //发送模板消息END
            echo json_encode(['error_code'=>1,'url'=>'index','msg'=>'添加成功！']);
        }


    }

    public function tongjixue(Request $request){
        $bdata = $request->param('birthdata');
        if(empty($bdata)){
            $this->redirect('info/index');
        }
        $valbb = date('Y-n-j',$bdata);
        $val = explode('-',$valbb);
        $birth = $val[1]."/".$val[2];
        $list = birthday::where('birth',$birth)->find();
        //echo birthday::getLastSql();
        $this->assign('list', $list);
        return $this->fetch();

    }

    public function xingqu(Request $request){

        $ziji = $request->param('self');
        $duifang = $request->param('list');
        if(empty($ziji) || empty($duifang)){
            $this->redirect('info/index');
        }
        $zijivalb = date('Y-n-j',$ziji);
        $zijival = explode('-',$zijivalb);
        $zjm = $zijival[1];
        $zjd = $zijival[2];
        $zjdata = '2008-'.$zjm.'-'.$zjd;
        if(strtotime($zjdata) >=strtotime("2008-12-26") or strtotime($zjdata)<=strtotime("2008-1-2")){
            $YC = "魔羯座一";
        }else{
            $disval = District::where('birthday1',"<=",$zjdata)->where('birthday2',">=",$zjdata)->find();
            $YC = $disval['constellation'];
        }
        $duifangvalbb = date('Y-n-j',$duifang);
        $duifangval = explode('-',$duifangvalbb);
        $dfm = $duifangval[1];
        $dfd = $duifangval[2];
        $dfdata = '2008-'.$dfm.'-'.$dfd;
        if(strtotime($dfdata) >=strtotime("2008-12-26") or strtotime($dfdata)<=strtotime("2008-1-2")){
            $NC = "魔羯座一";
        }else{
            $disval2 = District::where('birthday1',"<=",$dfdata)->where('birthday2',">=",$dfdata)->find();
            $NC = $disval2['constellation'];
        }
        $lists = Constellation::where("C_1='".$YC."' and C_2='".$NC."'")->whereOr("C_1='".$NC."' and C_2='".$YC."'")->find();
        //echo Constellation::getLastSql();
        $this->assign('list', $lists);
        return $this->fetch();
    }

    public function xuexing(Request $request){
        $ziji = $request->param('self');
        $duifang = $request->param('list');
        if(empty($ziji) || empty($duifang)){
            $this->redirect('info/index');
        }
        $list = blood::where("blood1='".$ziji."' and blood2='".$duifang."'")->whereOr("blood1='".$duifang."' and blood2='".$ziji."'")->find();
        $this->assign('list', $list);
        return $this->fetch();
    }
    public function dehulue(){
        $uid = input('uid');
        $suid = input('suid');

        $val1 = Alternative::where('uid',$uid)->find();

        if(!empty($val1)){
            echo json_encode(['error_code'=>0,'msg'=>'已添加到了备选列表！']);
            exit();
        }

        $val2 = Know::where('uid',$uid)->find();

        if(!empty($val2)){
            echo json_encode(['error_code'=>0,'msg'=>'已添加到了认识列表！']);
            exit();
        }

        $val3 = Hulue::where('uid',$uid)->find();

        if(!empty($val3)){
            echo json_encode(['error_code'=>0,'msg'=>'已添加到了忽略列表！']);
            exit();
        }

        $db = new Hulue();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
        ];
        $flag = $db ->save($lab_data);

        if($flag){
            echo json_encode(['error_code'=>0,'msg'=>'添加到忽略列表成功！']);
        }else{
            echo json_encode(['error_code'=>1,'msg'=>'添加到忽略列表失败！']);
        }
    }

    public function debeixuan(){
        $uid = input('uid');
        $suid = input('suid');
        $tjly = input('tjly');


        $val1 = Hulue::where('uid',$uid)->find();

        if(!empty($val1)){
            echo json_encode(['error_code'=>0,'msg'=>'已添加到了忽略列表！']);
            exit();
        }

        $val2 = Know::where('uid',$uid)->find();

        if(!empty($val2)){
            echo json_encode(['error_code'=>0,'msg'=>'已添加到了认识列表！']);
            exit();
        }

        $val3 = Alternative::where('uid',$uid)->find();

        if(!empty($val3)){
            echo json_encode(['error_code'=>0,'msg'=>'已添加到了备选列表！']);
            exit();
        }


        $db = new Alternative();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>$tjly,
        ];
        $flag = $db ->save($lab_data);

        if($flag){
            echo json_encode(['error_code'=>0,'msg'=>'添加到备选列表成功！']);
        }else{
            echo json_encode(['error_code'=>1,'msg'=>'添加备选列表失败！']);
        }
    }

    public function derenshi(){
        $uid = input('uid');
        $suid = input('suid');
        $tjly = input('tjly');


        $val1 = Hulue::where('uid',$uid)->find();

        if(!empty($val1)){
            echo json_encode(['error_code'=>0,'msg'=>'已添加到了忽略列表！']);
            exit();
        }

        $val2 = Alternative::where('uid',$uid)->find();

        if(!empty($val2)){
            echo json_encode(['error_code'=>0,'msg'=>'已添加到了备选列表！']);
            exit();
        }

        $val3 = Know::where('uid',$uid)->find();

        if(!empty($val3)){
            echo json_encode(['error_code'=>0,'msg'=>'已添加到了认识列表！']);
            exit();
        }


        $db = new Know();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>$tjly,
        ];
        $flag = $db ->save($lab_data);

        if($flag){
            echo json_encode(['error_code'=>0,'msg'=>'加到认识列表成功！']);
        }else{
            echo json_encode(['error_code'=>1,'msg'=>'添加认识列表失败！']);
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