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
use app\weixin\model\Alternative;

class Detail extends BaseController
{
	public function index(Request $request)
    {
        //<!--type: 1-myfriend, 2-mylike, 3-likeme, 4-mysaw, 5-knowothers,6-hulve -->
        //<!--from: 1-myfriend, 2-mylike, 3-likeme, 4-mysaw, 5-knowothers, -->
		$suid = $request->param('suid'); //查看者的ID
		$type = !empty($request->param('type'))?$request->param('type'):0; //添加好友的方式 0从我想认识中加，1从我的备选中加
		$tjly = !empty($request->param('tjly'))?$request->param('tjly'):0;
		$id = !empty($request->param('id'))?$request->param('id'):0;
		$uid = !empty($request->param('uid'))?$request->param('uid'):input('uid'); //系统推荐过来用户的ID
		$jh = !empty($request->param('jh'))?$request->param('jh'):0;
		$from = !empty($request->param('from'))?$request->param('from'):0;
        $kid = !empty($request->param('kid'))?$request->param('kid'):0;
		//查看好友申请状态

        $frid = '';
        $sendStatus = '';
         if($type == 3){
             $fridDb = new Know();
             $knowFlagOne = $fridDb->where('uid',$suid)->where('suid',$uid)->find();
             if($knowFlagOne){
                 $frid = $knowFlagOne['flag'];
                 //别人想加我为好友
                 $sendStatus = 1;

             }
             $knowFlagTwo = $fridDb->where('uid',$uid)->where('suid',$suid)->find();

             if($knowFlagTwo){
                 $frid = $knowFlagTwo['flag'];
                 //我想加别人为好友
                 $sendStatus = 2;
             }

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
		$year=date("Y",time());
		$ymddd = date('Y-m-d',$list['Birthday']);
		$ymd = explode('-',$ymddd);
		$age=$year-$ymd[0];


		//补充匹配数据
        $tuijian = "";
        $result = "";
        $content = "";
        $tjly = '';
        $findval=mfind::where('uid',$list['nuid'])->find();
		//补充匹配数据
        //flagStatus = ?, 如果是type == 3 ，是从know表中来的，如果type == 4 从alernative

        $controller = $request->controller();
        $this->assign('controller', $controller);
		$this->assign('shuxing',$this->birthshuxing(date('Y-m-d',$list['Birthday'])));
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
		$this->assign('frid', $frid);
		$this->assign('from', $from);
		$this->assign('jh', $jh);
		$this->assign('flag', $flag);
        $this->assign('nuid', 0);
		$this->assign('sendStatus', $sendStatus);
		$this->assign('type', $type);
		$this->assign('tuijian', $tuijian);
		$this->assign('result', $result);
		$this->assign('content', $content);
		$this->assign('tjly', $tjly);
        $this->assign('fval',$findval);
        $this->assign('num',0);
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