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
		
		//echo user::getLastSql();
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
		$suid = $self['suid'];
		
		$list=user::alias('a')
			->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header,b.*')
			->join('weixin b','b.id=a.wid')
			->where('b.openid',$bopenid)
			->find();
		$findval=mfind::where('uid',$list['nuid'])->find();
		//echo user::getLastSql();
		$suid = $self['suid'];	

		$lastdb=new User();
        $lastdata=[
            'Lasttime'=>time(),
        ];
        $lastdb->save($lastdata,['ID' => $suid]);
		
		$selfsex = (!empty($self['Sex']))?$self['Sex']:$self['wsex'];
		$listsex = (!empty($list['Sex']))?$list['Sex']:$list['wsex'];
		if(($selfsex=='1' && $listsex=='1') || ($selfsex=='2' && $listsex=='2')){    //判断是同性还是异性
			$istxyx = "同性";
		}else{
			$istxyx = "异性";
		}

		//echo user::getLastSql();
		//exit();

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
			if($self['Loveage']=='合适就行'){    //年龄
				$fage=true;
			}elseif($self['Loveage']=='小我0-3岁' && $selfage>=$age && $age>=$selfage-3){
				$fage=true;
			}elseif($self['Loveage']=='小我3岁以上' && $age<=$selfage-3){
				$fage=true;
			}elseif($self['Loveage']=='大我0-3岁' && $selfage<=$age && $age<=$selfage+3){
				$fage=true;
			}elseif($self['Loveage']=='大我3岁以上' && $age>=$selfage+3){
				$fage=true;
			}else{
				if($selfsex=='1' && $selfage > $age){    //判断是同性还是异性  男
					$fage=true;
				}elseif($selfsex=='2' && $selfage <= $age){
					$fage=true;
				}else{
					$fage=false;
					$isnianling = "年龄相差太大了吧";
				}
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
		
		if($fage && $bestfind){
				$tuijian = "认识一下";
				$result = "匹配数据";
				$content = "<table>
							<tr><td>性别：</td><td>{$istxyx}</td><td></td></tr>
							<tr><td>年龄：</td><td>{$isnianling}</td><td></td></tr>
							<tr><td>48星区：</td><td>{$heshiweizhi}</td><td><a id='xingquc' href='xingqu/self/".$self['Birthday']."/list/".$list['Birthday']."'>点击查看</a></td></tr>
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