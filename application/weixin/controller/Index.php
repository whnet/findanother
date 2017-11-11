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
use app\weixin\model\Saoma;
use app\weixin\model\Friends;

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
						$bopenid = $message->FromUserName;  //被邀请人的openid
                        //将扫码信息写到数据库中


						$yval = weixin::where('openid',$yopenid)->find();
						$aname = $yval['nickname'];
						$ydata = user::where('wid',$yval['id'])->find();
						
						$val = weixin::where('openid',$bopenid)->find();
                        $bname = $val['nickname'];
						$data = user::where('wid',$val['id'])->find();

						//将扫码的记录存到数据表中，
                        //先判断是否是自己，因为有一个break 注意判断顺序
                        if($yopenid == $bopenid){   //自己扫自己的二维码
                            $bmessage = "和自己无法匹配哦，将下方你的专属二维码，发送到朋友圈或发给那个Ta，看看谁是你的Mr/Ms Right！";
                            $this->sendtxtmessage($bmessage,$bopenid);

                            $options = Config::get('wechat');
                            $app = new Application($options);
                            $temporary = $app->material_temporary;
                            $path = $this->qrCode($bopenid);
                            $data = $temporary->uploadImage($path);
                            @unlink($path);  //删除生成的二维码
                            $imgmessage = new Image(['media_id' => $data['media_id']]);
                            $this->sendtxtmessage($imgmessage,$bopenid);
                            break;
                        }else{//扫邀请人的二维码

                            if(!$data){
                                $message2 = "Hi 我是星数君，你还未填写资料，无法查看你们的关系，<a href='http://weixin.matchingbus.com/index.php/weixin/info/index?flag=1&bopenid=".$bopenid."&openid=".$yopenid."'>点击这里</a>，填写资料，看看你和Ta什么匹配！";
                                $this->sendtxtmessage($message2,$bopenid);
                                break;
                            }
                            //查询是否匹配过
                            $map['yaoqingopenid'] = $yopenid;
                            $map['beiyaoqingopenid'] = $bopenid;
                            $map['status'] = 1;
                            $saoma = saoma::where($map)->find();
                            if(!$saoma){
                                $db = new saoma();
                                $lab_data=[
                                    'yaoqingopenid'=>$yopenid,
                                    'beiyaoqingopenid'=>$bopenid,
                                    'status'=>1,
                                    'created'=>time(),
                                ];
                                $db ->save($lab_data);
                            }
                            //扫码就将扫码者的信息写入到数据know表中
                            $yval = weixin::where('openid',$yopenid)->find();
                            $yuinfo = user::where('wid',$yval['id'])->find();
                            //被邀请者
                            $bval = weixin::where('openid',$bopenid)->find();
                            $buinfo = user::where('wid',$bval['id'])->find();

                            $uid = $yuinfo['ID'];
                            $fid = $buinfo['ID'];
                            $friendDb = new Friends();
                            $friendExist = $friendDb->where('uid',$uid)->where('fid',$fid)->count();
                            if(!$friendExist){//扫我码的不存在记录, 就添加
                                $fdb = new friends();
                                $ylab_fdata=[
                                    'uid'=>$uid,
                                    'fid'=>$fid,
                                    'flag'=>0,
                                    'create_at'=>time(),
                                ];

                                $fdb ->save($ylab_fdata);
                            }
                            //通过扫码查看与其他人的关系
                            $myYmd =date('m-d',$yuinfo['Birthday']);
                            $myData = '2008-'.$myYmd;
                            $myConstellation = $this->getConstellation($myData);

                            $otherYmd = date('m-d',$buinfo['Birthday']);
                            $otherData = '2008-'.$otherYmd;
                            $otherConstellation = $this->getConstellation($otherData);
                            //查出他们的最佳为朋友、夫妻、情侣的数据
                            $Constellation = Constellation::where("C_1='".$myConstellation."' and C_2='".$otherConstellation."'")->whereOr("C_1='".$otherConstellation."' and C_2='".$myConstellation."'")->find();
                            $best = '最佳'.$Constellation['best'];
                            $worst = '最糟'.$Constellation['worst'];
                            //通过生日获得两人的关系 END
                            //$guanxi = $this->get_guanxi($yopenid,$bopenid);
                            $message = "您和".$aname."的关系是：".$best."<a href='http://weixin.matchingbus.com/index.php/weixin/gxpipei/index/froms/gxpipei/yopenid/".$bopenid."/bopenid/".$yopenid."'>点击查看</a>";
                            $this->sendtxtmessage($message,$bopenid);


                            //避免多次扫码给对方产生影响
//                            if(!$data) {
                                $bmessage = $bname . "刚扫码成为你的好友，你和" . $bname . "的关系是：" . $best . "<a href='http://weixin.matchingbus.com/index.php/weixin/gxpipei/index/froms/gxpipei/yopenid/" . $yopenid . "/bopenid/" . $bopenid . "'>点击查看</a>";
                                $this->sendtxtmessage($bmessage, $yopenid);
//                            }
                            break;
                        }
                        //后判断是否填写资料，因为有一个break 注意判断顺序
                        if(empty($data['Birthday'])){
                            //为空，让填写信息
                            $message2 = "Hi 我是星数君，<a href='http://weixin.matchingbus.com/index.php/weixin/info/index?flag=1&bopenid=".$bopenid."&openid=".$yopenid."'>点击这里</a>，填写资料，看看你和Ta什么匹配！";
                            $this->sendtxtmessage($message2,$bopenid);
                            break;
                        }elseif(!empty($data['Birthday'])){
						    //填完信息后才能弹窗
							$message2 = "Hi 我是星数君，将下方你的专属二维码，发送到朋友圈或发给那个Ta，看看谁是你的Mr/Ms Right！";
							$this->sendtxtmessage($message2,$bopenid);

							$options = Config::get('wechat');
							$app = new Application($options);
							$temporary = $app->material_temporary;
							$path = $this->qrCode($bopenid);
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
                            $message1 = "您的资料不完整，<a href=\"http://weixin.matchingbus.com/index.php/weixin/info/index\">点击这里</a>，填写资料，获取你的专属二维码，看看谁是你的Mr/Ms Right！";
                            $this->sendtxtmessage($message1,$bopenid);
                            break;
                        }else if($message->EventKey == 'ERWEIMA' && !empty($data['Birthday'])){
							$openid = $message->FromUserName;
							$message3 = "Hi，将下方你的专属二维码，发送到朋友圈或发给那个Ta，看看谁是你的Mr/Ms Right！";
							$this->sendtxtmessage($message3,$openid);
							
							$options = Config::get('wechat');
							$app = new Application($options);
							$temporary = $app->material_temporary;
							$path = $this->qrCode($openid);
							$data = $temporary->uploadImage($path);
                            //@unlink($path);  //删除生成的二维码
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

            $zjdata = '2008-'.$selfymd[1].'-'.$selfymd[2];
            $YC = $this->getConstellation($zjdata);
            $dfdata = '2008-'.$bymd[1].'-'.$bymd[2];
            $NC = $this->getConstellation($dfdata);
			$xingcon = Constellation::where("C_1='".$YC."' and C_2='".$NC."'")->whereOr("C_1='".$NC."' and C_2='".$YC."'")->find();
            $data = $this->match_others($xingcon['best'], $self['Wanna']);
            $heshiweizhi = $data[0];
            $bestfind = $data[1];
			
			if($fage && $bestfind){
				return $text;
			}else{
				return $text;
			}
		}
	}

	function qrCode($bopenid=''){
		
		$weixinval = weixin::where('openid',$bopenid)->find();
		$nickname = $weixinval['nickname'];
		$headimg = $weixinval['headimgurl'];

		$options = Config::get('wechat');
		$app = new Application($options);
		$qrcode = $app->qrcode;
		$result = $qrcode->temporary($bopenid, 6 * 24 * 3600);
		$ticket = $result->ticket;
       // 二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片
		$markimgurl = $qrcode->url($ticket);
		$mubiaoimg = 'uploads/qrcode/'.$bopenid.'.jpeg';
		$content = file_get_contents($markimgurl);
		file_put_contents($mubiaoimg, $content);

		$phone = $bopenid;
		$src = 'uploads/headerAndbackground/'.$bopenid.'.png';//获取已经合成了的背景图片
		$markimgurl = $this->myImageResize($mubiaoimg, '280', '280');   //将二维码进行缩放

		if($phone){
			$imgpath = $this->waterMarkFinal($src,$markimgurl,$phone);
			return $imgpath;
		}else{
			header("Content-type: image/jpeg");
			$this->waterMarkFinal($src,$markimgurl,$phone);
		}
	}


	public function waterMarkFinal($src,$mark_img,$phone,$pct = 100)
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
            $x = 250;    //水平位置
            $y = 750;    //垂直位置

            imageCopyMerge($src_im, $mark_im, $x, $y, 0, 0, $mark_width, $mark_height, $pct);
		   if($phone){
				$picname = $phone;
				imagejpeg($src_im, 'uploads/qrcode/'.$picname.'.png');
				imagedestroy($src_im);			// 释放内存
				return 'uploads/qrcode/'.$picname.'.png';
		   }else{
				return imagejpeg($src_im);
		   }

        }
    }


	/*
	 * 添加字体
	 */
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
		imagejpeg($img, 'uploads/qrcode/'.$picname.'.png');
		imagedestroy($img);			// 释放内存
		return 'uploads/qrcode/'.$picname.'.png';
	}
	/*
	 * 缩小图片
	 */
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
		$imgArr = explode('.', $source_path);
		$target_path = $imgArr[0] . '.' . $imgArr[1];
		imagejpeg($target_image, $target_path, 100);
		imagedestroy($target_image);	
		return $target_path;
	}
	
	function sendtxtmessage($message,$openId){
		$options = Config::get('wechat');
		$app = new Application($options);
		$result = $app->staff->message($message)->to($openId)->send();
	}

    public function getConstellation($data){
        if(strtotime($data) >=strtotime("2007-12-26") and strtotime($data)<=strtotime("2008-1-2")){
            $constellation = "魔羯座一";
        }else{
            $disval = District::where('birthday1',"<=",$data)->where('birthday2',">=",$data)->find();
            $constellation = $disval['constellation'];
        }
        return $constellation;
    }
}
