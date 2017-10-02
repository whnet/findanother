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

class BaseController extends Controller
{
	
	public function _initialize()
    {
		$options = Config::get('wechat');
		$app = new Application($options);
		if(!Cookie::has('openid')) {

			$this->redirect('info/index');
		}
		

		$js = $app->js;
		$this->assign('js', $js);
		$this->assign('desc','【爱情巴士】全国第一家专业星座配对网站！性格配对准确率高达90%！10年专注于夫妻情侣的配对研究，注册会员超过1.5万华人。');

    }
	public function match_others($best, $data){
        $xingcon['best'] = $best;
        switch($data){
            case '合适就行':
                $bestfind = true;
                $heshiweizhi = '最佳夫妻';
                break;

            case '异性朋友':
                if(strpos($xingcon['best'],'朋友')!== false || strpos($xingcon['best'],'工作伙伴')!== false || strpos($xingcon['best'],'社交伙伴')!== false){
                    $bestfind = true;
                    $heshiweizhi = '最佳异性朋友';
                }
                else{
                    $bestfind = false;
                    $heshiweizhi = '最差异性朋友';
                }
                break;
            case '情侣':
                if(strpos($xingcon['best'],'情侣')!== false){
                    $bestfind = true;
                    $heshiweizhi = '最佳情侣';
                }
                else{
                    $bestfind = false;
                    $heshiweizhi = '最差情侣';
                }
                break;
            case '夫妻':
                if(strpos($xingcon['best'],'夫妻')!== false){
                    $bestfind = true;
                    $heshiweizhi = '最佳夫妻';
                }
                else{
                    $bestfind = false;
                    $heshiweizhi = '最差夫妻';
                }
                break;
            case '同性朋友':
                if(strpos($xingcon['best'],'朋友')!== false || strpos($xingcon['best'],'工作伙伴')!== false || strpos($xingcon['best'],'社交伙伴')!== false){
                    $bestfind = true;
                    $heshiweizhi = '最佳同性朋友';
                }
                else{
                    $bestfind = false;
                    $heshiweizhi = '最差同性朋友';
                }
                break;
            case '同性恋人':
                if(strpos($xingcon['best'],'情侣')!== false || strpos($xingcon['best'],'夫妻')!== false){
                    $bestfind = true;
                    $heshiweizhi = '最佳同性恋人';
                }
                else{
                    $bestfind = false;
                    $heshiweizhi = '最差同性恋人';
                }
                break;
            default:
                $bestfind = false;
                $heshiweizhi = '最糟关系';
                break;
        }
        return [$heshiweizhi,$bestfind];
    }


	public function get_guanxi($yopenid,$bopenid){
		
		$selfval=user::alias('a')
            ->field('a.*,a.ID as suid')
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
	
	function sendtxtmessage($message,$openId){
			$options = Config::get('wechat');
			$app = new Application($options);
			$result = $app->staff->message($message)->to($openId)->send();
	}
	
}