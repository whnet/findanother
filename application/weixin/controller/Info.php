<?php
namespace app\weixin\controller;

use think\Controller;
use think\Config;
use \think\Session;
use \think\Cookie;
use \think\View;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Image;
use \think\exception\Handle;
use think\Request;
use app\weixin\model\Mfind; 
use app\weixin\model\Weixin; 
use app\weixin\model\User; 
use EasyWeChat\Message\Text;
use app\weixin\model\Know; 
use app\weixin\model\Birthday; 
use app\weixin\model\Alternative; 
use app\weixin\model\District; 
use app\weixin\model\Constellation; 
use app\weixin\model\Blood; 
use app\weixin\model\Photos; 
use app\weixin\model\Friends;


class Info extends SecdController
{
	public function index()
    {
		if(input('flag')){
			$yaoqingopenid = !empty(input('openid'))?input('openid'):'';
			Cookie::set('yaoqingopenid',$yaoqingopenid,360000);
		}
		$options = Config::get('wechat');
		$app = new Application($options);
		
		$oauth = $app->oauth;
		if(!Cookie::has('wechat_user')) {

		  session('target_url','info/save_uinfo');
		  $oauth->redirect()->send();
		}else{                              // 已经关注过
			$user = Cookie::get('wechat_user');
			$open_id = $user['original']['openid'];

			//查询数据库中用户的账号的openid中是否有值，有值说明用户的微信与账号绑定
			$result = Weixin::check_login($open_id);

			if(!empty($result['openid'])){
				
				Cookie::set('openid',$result['openid'],2419200);
				$data = User::where('wid',$result['id'])->find();
				if(empty($data)){
					
					$this->redirect('birthday');
				}else{
					$uid=$data['ID'];
					if(empty($data['flag'])){
						$this->redirect('register',['id'=>$uid]);
					}elseif($data['flag']==1){
						$this->redirect('next',['id'=>$uid]);
					}elseif($data['flag']==2){
						$this->redirect('interest',['id'=>$uid]);
					}elseif($data['flag']==3){
						$this->redirect('mfind',['id'=>$uid]);
					}elseif($data['flag']==4){
						$this->redirect('avatar',['id'=>$uid]);
					}elseif($data['flag']==5){
						$this->panduan_find($uid);
					}
				}

			}else{ 
				Cookie::delete('wechat_user');
				$this->redirect('index');
			}
		}
        
    }
	
	public function birthday(){
		
		if(request()->ispost()){
			$id = Cookie::get('openid');
			$val = Weixin::where('openid',$id)->find();
		
		   $db = new user();
           $lab_data=[
				'wid'=>$val['id'],
				'Sex'=>$val['wsex'],
				'Wanna'=>input("find"),
                'Birthday'=>strtotime(input("birthday")),
				'Regtime'=>time(),
           ];

           $uid = $db ->insertGetId($lab_data);

		   	$yopenid = !empty(Cookie::get('yaoqingopenid'))?Cookie::get('yaoqingopenid'):'';
			if(!empty($yopenid)){
				
			   $yval = weixin::where('openid',$yopenid)->find();
			   $name = $yval['nickname'];
			   $bname = $val['nickname'];
			   $yuinfo = user::where('wid',$yval['id'])->find();

				$fdb = new friends();
				
				$ylab_fdata=[
						'uid'=>$yuinfo['ID'],
						'fid'=>$uid,
						'flag'=>2,
						'create_at'=>time(),
				];
				
				$fdb ->save($ylab_fdata);

			   $guanxi = $this->get_guanxi($id,$yopenid); 
				
				$message = "您和".$name."的关系是：".$guanxi."，<a href=\"http://weixin.matchingbus.com/index.php/weixin/gxpipei/index/yopenid/".$id."/bopenid/".$yopenid."\">点击查看</a>";
				$this->sendtxtmessage($message,$id);
			}
			$this->redirect('Ppbirthday/index',['id'=>$uid]);
        }else{
			return $this->fetch();
		}
		
	}
	
