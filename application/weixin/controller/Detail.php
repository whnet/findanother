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

class Detail extends BaseController
{
	public function index(Request $request)
    {
		$suid = $request->param('suid'); //推荐人的id
		$tjly = !empty($request->param('tjly'))?$request->param('tjly'):"0";
		
		$uid = !empty($request->param('uid'))?$request->param('uid'):input('uid'); //被推荐人的id 
		
		$jh = !empty($request->param('jh'))?$request->param('jh'):0;
		
		$flag2 = !empty($request->param('flag2'))?$request->param('flag2'):1;
		
		$frid = !empty($request->param('frid'))?$request->param('frid'):0;
		
		$kid = !empty($request->param('kid'))?$request->param('kid'):0;
		
		$list=user::alias('a')
			->field('a.*,b.nickname as name ,b.headimgurl as header')
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
		$this->assign('shuxing',$this->birthshuxing(date('Y-m-d',$list['Birthday'])));
		$this->assign('mfindval', $mfindval);
		$this->assign('list', $list);
		$this->assign('age', $age);
		$this->assign('intes', $intes);
		$this->assign('xc', $xiangce);
		$this->assign('suid', $suid);
		$this->assign('tjly', $tjly);
		$this->assign('uid', $uid);
		$this->assign('kid', $kid);
		$this->assign('frid', $frid);		
		$this->assign('jh', $jh);
		$this->assign('flag', $flag);
		$this->assign('flag2', $flag2);
		$this->assign('start',$this->birthext(date('Y-m-d',$list['Birthday'])));
		return $this->fetch();
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