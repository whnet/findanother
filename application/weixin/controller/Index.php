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

class Index extends Controller
{
    public function index()
    {
		$this->get_menu();     //菜单
		$this->show_winfo();   //消息信息
    }
	
	public function get_menu(){ 
		
		$options = Config::get('wechat');
 		$app = new Application($options);
		$menu = $app->menu;
		$buttons = [
			[
				"type" => "click",
				"name" => "瞧瞧身边",
				"key"  => "ERWEIMA"
			],
			[
				"type" => "view",
				"name" => "丰富资料",
				"url"  => "http://weixin.matchingbus.com/index.php/weixin/info/"
			],
			[
				"type" => "view",
				"name" => "个人中心",
				"url"  => "http://weixin.matchingbus.com/index.php/weixin/center/"
			],
		];
		$menu->add($buttons);
		$menus = $menu->current();
	}
	
	public function show_winfo(){
		
		$options = Config::get('wechat');
 		$app = new Application($options);
		$notice = $app->notice;
		
		$server = $app->server;
		$user = $app->user;
		
		
		$server->setMessageHandler(function($message) use ($user) {
			 
			 if($message->MsgType == 'event') {
				switch ($message->Event) {  
					case 'subscribe':
						$bopenid = $message->FromUserName;   //邀请人的openid 
						$message1 = "Hi 我是星数君，<a href=\"http://weixin.matchingbus.com/index.php/weixin/info/index\">点击这里</a>，填写资料，看看你和Ta是什么配！";
						$this->sendtxtmessage($message1,$bopenid);
						
						break;
					case 'SCAN':
						$yopenid = $message->EventKey;  //邀请人的openid
//                        Cookie::set('yopenid',$yopenid,3600*24);
						$bopenid = $message->FromUserName;  //被邀请人的openid
//                        Cookie::set('bopenid',$bopenid,3600*24);
						$yval = weixin::where('openid',$yopenid)->find();
						$aname = $yval['nickname'];
						$ydata = user::where('wid',$yval['id'])->find();
						
						$val = weixin::where('openid',$bopenid)->find();
						$data = user::where('wid',$val['id'])->find();
						$bname = $val['nickname'];
                        //先判断是否是自己，因为有一个break 注意判断顺序
                        if($yopenid == $bopenid){   //自己扫自己的二维码
                            $bmessage = "和自己无法匹配哦，将下方你的专属二维码，发送到朋友圈或发给那个Ta，看看谁是你的Mr/Ms Right！";
                            $this->sendtxtmessage($bmessage,$bopenid);

                            $options = Config::get('wechat');
                            $app = new Application($options);
                            $temporary = $app->material_temporary;
                            $path = $this->erweima($bopenid);
                            $data = $temporary->uploadImage($path);
                            @unlink($path);  //删除生成的二维码
                            $imgmessage = new Image(['media_id' => $data['media_id']]);
                            $this->sendtxtmessage($imgmessage,$bopenid);
                            break;
                        }else{     //扫邀请人的二维码
                            if(!$val){//判断扫描的人是否填写了资料
                                //
                                $message2 = "Hi 我是星数君，你还未填写资料，无法查看你们的关系，<a href='http://weixin.matchingbus.com/index.php/weixin/info/index?flag=1&&openid=".$yopenid."'>点击这里</a>，填写资料，看看你和Ta什么匹配！";
                                $this->sendtxtmessage($message2,$bopenid);
                                break;
                            }
                            $guanxi = $this->get_guanxi($yopenid,$bopenid);
                            $message = "您和".$aname."的关系是：".$guanxi."，<a href='http://weixin.matchingbus.com/index.php/weixin/gxpipei/index/yopenid/".$bopenid."/bopenid/".$yopenid."'>点击查看</a>";
                            $this->sendtxtmessage($message,$bopenid);


                            $guanxi2 = $this->get_guanxi($bopenid,$yopenid);
                            $bmessage = $bname."刚扫码成为你的好友，你和".$bname."的关系是：".$guanxi2."，<a href='http://weixin.matchingbus.com/index.php/weixin/gxpipei/index/yopenid/".$yopenid."/bopenid/".$bopenid."'>点击查看</a>";
                            $this->sendtxtmessage($bmessage,$yopenid);
                            break;
                        }
                        //后判断是否填写资料，因为有一个break 注意判断顺序
                        if(empty($data['Birthday'])){
                            //为空，让填写信息
                            $message2 = "Hi 我是星数君，<a href='http://weixin.matchingbus.com/index.php/weixin/info/index?flag=1&&openid=".$yopenid."'>点击这里</a>，填写资料，看看你和Ta什么匹配！";
                            $this->sendtxtmessage($message2,$bopenid);
                            break;
                        }elseif(!empty($data['Birthday'])){
						    //填完信息后才能弹窗
							$message2 = "Hi 我是星数君，将下方你的专属二维码，发送到朋友圈或发给那个Ta，看看谁是你的Mr/Ms Right！";
							$this->sendtxtmessage($message2,$bopenid);

							$options = Config::get('wechat');
							$app = new Application($options);
							$temporary = $app->material_temporary;
							$path = $this->erweima($bopenid);
							$data = $temporary->uploadImage($path);
							@unlink($path);  //删除生成的二维码
							$imgmessage = new Image(['media_id' => $data['media_id']]);
							$this->sendtxtmessage($imgmessage,$bopenid);

							break;
						}

					case 'CLICK':
					    //判断是否填写资料，没有则弹出界面
                        $openid = $message->FromUserName;
                        $val = weixin::where('openid',$openid)->find();
                        $data = user::where('wid',$val['id'])->find();
					    if(empty($data['Birthday'])){
                            $bopenid = $message->FromUserName;   //邀请人的openid
                            $message1 = "您的资料不完整，<a href=\"http://weixin.matchingbus.com/index.php/weixin/info/index\">点击这里</a>，填写资料，获取你的专属二维码,看看谁是你的Mr/Ms Right！";
                            $this->sendtxtmessage($message1,$bopenid);

                            break;
                        }else if($message->EventKey == 'ERWEIMA' && !empty($data['Birthday'])){
							$openid = $message->FromUserName;
							$message3 = "Hi，将下方你的专属二维码，发送到朋友圈或发给那个Ta,看看谁是你的Mr/Ms Right！";
							$this->sendtxtmessage($message3,$openid);
							
							$options = Config::get('wechat');
							$app = new Application($options);
							$temporary = $app->material_temporary;
							$path = $this->erweima($openid);
							$data = $temporary->uploadImage($path);
							@unlink($path);  //删除生成的二维码
							$imgmessage = new Image(['media_id' => $data['media_id']]);
							$this->sendtxtmessage($imgmessage,$openid);
						}
						break;
					default:
						break;
				}
			 }

		});

		$server->serve()->send();

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
			
			
			$selfsex = (!empty($self['Sex']))?$self['Sex']:$self['wsex'];
			$listsex = (!empty($list['Sex']))?$list['Sex']:$list['wsex'];
			
			
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
			
			if($fage && $bestfind){
				return $text;
			}else{
				return $text;
			}
		}
	}
	
	function erweima($bopenid=''){
		
		$weixinval = weixin::where('openid',$bopenid)->find();
		$nickname = $weixinval['nickname'];
		$headimg = $weixinval['headimgurl'];
		
		$options = Config::get('wechat');
		$app = new Application($options);
		$qrcode = $app->qrcode;
		$result = $qrcode->temporary($bopenid, 6 * 24 * 3600);
		$ticket = $result->ticket; 
		$markimgurl = $qrcode->url($ticket); // 二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片
		$mubiaoimg = 'uploads/ewm/code.jpeg';
		$content = file_get_contents($markimgurl); // 得到二进制图片内容
		file_put_contents($mubiaoimg, $content); // 写入文件

		
		$phone = true;
		$src = 'static/weixin/images/timg.jpg';
		$markimgurl = $this->myImageResize($mubiaoimg, '183', '183');   //缩放图片
		
		if($phone){
			$imgpath = $this->water_mark($src,$markimgurl,$phone);

/* 			$mubiaoimg = 'uploads/ewm/head.jpeg';
			$content = file_get_contents($headimg); // 得到二进制图片内容
			file_put_contents($mubiaoimg, $content); // 写入文件
			$markimgurl = $this->myImageResize($mubiaoimg, '85', '85');
			$imgg = $this->yuan_img($markimgurl);

			@unlink($markimgurl);		
			$imgss = $this->water_mark($imgpath,$imgg,true,2);
			@unlink($imgg);
			$tupian = $this->myImgString($imgss,$nickname,'268'); */
			
			//@unlink($mubiaoimg);  //删除生成的二维码
			return $imgpath;
		}else{
			header("Content-type: image/jpeg"); 
			$this->water_mark($src,$markimgurl,$phone);
			//@unlink($mubiaoimg);  //删除生成的二维码
		}
	}
	
	public function water_mark($src,$mark_img,$phone,$pct = 100)
    {
        if(function_exists('imagecopy') && function_exists('imagecopymerge')) {
            $data = getimagesize($src);
            if ($data[2] > 3)
            {
                return false;
            }
            $src_width = $data[0];
            $src_height = $data[1];
            $src_type = $data[2];
            $data = getimagesize($mark_img);
            $mark_width = $data[0];
            $mark_height = $data[1];
            $mark_type = $data[2];

            if ($src_width < ($mark_width + 20) || $src_width < ($mark_height + 20))
            {
                return false;
            }
            switch ($src_type)
            {
                case 1:
                $src_im = imagecreatefromgif($src);
                break;
                case 2:
                $src_im = imagecreatefromjpeg($src);
                break;
                case 3:
                $src_im = imagecreatefrompng($src);
                break;
            }
            switch ($mark_type)
            {
                case 1:
                $mark_im = imagecreatefromgif($mark_img);
                break;
                case 2:
                $mark_im = imagecreatefromjpeg($mark_img);
                break;
                case 3:
                $mark_im = imagecreatefrompng($mark_img);
                break;
            }
            $x = ($src_width - $mark_width - 10) / 2-65;    //水平位置
            $y = ($src_height - $mark_height - 10) / 2-10;    //垂直位置
			
            imageCopyMerge($src_im, $mark_im, $x, $y, 0, 0, $mark_width, $mark_height, $pct);
		   if($phone){
				$picname = MD5(time()).rand(1000,2000);
				imagejpeg($src_im, 'uploads/ewm/'.$picname.'.png');
				imagedestroy($src_im);			// 释放内存 
				return 'uploads/ewm/'.$picname.'.png';
		   }else{
				return imagejpeg($src_im);
		   }
            //return '/upload/ewm/'.$picname.'.png';
        }
    }

	function yuan_img($imgpath) {
		$ext     = pathinfo($imgpath);
		$src_img = null;
		switch ($ext['extension']) {
		case 'jpeg':
			$src_img = imagecreatefromjpeg($imgpath);
			break;
		case 'png':
			$src_img = imagecreatefrompng($imgpath);
			break;
		}
		$wh  = getimagesize($imgpath);
		$w   = $wh[0];
		$h   = $wh[1];
		$w   = min($w, $h);
		$h   = $w;
		$img = imagecreatetruecolor($w, $h);
		//这一句一定要有
		imagesavealpha($img, true);
		//拾取一个完全透明的颜色,最后一个参数127为全透明
		$bg = imagecolorallocatealpha($img, 231, 75, 138, 127);
		imagefill($img, 0, 0, $bg);
		$r   = $w / 2; //圆半径
		$y_x = $r; //圆心X坐标
		$y_y = $r; //圆心Y坐标
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				$rgbColor = imagecolorat($src_img, $x, $y);
				if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
					imagesetpixel($img, $x, $y, $rgbColor);
				}
			}
		}
		imagepng($img, 'uploads/ewm/yuanhead.png');
		imagedestroy($img);			// 释放内存 
		return 'uploads/ewm/yuanhead.png';
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
	
	function myImageResize($source_path, $target_width = 200, $target_height = 200, $fixed_orig = ''){
		$source_info = getimagesize($source_path);
		$source_width = $source_info[0];
		$source_height = $source_info[1];
		$source_mime = $source_info['mime'];
		$ratio_orig = $source_width / $source_height;
		if ($fixed_orig == 'width'){
			//宽度固定
			$target_height = $target_width / $ratio_orig;
		}elseif ($fixed_orig == 'height'){
			//高度固定
			$target_width = $target_height * $ratio_orig;
		}else{
			//最大宽或最大高
			if ($target_width / $target_height > $ratio_orig){
				$target_width = $target_height * $ratio_orig;
			}else{
				$target_height = $target_width / $ratio_orig;
			}
		}
		switch ($source_mime){
			case 'image/gif':
				$source_image = imagecreatefromgif($source_path);
				break;
			
			case 'image/jpeg':
				$source_image = imagecreatefromjpeg($source_path);
				break;
			
			case 'image/png':
				$source_image = imagecreatefrompng($source_path);
				break;
			
			default:
				return false;
				break;
		}
		
		$target_image = imagecreatetruecolor($target_width, $target_height);
		imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);
		//header('Content-type: image/jpeg');
		$imgArr = explode('.', $source_path);
		$target_path = $imgArr[0] . '_new.' . $imgArr[1];
		imagejpeg($target_image, $target_path, 100);
		imagedestroy($target_image);	
		return $target_path;
	}
	
	function sendtxtmessage($message,$openId){
		$options = Config::get('wechat');
		$app = new Application($options);
		$result = $app->staff->message($message)->to($openId)->send();
	}
}