	public function register($id=''){
		$id = input('id');
		if(request()->ispost()){
			$id = input('id');
			$openid = Cookie::get('openid');
			$val = Weixin::field('id')->where('openid',$openid)->find();
		
		   $addr = explode("-",input("city"));
		   $db = new user();
           $lab_data=[
                'Sex'=>input("sex"),
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
				'Mark'=>'20',
				'flag'=>'1',
           ];

           $uid = $db ->save($lab_data,['ID' => $id]);
		   $this->redirect('next',['id'=>$id]);
        }else{
			if(empty($id)){
				$flag = 1;
				$flag2 = 1;
				$url = "-index.php-weixin-info-birthday";
				$message = "请先填写你的出生年月日！";
		
				$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);
				exit();
			}else{
				$this->assign('id',$id);
				return $this->fetch();
			}
		}
	}
	
	
	public function next($id=''){
		
		if(request()->ispost()){
		   $id = input('id');
		   $db = new user();
           $lab_data=[
				'education'=>input('education'),
                'salary'=>input("income"),
                'school'=>input("school"),
				'stuabroad'=>input("stuabroad"),
				'houst'=>input("houst"),
                'residence'=> input("residence"),
			    'hometown'=>input("hometown"),
				'flag'=>'2',
           ];
 
           $db ->save($lab_data,['ID' => $id]);
           $this->redirect('interest',['id'=>$id]);
        }else{
			$this->assign('id',$id);
			return $this->fetch();
		}
		
	}
	
	public function avatar($id=''){
		
		$this->assign('id',$id);
		return $this->fetch();
	}
	
	public function savepho(){

		    $file = request()->file('imgfile0');
            if($file){
                // 移动到框架应用根目录/uploads/ 目录下
                $info = $file->validate(['size'=>51000,'ext'=>'jpg,png,gif'])->move('uploads/photo/');
                if($info){
                    // 成功上传后 获取上传信息
                    $license_url=$info->getSaveName();
					$msg="成功";
					$error_code=0;
                }else{
                    // 上传失败获取错误信息
					$msg=$file->getError();
					$error_code=2104;
					$license_url='';
                }
			}
			$uid = input('uid');
			
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

		   $udb = new user();
           $ulab_data=[
				'flag'=>'5',
           ];
			$udb ->save($ulab_data,['ID' => $uid]);
		
		
		$items=array(0=>array('photoid'=>$imgid,'uploadfiles'=>'/uploads/photo/'.$license_url,"thumbfiles"=>'/uploads/photo/'.$license_url));
		echo json_encode(['error_code'=>$error_code,'data'=>$items,'msg'=>$msg]);
	}
	
	public function interest(Request $request){
		$uid = $request->param('id');
		$this->assign('uid',$uid);
		return $this->fetch();
	}
	
	public function save_inter(){
		if(request()->ispost()){
		    
			$uid = input('id');
			$db = new user();
			$data=[
				'interest'=>input('interest'),
				'flag'=>'3',
			];
 
            $flagq = $db ->save($data,['ID' => $uid]);
			if($flagq){
				$error_code=0;
			}else{
				$error_code=1;
			}
			
		}
		echo json_encode(['error_code'=>$error_code]);
		
	}
	
	public function mfind($id=''){
		$id = input('id');
		if(request()->ispost()){
		   $db = new mfind();
		   $lab_data=[
				'uid'=>$id,
				'start'=> input("start"),
				'boold'=>input("boold"),
				'height'=>input("height"),
				
		   ];

		   $bool = $db ->save($lab_data);
		   
		   $dbu = new user();
		   $data=[
				'Loveage'=>input("xiwang"),
				'flag'=>'4',
		   ];			  
		  
			$boolu = $dbu ->save($data,['id' => $id]);
			$this->redirect('avatar',['id'=>$id]);

		}else{
			$this->assign('id',$id);
			return $this->fetch();
		}
		
	}
	
	
	
	public function panduan_find($uid){
		$uid = empty($uid)?input('uid'):$uid;
		$data = Mfind::where('uid',$uid)->find();
		if(!empty($data)){
			
			$flag = 1;
			$flag2 = 1;
			$url = "-index.php-weixin-pipei-index";
			$message = "您已经填写了个人信息，正在为你跳转到匹配页";
		
			$this->redirect('zhezhao',['flag'=>$flag,'flag2'=>$flag2,'url'=>$url,'message'=>$message]);
		}else{
			$this->redirect('info/index');
		}
	}
	
	
	public function save_uinfo(){
		
		$user = Cookie::get('wechat_user');
		$openid = $user['original']['openid'];
		$val = weixin::where('openid',$openid)->find();
		if(empty($val)){
			$db = new Weixin();
			$sex = !empty($user['original']['sex'])?$user['original']['sex']:'1';
			$lab_data=[
					'nickname'=>$user['original']['nickname'],
					'openid'=>$user['original']['openid'],
					'headimgurl'=> $user['original']['headimgurl'],
					'wsex'=>$sex, 
					'wprovince'=> $user['original']['province'],				
					'wcountry'=>$user['original']['country'],
			   ];
	 
			$db ->save($lab_data);
			$this->redirect('index');
		}else{
			Cookie::set('openid',$openid,2419200);
			$this->redirect('index');
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