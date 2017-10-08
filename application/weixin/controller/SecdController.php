<?php
namespace app\weixin\controller;

use think\Controller;
use think\Config;
use \think\Session;
use \think\Cookie;
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

class SecdController extends Controller
{
	
	public function _initialize()
    {
		$options = Config::get('wechat');
		$app = new Application($options);
		$js = $app->js;
		$this->assign('js', $js);
		$this->assign('desc','【爱情巴士】全国第一家专业星座配对网站！性格配对准确率高达90%！10年专注于夫妻情侣的配对研究，注册会员超过1.5万华人。');
	}
	
	public function get_guanxi($yopenid,$bopenid){
		
		$selfval=user::alias('a')
            ->field('a.*,a.ID as suid,c.*')
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
		//echo user::getLastSql();
		
		$list=user::alias('a')
			->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header,b.*')
			->join('weixin b','b.id=a.wid')
			->where('b.openid',$bopenid)
			->find();

		//echo user::getLastSql();
		//exit();

		if(empty($list)){
			return "您的MR.RIGHT还没有出现！";  
		}else{

			$year=date("Y",time());
			
			$ymd = date('Y-m-d',$list['Birthday']);
			$bymd = explode('-',$ymd);
			$age=$year-$bymd[0];

			$bymd2 = date('Y-m-d',$self['Birthday']);
			$selfymd = explode('-',$bymd2);
			$selfage=$year-$selfymd[0];
			
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
					$text = '最佳夫妻';
					break;
			
				case '异性朋友':
					if(strpos($xingcon['best'],'朋友')!== false || strpos($xingcon['best'],'工作伙伴')!== false || strpos($xingcon['best'],'社交伙伴')!== false){
						$bestfind = true;
						$text = '最佳异性朋友';
					}else{
						$bestfind = false;
						$text = '最差异性朋友';
					}
					break;
				case '情侣':
					if(strpos($xingcon['best'],'情侣')!== false){
						$bestfind = true;
						$text = '最佳情侣';
					}else{
						$bestfind = false;
						$text = '最差情侣';
					}
					break;
				case '夫妻':
					if(strpos($xingcon['best'],'夫妻')!== false){
						$bestfind = true;
						$text = '最佳夫妻';
					}else{
						$bestfind = false;
						$text = '最差夫妻';
					}
					break;
				case '同性朋友':
					if(strpos($xingcon['best'],'朋友')!== false || strpos($xingcon['best'],'工作伙伴')!== false || strpos($xingcon['best'],'社交伙伴')!== false){
						$bestfind = true;
						$text = '最佳同性朋友';
					}else{
						$bestfind = false;
						$text = '最差同性朋友';
					}
					break;
				case '同性恋人':
					if(strpos($xingcon['best'],'情侣')!== false || strpos($xingcon['best'],'夫妻')!== false){
						$bestfind = true;
						$text = '最佳同性恋人';
					}else{
						$bestfind = false;
						$text = '最差同性恋人';
					}
					break;
				default:
					$bestfind = false;
					$text = '最糟关系';
					break;
			}
			
			return $text;
		}
	}
	

	

	
	

	

	
	function myImgString($bigImgPath,$content,$top){
		
		
		$data = getimagesize($bigImgPath);

        $src_width = $data[0];

        $dsc_width = mb_strlen($content);
		
		$img = imagecreatefromstring(file_get_contents($bigImgPath));
	 
		$font = 'static/weixin/fonts/msyh.ttf';//字体
		$black = imagecolorallocate($img, 255, 255, 255);//字体颜色 RGB
		
		$fontSize = 15;   //字体大小
		$circleSize = 0; //旋转角度
		$left = $src_width / 2-$dsc_width*10;    //水平位置
		$top = $top;
	 
		imagefttext($img, $fontSize, $circleSize, $left, $top, $black, $font, $content);
	 
		$picname = MD5(time()).rand(1000,2000);
		imagejpeg($img, 'uploads/ewm/'.$picname.'.png');
		imagedestroy($img);			// 释放内存 
		return 'uploads/ewm/'.$picname.'.png';
	}
	
	function sendtxtmessage($message,$openId){
			$options = Config::get('wechat');
			$app = new Application($options);
			$result = $app->staff->message($message)->to($openId)->send();
	}
	
}