<?php
namespace app\weixin\controller;

use think\Controller;
use think\Config;
use \think\Session;
use \think\Cookie;
use app\weixin\model\Weixin; 
use app\weixin\model\User; 
use app\weixin\model\Photos;
use app\weixin\model\Mfind; 
use app\weixin\model\Know; 
use app\weixin\model\Hulue; 
use app\weixin\model\Friends;
use app\weixin\model\Alternative;
use EasyWeChat\Foundation\Application;
use \think\exception\Handle;
use think\Request;

class Center extends BaseController
{
	public function index()
    {
		$openid = Cookie::get('openid');
		$list = weixin::where('openid',$openid)->find();
		
		$uinfo = user::where('wid',$list['id'])->find();
		
		$suid = $uinfo['ID'];
		
		$knum = know::where('flag','<>','3')->where('suid',$suid)->count();
		$hnum = hulue::where('flag','<>','3')->where('suid',$suid)->count();
		$fnum = friends::where('flag','<>','3')->where('uid',$suid)->count();
		$anum = alternative::where('flag','<>','3')->where('suid',$suid)->count();
		$xknum = know::where('flag','<>','3')->where('uid',$suid)->count();

		$this->assign('knum', $knum);
		$this->assign('hnum', $hnum);
		$this->assign('fnum', $fnum);
		$this->assign('anum', $anum);
		$this->assign('xknum', $xknum);
		$this->assign('list', $list);
		return $this->fetch();
        
    }
	
	public function mylike(){
		return $this->fetch();
	}
	
	public function myfriend(){

		return $this->fetch();
	}
	
	public function likeme(){
		return $this->fetch();
	}
	
	public function mysaw(){
		return $this->fetch();
	}
	
	public function sawme(){
		return $this->fetch();
	}
	
	public function hulue(){
		return $this->fetch();
	}
	
	public function mylikedel(){
		$id = input('homeuserid');
	 	$bool = new know();
		$lab_data=[
				'flag'=>'3',   //标记为删除
           ];

        $uid = $bool ->save($lab_data,['id' => $id]);
		
		if($uid){
			$msg = '删除成功！';
			$error_code = 0;
		}else{
			$msg = '删除失败！';
			$error_code = 1250;
		}
		echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
	}
	
 	public function huluedel(){
		$id = input('homeuserid');
	 	$bool = new hulue();
		$lab_data=[
				'flag'=>'3',   //标记为删除
           ];

        $uid = $bool ->save($lab_data,['id' => $id]);
		
		if($uid){
			$msg = '删除成功！';
			$error_code = 0;
		}else{
			$msg = '删除失败！';
			$error_code = 1250;
		}
		echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
	}
	
	public function myfrienddel(){
		$id = input('homeuserid');
	 	$bool = new friends();
		
		$lab_data=[
				'flag'=>'3',   //标记为删除
           ];

        $uid = $bool ->save($lab_data,['id' => $id]);
		
		if($uid){
			$msg = '删除成功！';
			$error_code = 0;
		}else{
			$msg = '删除失败！';
			$error_code = 1250;
		}
		echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
	}
	
/* 	public function likemedel(){
		$id = input('homeuserid');
	 	$bool = new know();
		
		$lab_data=[
				'flag'=>'3',   //标记为删除
           ];

        $uid = $bool ->save($lab_data,['id' => $id]);
		
		if($uid){
			$msg = '删除成功！';
			$error_code = 0;
		}else{
			$msg = '删除失败！';
			$error_code = 1250;
		}
		echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
	} */
	
	public function mysawdel(){
		$id = input('homeuserid');
	 	$bool = new Alternative();
		$lab_data=[
				'flag'=>'3',   //标记为删除
           ];

        $uid = $bool ->save($lab_data,['id' => $id]);
		
		if($uid){
			$msg = '删除成功！';
			$error_code = 0;
		}else{
			$msg = '删除失败！';
			$error_code = 1250;
		}
		echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
	}
	
/* 	public function sawmedel(){
		$id = input('homeuserid');
	 	$bool = Alternative::where('id',$id)->delete();
		if($bool){
			$msg = '删除成功！';
			$error_code = 0;
		}else{
			$msg = '删除失败！';
			$error_code = 1250;
		}
		echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
	} */
	
