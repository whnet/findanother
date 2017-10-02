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
        $num = $request->param('num');
        $num = !empty($num)?$num:0;
        $limit = "1";
        $openid = Cookie::get('openid');
        $selfval=user::alias('a')
            ->field('a.ID as suid')
            ->join('weixin b','b.id=a.wid')
            ->join('mfind c','c.uid=a.ID')
            ->where('b.openid',$openid)
            ->find();
        //echo user::getLastSql();
        $valsuid = $selfval['suid'];
        if(empty($valsuid)){
            $self=user::alias('a')
                ->field('a.*,a.ID as suid,b.*')
                ->join('weixin b','b.id=a.wid')
                ->where('b.openid',$openid)
                ->find();
        }else{
            $self=user::alias('a')
                ->field('a.*,a.ID as suid,b.*,c.*')
                ->join('weixin b','b.id=a.wid')
                ->join('mfind c','c.uid=a.ID')
                ->where('b.openid',$openid)
                ->find();
        }
        $suid = $self['suid'];
        $lastdb=new User();
        $lastdata=[
            'Lasttime'=>time(),
        ];
        $lastdb->save($lastdata,['ID' => $suid]);
        //认识
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
        //忽略
        $hget_val = hulue::where('suid',$suid)->select();
        if(!empty($hget_val)){
            $huusersid="";
            foreach($hget_val as $hkey => $hval){
                $huusersid.=$hval['uid'].",";
            }
            $huusersid = substr($huusersid,0,-1);
            $where3 = "a.ID not in ($huusersid)";
        }else{
            $where3 = '1=1';
        }


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
        //好友
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
        //判断是同性还是异性
        if($self['Sex']=='1'){
            $tong = 1;
            $yi = 2;
        }else{
            $tong = 2;
            $yi = 1;
        }
        if($self['Wanna']=='同性朋友' || $self['Wanna']=='同性恋人'){
            $list=user::alias('a')
                ->field('a.*,a.ID as nuid,b.*,b.nickname as name ,b.headimgurl as header,b.*')
                ->join('weixin b','b.id=a.wid')
                ->where('a.Sex',$tong)
                ->where($where1)
                ->where($where2)
                ->where($where3)
                ->where($where4)
                ->where('a.ID','<>',$valsuid)
                ->order('a.ID asc')
                ->limit($limit)
                ->select();
            $istxyx = "非异性";

        }else{
            $list=user::alias('a')
                ->field('a.*,a.ID as nuid,b.*,b.nickname as name ,b.headimgurl as header,b.*')
                ->join('weixin b','b.id=a.wid')
                ->where('a.Sex',$yi)
                ->where($where1)
                ->where($where2)
                ->where($where3)
                ->where($where4)
                ->order('a.ID asc')
                ->limit($limit)
                ->select();
            echo user::getLastSql();
            $istxyx = "异性";
        }
        if(empty($list)){
            $flag = 1;
            $flag2 = 1;
            $url = "-index.php-weixin-center-index";
            $message = "您的MR.RIGHT还没有出现！";

            $this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);
            exit();
        }else{
            $list = $list[0];
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

            if($self['Loveage']=='合适就行'){    //年龄
                $fage=true;
                $isnianling = "年龄符合";
            }elseif($self['Loveage']=='小我0-3岁' && $selfage>=$age && $age>=$selfage-3){
                $fage=true;
                $newage = $selfage-$age;
                $isnianling = "Ta小你".$newage."岁";
            }elseif($self['Loveage']=='小我3岁以上' && $age<$selfage-3){
                $fage=true;
                $newage = $selfage-$age;
                $isnianling = "Ta小你".$newage."岁";
            }elseif($self['Loveage']=='大我0-3岁' && $selfage<=$age && $age<=$selfage+3){
                $fage=true;
                $newage = $age-$selfage;
                $isnianling = "Ta大你".$newage."岁";
            }elseif($self['Loveage']=='大我3岁以上' && $age>$selfage+3){
                $fage=true;
                $newage = $age-$selfage;
                $isnianling = "Ta大你".$newage."岁";
            }else{
                if($self['Sex']=='1' && $selfage > $age){    //判断是同性还是异性  男
                    $fage=true;
                    $newage = $selfage-$age;
                    $isnianling = "Ta小你".$newage."岁";
                }elseif($self['Sex']=='2' && $selfage <= $age){
                    $fage=true;
                    $newage = $age-$selfage;
                    $isnianling = "Ta大你".$newage."岁";
                }else{
                    $fage=false;
                    $isnianling = "年龄相差太大了吧";
                }
            }

            if(!empty($self['City']) && !empty($list['City'])){
                if($self['City']==$list['City']){  //是否同城
                    $city=true;
                    $tc="同城";
                }else{
                    $city=false;
                    $tc="坚决反对异地恋";
                }
            }else{
                $city=true;
            }

            $zjm = $selfymd[1];
            $zjd = $selfymd[2];
            $zjdata = '2008-'.$zjm.'-'.$zjd;
            if(strtotime($zjdata) >=strtotime("2008-12-26") and strtotime($zjdata)<=strtotime("2009-1-2")){
                $YC = "魔羯座一";
            }else{
                $disval = District::where('birthday1',"<=",$zjdata)->where('birthday2',">=",$zjdata)->find();
                $YC = $disval['constellation'];
            }
            $dfm = $bymd[1];
            $dfd = $bymd[2];
            $dfdata = '2008-'.$dfm.'-'.$dfd;
            if(strtotime($dfdata) >=strtotime("2008-12-26") and strtotime($dfdata)<=strtotime("2009-1-2")){
                $NC = "魔羯座一";
            }else{
                $disval2 = District::where('birthday1',"<=",$dfdata)->where('birthday2',">=",$dfdata)->find();
                $NC = $disval2['constellation'];
            }
            $xingcon = Constellation::where("C_1='".$YC."' and C_2='".$NC."'")->whereOr("C_1='".$NC."' and C_2='".$YC."'")->find();

            $worst = $xingcon['worst']; //最遭情况

            switch($self['Wanna']){
                case '合适就行':
                    $bestfind = true;
                    $heshiweizhi = '最佳夫妻配';
                    break;

                case '异性朋友':
                    if(strpos($xingcon['best'],'朋友')!== false || strpos($xingcon['best'],'工作伙伴')!== false || strpos($xingcon['best'],'社交伙伴')!== false){
                        $bestfind = true;
                        $heshiweizhi = '最佳异性朋友';
                    }else{
                        $bestfind = false;
                        $heshiweizhi = '最差异性朋友';
                    }
                    break;
                case '情侣':
                    if(strpos($xingcon['best'],'情侣')!== false){
                        $bestfind = true;
                        $heshiweizhi = '最佳情侣';
                    }else{
                        $bestfind = false;
                        $heshiweizhi = '最差情侣';
                    }
                    break;
                case '夫妻':
                    if(strpos($xingcon['best'],'夫妻')!== false){
                        $bestfind = true;
                        $heshiweizhi = '最佳夫妻';
                    }else{
                        $bestfind = false;
                        $heshiweizhi = '最差夫妻';
                    }
                    break;
                case '同性朋友':
                    if(strpos($xingcon['best'],'朋友')!== false || strpos($xingcon['best'],'工作伙伴')!== false || strpos($xingcon['best'],'社交伙伴')!== false){
                        $bestfind = true;
                        $heshiweizhi = '最佳同性朋友';
                    }else{
                        $bestfind = false;
                        $heshiweizhi = '最差同性朋友';
                    }
                    break;
                case '同性恋人':
                    if(strpos($xingcon['best'],'情侣')!== false || strpos($xingcon['best'],'夫妻')!== false){
                        $bestfind = true;
                        $heshiweizhi = '最佳同性恋人';
                    }else{
                        $bestfind = false;
                        $heshiweizhi = '最差同性恋人';
                    }
                    break;
                default:
                    $bestfind = false;
                    $heshiweizhi = '最糟关系';
                    break;
            }


            if($city && $fage &&$bestfind){
                $tuijian = "认识一下";
                $result = "匹配数据";
                $content = "<table>
							<tr><td>性别：</td><td>{$istxyx}</td><td></td></tr>
							<tr><td>年龄：</td><td>{$isnianling}</td><td></td></tr>
							<tr><td>48星区：</td><td>{$heshiweizhi}，最糟{$worst}</td><td><a id='xingquc' href='xingqu/self/".$self['Birthday']."/list/".$list['Birthday']."'>点击查看</a></td></tr>
							</table>";
                $tjly = $heshiweizhi;
            }else{
                $tuijian = "备选观察";
                $result = "匹配数据";
                $content = "<table>
							<tr><td>性别：</td><td>{$istxyx}</td><td></td></tr>
							<tr><td>年龄：</td><td>{$isnianling}</td><td></td></tr>
							<tr><td>48星区：</td><td>{$heshiweizhi}</td><td><a id='xingquc' href='xingqu/self/".$self['Birthday']."/list/".$list['Birthday']."'>点击查看</a></td></tr>
							</table>";
                $tjly = $heshiweizhi;
            }

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

            return $this->fetch();
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

        $db = new Know();
        $lab_data=[
            'uid'=>$uid,
            'suid'=>$suid,
            'tjly'=>$tjly,
        ];
        $db ->save($lab_data);

        if($flag == 1){
            echo json_encode(['error_code'=>1,'url'=>'info/index','msg'=>'添加成功！您的资料还未填写完整，请填写完整再来寻找你的Ta吧！']);
        }else{
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