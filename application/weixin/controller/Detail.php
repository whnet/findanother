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
use app\weixin\model\Photos;
use app\weixin\model\Know;
use app\weixin\model\Hulue;
use app\weixin\model\Alternative;
use app\weixin\model\Constellation;

class Detail extends BaseController
{
	public function index(Request $request)
    {

        $type = !empty($request->param('type'))?$request->param('type'):0; //添加好友的方式 0从我想认识中加，1从我的备选中加
        $tjly = !empty($request->param('tjly'))?$request->param('tjly'):0;
        $id = !empty($request->param('id'))?$request->param('id'):0;
        $uid = !empty($request->param('uid'))?$request->param('uid'):input('uid'); //系统推荐过来用户的ID
        $suid = $request->param('suid'); //查看者的ID
        $jh = !empty($request->param('jh'))?$request->param('jh'):0;
        $froms = !empty($request->param('froms'))?$request->param('froms'):0;
        $kid = !empty($request->param('kid'))?$request->param('kid'):0;

        $openid = Cookie::get('openid');//邀请
        $yopenid = Cookie::get('byaoqingopenid');//被邀请
        $list = weixin::where('openid',$openid)->find();

        $uinfo = user::where('wid',$list['id'])->find();//查看者的信息
        if( ($suid == $uinfo['ID'] && $froms == 'fromWechatToAgreed') || ($froms == 'mysaw' && $uid == $uinfo['ID'])){//避免自己匹配自己,暂时屏蔽
            //$this->redirect('center/index');
        }
        $self = user::where('wid',$list['id'])->find();//查看者的信息

        $suid = $uinfo['ID'];//我的id


        if(!$suid || !$openid){
            $this->redirect('info/birthday');
        }
        //我想认识中根据访问者ID  将表中uid超过addtime七天的flag=1 改变成flag=0,
        if(!empty($suid)){
            //查看know中的时间
            $SevenTime = 3600*24*7;
            $afterDays = know::where('uid',$suid)->select();
            $findDatas = [];
            foreach($afterDays as $k=>$v){
                if( (time() - $v['addtime']) > $SevenTime ){
                    $findDatas[$v['id']]['id'] = $v['id'];
                    $findDatas[$v['id']]['flag'] = 0;
                }
            }

            $knowDeadTime = new Know;
            $knowDeadTime->saveAll($findDatas);


        }

        //获取当前的url 写入session
        //<!--type: 1-myfriend, 2-mylike, 3-likeme, 4-mysaw, 5-knowothers,6-hulve -->
        //<!--froms: 1-myfriend, 2-mylike, 3-likeme, 4-mysaw, 5-knowothers, -->
        $request = Request::instance();
        $url = $request->url();
        Session::set('preurl',$url);




        //如果$uid 或 $suid 的信息不存在 就跳转到会员中心
            $uidInfo = user::where('ID',$uid)->find();
            if(!$uidInfo){
                $this->redirect('center/index');
            }
            $suidInfo = user::where('ID',$suid)->find();
            if(!$suidInfo){
                $this->redirect('center/index');
            }






        $list=user::alias('a')
            ->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header,b.*')
            ->join('weixin b','b.id=a.wid')
            ->where('a.ID',$uid)
            ->find();

			
		$mfindval = mfind::where('uid',$list['ID'])->find();

		$xiangce = photos::where('uid',$uid)->select();
		
		if(!empty($xiangce)){
			$flag =1;
		}else{
			$flag =2;
		}

		$intes = explode(",",$list['interest']);

        //判断性别
        if($uinfo['Sex'] == $list['Sex']){
            $sex = '同性';
            $istxyx = '同性';
            $xingbie = false;
        }else{
            $sex = '异性';
            $istxyx = '异性';
            $xingbie = true;
        }
        $num = $request->param('num');
        $num = !empty($num)?$num:0;
        require_once(dirname(dirname(__FILE__)).'/rules/match.php');

        //查看好友申请状态
        $frid = '';
        $sendStatus = '';
        if($type == 3 || $type == 2 || $type == 1){

            $fridDb = new Know();
            $knowFlag = $fridDb->where(['uid'=>$suid,'suid'=>$uid])->whereOr(['uid'=>$uid,'suid'=>$suid])->find();
            if($knowFlag){
                $frid = $knowFlag['flag'];

            }else{
                $alertFlag = Alternative::where(['uid'=>$suid,'suid'=>$uid])->whereOr(['uid'=>$uid,'suid'=>$suid])->find();
                if($alertFlag){
                    $frid = $alertFlag['flag'];
                }
            }



        }elseif($type == 4){
            $fridDb = new Alternative();
            $knowFlagOne = $fridDb->where('uid',$suid)->where('suid',$uid)->find();
            if($knowFlagOne){
                $frid = $knowFlagOne['flag'];
                if($frid == 1){
                    $frid = 11;
                }
            }

            $knowFlagTwo = $fridDb->where('uid',$uid)->where('suid',$suid)->find();
            if($knowFlagTwo){
                $frid = $knowFlagTwo['flag'];
                if($frid == 1){
                    $frid = 12;
                }

            }
        }elseif($type == 5){//从相互认识中进来
            $knowothersData = Know::where(['uid'=>$uid,'suid'=>$suid])->whereOr(['suid'=>$uid,'uid'=>$suid])->find();
            $frid = $knowothersData['flag'];
           if(!$knowothersData){
               $AlertData = Alternative::where(['uid'=>$uid,'suid'=>$suid])->whereOr(['suid'=>$uid,'uid'=>$suid])->find();
               $frid = $AlertData['flag'];
           }else{
               $frid = 0;
           }
        }


        $findval=mfind::where('uid',$list['nuid'])->find();
		//补充匹配数据
        //flagStatus = ?, 如果是type == 3 ，是从know表中来的，如果type == 4 从alernative

        //判断是否已添加到忽略列表
        $isHulve = Hulue::where('suid',$uid)->where('uid',$suid)->count();
        //判断是否已添加到忽略列表END
        $controller = $request->controller();
        $this->assign('controller', $controller);
		$this->assign('shuxing',$this->birthshuxing(date('Y-m-d',$list['Birthday'])));

		$this->assign('isHulve', $isHulve);
		$this->assign('mfindval', $mfindval);
		$this->assign('list', $list);
		$this->assign('age', $age);
		$this->assign('intes', $intes);
		$this->assign('xc', $xiangce);
		$this->assign('id', $id);
		$this->assign('suid', $suid);
		$this->assign('tjly', $tjly);
		$this->assign('uid', $uid);
		$this->assign('kid', $kid);
		$this->assign('froms', $froms);
		$this->assign('jh', $jh);
		$this->assign('flag', $flag);
        $this->assign('nuid', 0);
		$this->assign('type', $type);
		$this->assign('tuijian', $tuijian);
		$this->assign('result', $result);
		$this->assign('content', $content);
		$this->assign('tjly', $tjly);
        $this->assign('fval',$findval);
        $this->assign('num',0);
        $this->assign('frid',$frid);
		$this->assign('start',$this->birthext(date('Y-m-d',$list['Birthday'])));
		return $this->fetch('combine/index');
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
		
		return $xzdict[$i].'座'; 
	}

}