	public function ajaxmylike(){
		$page = input('page');
		$userinfo[]=array();
		$limit = $page.",10";
		$openid = Cookie::get('openid');
		$uval=weixin::alias('a')
			->join('user b','a.id=b.wid')
			->where('a.openid',$openid)
			->find();
			
		$data = know::where('suid',$uval['ID'])->where('flag','<>','3')->limit($limit)->select();

		if(!empty($data)){
			
			foreach($data as $k => $v){
				
				$uinfo=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$v['uid'])
				->find();
				
				$userinfo[$k]['id'] = $v['id'];
				$userinfo[$k]['nuid'] = $uinfo['suid'];
				$userinfo[$k]['Province'] = $uinfo['Province'];
				$userinfo[$k]['City'] = $uinfo['City'];
				$userinfo[$k]['height'] = $uinfo['height'];
				$userinfo[$k]['addtime'] = $v['create_at'];
				$userinfo[$k]['nickname'] = $uinfo['nickname'];
				$userinfo[$k]['headimgurl'] = $uinfo['headimgurl'];
				$userinfo[$k]['birthdayyear'] = date("Y",$uinfo['Birthday']);
				$userinfo[$k]['sex'] = $uinfo['Sex'];
				$userinfo[$k]['blood'] = $uinfo['Blood'];
				$userinfo[$k]['start'] = $this->birthext($uinfo['Birthday']);
				$userinfo[$k]['Sign'] = $uinfo['Sign'];
			}
			$msg = '成功';
			$error_code = 0;
		}else{
			$msg = '暂无数据';
			$error_code = 0;
			$userinfo='';
		}
		
