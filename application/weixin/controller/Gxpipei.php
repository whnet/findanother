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

class Gxpipei extends BaseController
{
	public function index(Request $request)
    {
        $num = $request->param('num');
        $num = !empty($num)?$num:0;
		$yopenid = $request->param('yopenid');
		$bopenid = $request->param('bopenid');

		if(empty($yopenid) || empty($bopenid)){
			$this->redirect('info/index');
		}
			
		$selfval=user::alias('a')
            ->field('a.ID as suid')
            ->join('weixin b','b.id=a.wid')
			->join('mfind c','c.uid=a.ID')
			->where('b.openid',$yopenid)
			->find();
		$valsuid = $selfval['suid'];
		if(empty($valsuid)){
			$self=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('b.openid',$yopenid)
				->find();
		}else{
			$self=user::alias('a')
				->field('a.*,a.ID as suid,b.*,c.*')
				->join('weixin b','b.id=a.wid')
				->join('mfind c','c.uid=a.ID')
				->where('b.openid',$yopenid)
				->find();
		}
		
		$list=user::alias('a')
			->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header,b.*')
			->join('weixin b','b.id=a.wid')
			->where('b.openid',$bopenid)
			->find();

        $selfsex = (!empty($self['Sex']))?$self['Sex']:$self['wsex'];
        $listsex = (!empty($list['Sex']))?$list['Sex']:$list['wsex'];
        if(($selfsex=='1' && $listsex=='1') || ($selfsex=='2' && $listsex=='2')){
            $istxyx = "同性";
        }else{
            $istxyx = "异性";
        }

        //三个文件相同的部分START
            $suid = $self['suid'];
            $lastdb=new User();
            $lastdata=[
                'Lasttime'=>time(),
            ];
            $lastdb->save($lastdata,['ID' => $suid]);
            require_once(dirname(dirname(__FILE__)).'/rules/match.php');
        return $this->fetch('combine/index');
        //三个文件相同的部分END
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
	
	
	public function tongjixue(Request $request){
		
		$bdata = $request->param('birthdata');
		
		if(empty($bdata)){
			$this->redirect('info/index');
		}
		$valbb = date('Y-n-j',$bdata);
		$val = explode('-',$valbb);
		$birth = $val[1]."/".$val[2];
		$list = birthday::where('birth',$birth)->find();
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
}