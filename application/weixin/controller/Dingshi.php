<?php
namespace app\weixin\controller;

use think\Controller;
use think\Config;
use \think\Session;
use \think\Cookie;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Image;
use \think\exception\Handle;
use think\Request;
use app\weixin\model\Mfind; 
use app\weixin\model\Weixin; 
use app\weixin\model\User; 
use app\weixin\model\Know; 
use app\weixin\model\Birthday; 
use app\weixin\model\Hulue; 
use app\weixin\model\Alternative; 
use app\weixin\model\District; 
use app\weixin\model\Constellation; 
use app\weixin\model\Blood; 
use app\weixin\model\Friends;

class Dingshi extends BaseController
{
	public function index()
    {
		$options = Config::get('wechat');
		$app = new Application($options);
		
		$alluser = user::alias('a')
			->field('a.*,a.ID as suid,b.*')
			->join('weixin b','b.id=a.wid')
			->select();
			
		foreach($alluser as $allkey => $allval){ 
		
			$uid = $allval['suid'];
			$openid = $allval['openid'];
			
			$myfindval = mfind::where('uid',$uid)->find();
			if(!empty($myfindval)){
				$self=user::alias('a')
					->field('a.*,a.ID as suid,b.*,c.*')
					->join('weixin b','b.id=a.wid')
					->join('mfind c','c.uid=a.ID')
					->where('b.openid',$openid)
					->find();
			}else{
				$self=user::alias('a')
					->field('a.*,a.ID as suid,b.*')
					->join('weixin b','b.id=a.wid')
					->where('b.openid',$openid)
					->find();
			}
				
		//echo user::getLastSql();

			$suid = $self['suid'];
			
			$get_val = know::where('suid',$suid)->select();
			if(!empty($get_val)){
				$usersid='';
				foreach($get_val as $key => $val){
					$usersid.=$val['uid'].",";
				}
				$usersid = substr($usersid,0,-1);
				$where1 = "a.ID not in ($usersid)";
			}else{
				$where1 = '1=1';
			}
			
			$hget_val = hulue::where('suid',$suid)->select();
			if(!empty($hget_val)){
				$huusersid="";
				foreach($hget_val as $hkey => $hval){
					$huusersid.=$hval['uid'].",";
				}
				$huusersid = substr($huusersid,0,-1);
				$where3 = "a.ID not in ($huusersid)";
			}else{
				$where3 = '1=1';
			}
			
			$year=date("Y",time());
			$bymd2 = date('Y-m-d',$self['Birthday']);
			$selfymd = explode('-',$bymd2);
			$selfage=$year-$selfymd[0];
			
			if(empty($self['Loveage'])){
				if($self['Sex']==1){
					$where4 = "'{$selfage}','>=','Year(now())-Year(FROM_UNIXTIME(a.Birthday, '%Y-%m-%d'))'";
				}else{
					$where4 = "'{$selfage}','<','Year(now())-Year(FROM_UNIXTIME(a.Birthday, '%Y-%m-%d'))'";
				}
			}else{
				$where4 = '1=1';
			}
			
			$get_alval = Alternative::where('suid',$suid)->select();
			if(!empty($get_alval)){
				$ausersid="";
				foreach($get_alval as $alkey => $alval){
					$ausersid.=$alval['uid'].",";
				}
				$ausersid = substr($ausersid,0,-1);
				$where2 = "a.ID not in ($ausersid)";
			}else{
				$where2 = '1=1';
			}
			
			
		$get_frval = Friends::where('uid',$suid)->select();
		if(!empty($get_frval)){
			$userfsid="";
			foreach($get_frval as $key => $val){
				$userfsid.=$val['fid'].",";
			}
			$userfsid = substr($userfsid,0,-1);
			$where5 = "a.ID not in ($userfsid)";
		}else{
			$where5 = '1=1';
		}

		$selfsex = (!empty($self['Sex']))?$self['Sex']:$self['wsex'];

		
		if($selfsex=='1'){    //判断是同性还是异性
			$tong = 1;
			$yi = 2;
		}else{
			$tong = 2;
			$yi = 1;
		}
			
			$onemonth = time()-30*24*60*60;
			if($self['Wanna']=='同性朋友' || $self['Wanna']=='同性恋人'){
				$list=user::alias('a')
					->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header,b.*')
					->join('weixin b','b.id=a.wid')
					->where('a.Sex',$tong)
					->where($where1)
					->where($where2)
					->where($where3)
					->where($where5)
					->where('a.ID','neq',$suid)
					->order('a.Lasttime desc')
					->select();
				$istxyx = "非异性";
				
			}else{
				$list=user::alias('a')
					->field('a.*,a.ID as nuid,b.nickname as name ,b.headimgurl as header,b.*')
					->join('weixin b','b.id=a.wid')
					->where('a.Sex',$yi)
					->where($where1)
					->where($where2)
					->where($where3)
					->where($where5)
					->where('a.ID','neq',$suid)
					->order('a.Lasttime desc')
					->select();

				$istxyx = "异性"; 
			}
			//echo user::getLastSql();
			//exit();
			if(!empty($list)){
				
				$flag = 1;
				foreach($list as $key => $val){
				
					if($val['Sex']==1){
						$xingbie = '男';
					}else{
						$xingbie = '女';
					}
				
				
					$ymd = date('Y-m-d',$val['Birthday']);
					$bymd = explode('-',$ymd);
					$age=$year-$bymd[0];   //对方年龄
					
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
						}
					}
				
					if(!empty($self['City']) && !empty($list['City'])){
						if($self['City']==$list['City']){  //是否同城
							$city=true;
						}else{
							$city=false;

						}
					}else{
						$city=true;
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
							$tjly = '最佳夫妻';
							break;
					
						case '异性朋友':
							if(strpos($xingcon['best'],'朋友')!== false || strpos($xingcon['best'],'工作伙伴')!== false || strpos($xingcon['best'],'社交伙伴')!== false){
								$bestfind = true;
								$tjly = '最佳异性朋友';
							}else{
								$bestfind = false;
								$tjly = '最差异性朋友';
							}
							break;
						case '情侣':
							if(strpos($xingcon['best'],'情侣')!== false){
								$bestfind = true;
								$tjly = '最佳情侣';
							}else{
								$bestfind = false;
								$tjly = '最差情侣';
							}
							break;
						case '夫妻':
							if(strpos($xingcon['best'],'夫妻')!== false){
								$bestfind = true;
								$tjly = '最佳夫妻';
							}else{
								$bestfind = false;
								$tjly = '最差夫妻';
							}
							break;
						case '同性朋友':
							if(strpos($xingcon['best'],'朋友')!== false || strpos($xingcon['best'],'工作伙伴')!== false || strpos($xingcon['best'],'社交伙伴')!== false){
								$bestfind = true;
								$tjly = '最佳同性朋友';
							}else{
								$bestfind = false;
								$tjly = '最差同性朋友';
							}
							break;
						case '同性恋人':
							if(strpos($xingcon['best'],'情侣')!== false || strpos($xingcon['best'],'夫妻')!== false){
								$bestfind = true;
								$tjly = '最佳同性恋人';
							}else{
								$bestfind = false;
								$tjly = '最差同性恋人';
							}
							break;
						default:
							$bestfind = false;
							$tjly = '最糟关系';
							break;
					} 
					

					$uid = $val['nuid'];
					
					if($city && $bestfind){
						
						//$tmessage = "本周为你推荐的人为".$val['nickname']."点击<a href=\"http://weixin.matchingbus.com/index.php/weixin/detail/index/uid/".$uid."/suid/".$suid."/tjly/".$tjly."\">这里</a>查看TA的详细资料加为好友吧！";
						//$openid = $val['openid'];
						//$this->sendtxtmessage($tmessage,$openid);
						
						//$options = Config::get('wechat');
						//$app = new Application($options);
						$notice = $app->notice;
						$userId = $openid;
						$templateId = 'CXhc6nO5CRoOWt9LQ05a_8XeDHd_CYqmJPULXl9snPc';
						$url = 'http://weixin.matchingbus.com/index.php/weixin/detail/index/uid/'.$uid."/suid/".$suid.'/jh/1/flag2/2/tjly/'.$tjly;
						$data = array(
									"first"  => "本周为你推荐的人为:",
									"keyword1"   => $val['nickname'],
									"keyword2"  => "星数奇缘",
									"keyword3"  => date("Y-m-d",time()),
									"remark" => "点击下面链接赶紧查看TA的详细资料，加为好友吧！",
									);
						$result = $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
						
						$flag = 2;
						break;
					}
				}
				if($flag == 1){
								
					$message = "您的Mr/Ms Right还没有出现，将下方你的专属二维码，发送到朋友圈或发给那个Ta,看看谁是你的Mr/Ms Right？";
											
					$this->sendtxtmessage($message,$openid);
 					$temporary = $app->material_temporary;
					$path = $this->erweima($openid);
					$data = $temporary->uploadImage($path);
					@unlink($path);  //删除生成的二维码
					$imgmessage = new Image(['media_id' => $data['media_id']]);
					$this->sendtxtmessage($imgmessage,$openid);
				}
				
			}else{
			
				$message = "本周没有Mr/Ms Right出现，将下方你的专属二维码，发送到朋友圈或发给那个Ta,看看谁是你的Mr/Ms Right？";
										
				$this->sendtxtmessage($message,$openid);
 				$temporary = $app->material_temporary;
				$path = $this->erweima($openid);
				$data = $temporary->uploadImage($path);
				@unlink($path);  //删除生成的二维码
				$imgmessage = new Image(['media_id' => $data['media_id']]);
				$this->sendtxtmessage($imgmessage,$openid);
			}
		}
	}

	function erweima($bopenid=''){
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