		echo json_encode(['error_code'=>$error_code,'data'=>$userinfo,'msg'=>$msg]);
	}
	
	public function ajaxhulue(){
		$page = input('page');
		$userinfo[]=array();
		$limit = $page.",10";
		$openid = Cookie::get('openid');
		$uval=weixin::alias('a')
			->join('user b','a.id=b.wid')
			->where('a.openid',$openid)
			->find();
			
		$data = hulue::where('suid',$uval['ID'])->where('flag','<>','3')->limit($limit)->select();

		if(!empty($data)){
			
			foreach($data as $k => $v){
				
				$uinfo=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$v['uid'])
				->find();
				
				$userinfo[$k]['id'] = $v['id'];
				$userinfo[$k]['nuid'] = $uinfo['suid'];
				$userinfo[$k]['Province'] = $uinfo['Province'];
				$userinfo[$k]['City'] = $uinfo['City'];
				$userinfo[$k]['height'] = $uinfo['height'];
				$userinfo[$k]['addtime'] = $v['create_at'];
				$userinfo[$k]['nickname'] = $uinfo['nickname'];
				$userinfo[$k]['headimgurl'] = $uinfo['headimgurl'];
				$userinfo[$k]['birthdayyear'] = date("Y",$uinfo['Birthday']);
				$userinfo[$k]['sex'] = $uinfo['Sex'];
				$userinfo[$k]['blood'] = $uinfo['Blood'];
				$userinfo[$k]['start'] = $this->birthext($uinfo['Birthday']);
				$userinfo[$k]['Sign'] = $uinfo['Sign'];
			}
			$msg = '成功';
			$error_code = 0;
		}else{
			$msg = '暂无数据';
			$error_code = 0;
			$userinfo='';
		}
		
		echo json_encode(['error_code'=>$error_code,'data'=>$userinfo,'msg'=>$msg]);
	}
	
	public function ajaxlikeme(){
		$page = input('page');
		$userinfo[]=array();
		$limit = $page.",10";
		$openid = Cookie::get('openid');
		$uval=weixin::alias('a')
			->join('user b','a.id=b.wid')
			->where('a.openid',$openid)
			->find();
			
		$data = know::where('uid',$uval['ID'])->limit($limit)->select();

		if(!empty($data)){
			
			foreach($data as $k => $v){
				
				$uinfo=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$v['suid'])
				->find();
				
				$userinfo[$k]['id'] = $v['id'];
				$userinfo[$k]['nuid'] = $uinfo['suid'];
				$userinfo[$k]['Province'] = $uinfo['Province'];
				$userinfo[$k]['City'] = $uinfo['City'];
				$userinfo[$k]['height'] = $uinfo['height'];
				$userinfo[$k]['addtime'] = $v['create_at'];
				$userinfo[$k]['nickname'] = $uinfo['nickname'];
				$userinfo[$k]['headimgurl'] = $uinfo['headimgurl'];
				$userinfo[$k]['birthdayyear'] = date("Y",$uinfo['Birthday']);
				$userinfo[$k]['sex'] = $uinfo['Sex'];
				$userinfo[$k]['blood'] = $uinfo['Blood'];
				$userinfo[$k]['start'] = $this->birthext($uinfo['Birthday']);
				$userinfo[$k]['Sign'] = $uinfo['Sign'];
			}
			$msg = '成功';
			$error_code = 0;
		}else{
			$msg = '暂无数据';
			$error_code = 0;
			$userinfo='';
		}
		
		echo json_encode(['error_code'=>$error_code,'data'=>$userinfo,'msg'=>$msg]);
	}
	
	public function ajaxmysaw(){
		$page = input('page');
		$userinfo[]=array();
		$limit = $page.",10";
		$openid = Cookie::get('openid');
		$uval=weixin::alias('a')
			->join('user b','a.id=b.wid')
			->where('a.openid',$openid)
			->find();
			
		$data = Alternative::where('suid',$uval['ID'])->where('flag','<>','3')->limit($limit)->select();

		if(!empty($data)){
			
			foreach($data as $k => $v){
				
				$uinfo=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$v['uid'])
				->find();
				
				$userinfo[$k]['id'] = $v['id'];
				$userinfo[$k]['nuid'] = $uinfo['suid'];
				$userinfo[$k]['Province'] = $uinfo['Province'];
				$userinfo[$k]['City'] = $uinfo['City'];
				$userinfo[$k]['height'] = $uinfo['height'];
				$userinfo[$k]['addtime'] = $v['create_at'];
				$userinfo[$k]['nickname'] = $uinfo['nickname'];
				$userinfo[$k]['headimgurl'] = $uinfo['headimgurl'];
				$userinfo[$k]['birthdayyear'] = date("Y",$uinfo['Birthday']);
				$userinfo[$k]['sex'] = $uinfo['Sex'];
				$userinfo[$k]['blood'] = $uinfo['Blood'];
				$userinfo[$k]['start'] = $this->birthext($uinfo['Birthday']);
				$userinfo[$k]['Sign'] = $uinfo['Sign'];
			}
			$msg = '成功';
			$error_code = 0;
		}else{
			$msg = '暂无数据';
			$error_code = 0;
			$userinfo='';
		}
		
		echo json_encode(['error_code'=>$error_code,'data'=>$userinfo,'msg'=>$msg]);
	}
	
	public function ajaxsawme(){
		$page = input('page');
		$userinfo[]=array();
		$limit = $page.",10";
		$openid = Cookie::get('openid');
		$uval=weixin::alias('a')
			->join('user b','a.id=b.wid')
			->where('a.openid',$openid)
			->find();
			
		$data = Alternative::where('uid',$uval['ID'])->limit($limit)->select();

		if(!empty($data)){
			
			foreach($data as $k => $v){
				
				$uinfo=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$v['suid'])
				->find();
				
				$userinfo[$k]['id'] = $v['id'];
				$userinfo[$k]['nuid'] = $uinfo['suid'];
				$userinfo[$k]['Province'] = $uinfo['Province'];
				$userinfo[$k]['City'] = $uinfo['City'];
				$userinfo[$k]['height'] = $uinfo['height'];
				$userinfo[$k]['addtime'] = $v['create_at'];
				$userinfo[$k]['nickname'] = $uinfo['nickname'];
				$userinfo[$k]['headimgurl'] = $uinfo['headimgurl'];
				$userinfo[$k]['birthdayyear'] = date("Y",$uinfo['Birthday']);
				$userinfo[$k]['sex'] = $uinfo['Sex'];
				$userinfo[$k]['blood'] = $uinfo['Blood'];
				$userinfo[$k]['start'] = $this->birthext($uinfo['Birthday']);
				$userinfo[$k]['Sign'] = $uinfo['Sign'];
			}
			$msg = '成功';
			$error_code = 0;
		}else{
			$msg = '暂无数据';
			$error_code = 0;
			$userinfo='';
		}
		
		echo json_encode(['error_code'=>$error_code,'data'=>$userinfo,'msg'=>$msg]);
	}
	
	public function ajaxmyfriend(){
		$page = input('page');
		$userinfo[]=array();
		$limit = $page.",10";
		$openid = Cookie::get('openid');
		$uval=weixin::alias('a')
			->join('user b','a.id=b.wid')
			->where('a.openid',$openid)
			->find();
			
		$data = friends::where('uid',$uval['ID'])->where('flag','<>','3')->limit($limit)->select();

		if(!empty($data)){
			
			foreach($data as $k => $v){
				
				$uinfo=user::alias('a')
				->field('a.*,a.ID as suid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('a.ID',$v['fid'])
				->find();
				
				$userinfo[$k]['id'] = $v['id'];
				$userinfo[$k]['nuid'] = $uinfo['suid'];
				$userinfo[$k]['Province'] = $uinfo['Province'];
				$userinfo[$k]['City'] = $uinfo['City'];
				$userinfo[$k]['height'] = $uinfo['height'];
				$userinfo[$k]['addtime'] = $v['create_at'];
				$userinfo[$k]['nickname'] = $uinfo['nickname'];
				$userinfo[$k]['headimgurl'] = $uinfo['headimgurl'];
				$userinfo[$k]['birthdayyear'] = date("Y",$uinfo['Birthday']);
				$userinfo[$k]['sex'] = $uinfo['Sex'];
				$userinfo[$k]['blood'] = $uinfo['Blood'];
				$userinfo[$k]['start'] = $this->birthext($uinfo['Birthday']);
				$userinfo[$k]['Sign'] = $uinfo['Sign'];
			}
			$msg = '成功';
			$error_code = 0;
		}else{
			$msg = '暂无数据';
			$error_code = 0;
			$userinfo='';
		}
		
		echo json_encode(['error_code'=>$error_code,'data'=>$userinfo,'msg'=>$msg]);
	}
	
	public function album(){
		
		$openid = Cookie::get('openid');
		$uval=weixin::alias('a')
			->join('user b','a.id=b.wid')
			->where('a.openid',$openid)
			->find();
		if(empty($uval)){
			$flag = 1;
			$flag2 = 1;
			$url = "-index.php-weixin-info-birthday";
			$message = "请先填写你的出生年月日！";
		
			$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);

			exit();
		}else{
			$this->assign('uid', $uval['ID']);	
			return $this->fetch();
		}
	}
	
	public function albumget(){
		
		$openid = Cookie::get('openid');
		$uval=weixin::alias('a')
			->join('user b','a.id=b.wid')
			->where('a.openid',$openid)
			->find();
			
		$data = photos::where('uid',$uval['ID'])->select();
		if(!empty($data)){
			foreach($data as $key => $val){
				$data[$key]['id'] = $val['id'];  
				$data[$key]['photo'] = '/'.$val['photo'];  
			}
			$error_code = 0;
		}else{
			$error_code = 1;
			$data = '';
		}
		echo json_encode(['error_code'=>$error_code,'data'=>$data]);
	}
	
	public function edit(){
		
		$openid = Cookie::get('openid');
		$list=user::alias('a')
            ->field('a.*,a.ID as uid,b.*')
            ->join('weixin b','b.id=a.wid')
			->where('b.openid',$openid)
			->find();
		if(empty($list)){
			$flag = 1;
			$flag2 = 1;
			$url = "-index.php-weixin-info-birthday";
			$message = "请先填写你的出生年月日！";
		
			$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);

			exit();
		}
			
		$findval = mfind::field('start as fstart,boold as fboold,height as fheight')->where('uid',$list['uid'])->find();
			
		if(!empty($list['interest'])){
			$intest = explode(',',$list['interest']);
			$intt = array(
				'运动'=>'icon-sport',
				'音乐'=>'icon-music',
				'影视'=>'icon-film',
				'美食'=>'icon-food',
				'游戏'=>'icon-game',
				'户外'=>'icon-outdoor',
				'文学'=>'icon-literature',
				'艺术'=>'icon-art',
				'动漫'=>'icon-comic',
			);
			foreach($intest as $key => $val){
				$info[]=$intt[$val];
			}
		}
		$xiangce = photos::where('uid',$list['uid'])->select();
		$year = date("Y",time());
		$ymddd = date('Y-m-d',$list['Birthday']);
		$ymd = explode('-',$ymddd);
		$age=$year-$ymd[0];
		$this->assign('list', $list);
		$this->assign('findval', $findval);		
		$this->assign('info', $info);
		$this->assign('age', $age);
		$this->assign('xc', $xiangce);
		$this->assign('shuxing',$this->birthshuxing(date('Y-m-d',$list['Birthday'])));
		$this->assign('start',$this->birthext(date('Y-m-d',$list['Birthday'])));
		return $this->fetch();
	}
	
	public function albumdel(){
		$plist = input('photoidList');
		$data = explode(',',$plist);
		foreach($data as $key => $val){
			$bool = Photos::where('id',$val)->delete();
		}
		if($bool){
			$error_code = 0;
			$msg = '删除成功！';
		}else{
			$error_code = 1;
			$msg = '删除失败！';
		}
	
		echo json_encode(['error_code'=>$error_code,'msg'=>$msg]);
	}
	
	public function savepho(){
		if(request()->ispost()){
		    $file = request()->file('imgfile0');
            if($file){
                // 移动到框架应用根目录/uploads/ 目录下
                $info = $file->validate(['size'=>50000,'ext'=>'jpg,png,gif'])->move('uploads/photo/');
                if($info){
                    // 成功上传后 获取上传信息
                    $license_url=$info->getSaveName();
					$msg="成功";
					$error_code=0;
                }else{
                    // 上传失败获取错误信息
                    echo $file->getError();
					$msg="失败";
					$error_code=2104;
                }
			}
			$uid = input('uid');
			$db = new user();
			$data=[
				'flag'=>'3',
			];
 
           $db ->save($data,['id' => $uid]);
			
			if($error_code==0){
				$db = new Photos();
				 $lab_data=[
					'photo'=>'uploads/photo/'.$license_url,
					'uid'=>$uid,
				];
				$imgid = $db ->insertGetId($lab_data);
			}else{
				
				$imgid=0;
			}
		}
		
		$items=array(0=>array('photoid'=>$imgid,'uploadfiles'=>'/uploads/photo/'.$license_url,"thumbfiles"=>'/uploads/photo/'.$license_url));
		echo json_encode(['error_code'=>$error_code,'data'=>$items,'msg'=>$msg]);
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
	
	public function baseinfo(){
		
		if(request()->ispost()){
			$uid = input('uid');
		   $addr = explode("-",input("city"));
		   $db = new user();
           $lab_data=[
                'Sex'=>input("sex"),
                'Birthday'=>strtotime(input("birthday")),
				'Province'=>$addr[0],
				'City'=>$addr[1],
                'Gqzt'=> input("marry"),
			    'Blood'=>input("boold"),
                'zhiye'=>input("job"),
				'character'=>input("xg"),
				'height'=>input("height"),
                'weight'=> input("weight"),
				'wxnumber'=>input("wxnamber"),
				'Sign'=>input("qianming"),
           ];

			$values = user::where("ID",$uid)->find();
			if($values['bflag']==1 && $values['Birthday']!=strtotime(input("birthday"))){
				$lab_data['bflag']=2;
			}
          $db ->save($lab_data,['ID' => $uid]);
		  	$flag = 1;
			$flag2 = 1;
			$url = "-index.php-weixin-center-edit";
			$message = "修改资料成功！";
		
			$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);

			exit();
        }else{
			$openid = Cookie::get('openid');
			$base=user::alias('a')
				->field('a.*,a.ID as uid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('b.openid',$openid)
				->find();
			if($base['bflag']==2){
				$this->assign('flag', 2);
			}else{
				$this->assign('flag', 1);
			}
			$base['Birthday'] = date("Y-m-d",$base['Birthday']);
			$this->assign('uid', $base['uid']);
			$this->assign('base', $base);
			return $this->fetch();
		}
	}
	
	public function detailinfo(){
		
		if(request()->ispost()){
			$uid = input('uid');
		
		   $db = new user();
           $lab_data=[
				'education'=>input('education'),
                'salary'=>input("income"),
                'school'=>input("school"),
				'stuabroad'=>input("stuabroad"),
				'houst'=>input("houst"),
                'residence'=> input("residence"),
			    'hometown'=>input("hometown"),
           ];
 
           $db ->save($lab_data,['ID' => $uid]);
		   	$flag = 1;
			$flag2 = 1;
			$url = "-index.php-weixin-center-edit";
			$message = "修改资料成功！";
		
			$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);

		   exit();
        }else{
			$openid = Cookie::get('openid');
			$detail=user::alias('a')
				->field('a.*,a.ID as uid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('b.openid',$openid)
				->find();
			$this->assign('uid', $detail['uid']);
			$this->assign('detail', $detail);
			return $this->fetch();
		}
	}
	
	public function zheouinfo(){
		
		if(request()->ispost()){
			$uid = input('uid');
			$fid = input('fid');
			
			$db = new mfind();
			$data=[
				'start'=> input("start"),
				'boold'=>input("boold"),
				'height'=>input("height"),
			];

			$bool = $db ->save($data,['id' => $fid]);

			$dbu = new user();
			$udata=[
				'Wanna'=>input("find"),
				'Loveage'=>input("xiwang"),
			];			  
		
			$boolu = $dbu ->save($udata,['id' => $uid]);


			$flag = 1;
			$flag2 = 1;
			$url = "-index.php-weixin-center-edit";
			$message = "修改资料成功！";
		
			$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);

			exit();
			
		  
        }else{
			$openid = Cookie::get('openid');
			$detail=user::alias('a')
				->field('a.*,a.ID as uid,b.*,c.*,c.id as fid')
				->join('weixin b','b.id=a.wid')
				->join('mfind c','c.uid=a.ID')
				->where('b.openid',$openid)
				->find();
			$this->assign('uid', $detail['uid']);
			$this->assign('detail', $detail);
			return $this->fetch();
		}
	}
	
	
	public function interinfo(){
		  
		if(request()->ispost()){
		    
			$uid = input('uid');
			$db = new user();
			$data=[
				'interest'=>input('interest'),
			];
 
            $flag = $db ->save($data,['ID' => $uid]);
			if($flag){
				$error_code=0;
			}else{
				$error_code=1;
			}
			echo json_encode(['error_code'=>$error_code]);
		}else{
			$openid = Cookie::get('openid');
			$detail=user::alias('a')
				->field('a.*,a.ID as uid,b.*')
				->join('weixin b','b.id=a.wid')
				->where('b.openid',$openid)
				->find();
			if(!empty($detail['interest'])){
				$data = explode(',',$detail['interest']);
			}else{
				$data = '';
			}
			
			$this->assign('detail', $data);
			$this->assign('uid', $detail['uid']);
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