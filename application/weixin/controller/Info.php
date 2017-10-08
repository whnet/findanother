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
        echo phpinfo();
        if (!extension_loaded('Imagick')) {
             echo 'error';
        }else{
            var_dump(1);
        }
      exit();
		if(input('flag')){
			$yaoqingopenid = !empty(input('openid'))?input('openid'):'';//别人
			Cookie::set('yaoqingopenid',$yaoqingopenid,300);
			$byaoqingopenid = !empty(input('bopenid'))?input('bopenid'):'';//我自己
			Cookie::set('byaoqingopenid',$byaoqingopenid,300);
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

        $user = Cookie::get('wechat_user');
        $nickname = $user['nickname'];
        $headimg = $user['avatar'];
        $openid = $user["id"];
         // 提交资料之前将头像下载下来，并且变成圆形, 存放在circlehead中,在这一步可以同时将所需的字体合成，
            $fileName = 'uploads/header/'.$openid.'.jpeg';
            $this->downloadWechatImage($headimg, $fileName);
//            $circleHead = $this->yuan_img($fileName,$openid);
            $circleHead = $this->toCircle($fileName,$openid);
            var_dump($circleHead);
            exit();

        //和背景图结合起来
        $src = 'uploads/background/background.jpg';//背景图片
        $markimgurl = $this->myImageResize($circleHead, '180', '180');   //缩放图片
        $imgpath = $this->water_mark($src,$markimgurl,$openid);

		//判断是否是扫别人分享的二维码进来的，在scan中写入cookies
		if(request()->ispost()){
            $id = Cookie::get('openid');

			$val = Weixin::where('openid',$id)->find();
		   $db = new user();
           $lab_data=[
				'wid'=>$val['id'],
				'Sex'=>input("sex"),
                'Birthday'=>strtotime(input("birthday")),
				'Regtime'=>time(),
           ];

           $uid = $db ->insertGetId($lab_data);
           //提交资料成功之后，给公众号发送一个二维码和一段话
            if($uid){
                //获取opened
                $user = Cookie::get('wechat_user');
                $open_id = $user['original']['openid'];
                //查询数据库中用户的账号的openid中是否有值，有值说明用户的微信与账号绑定
                $result = Weixin::check_login($open_id);
                if($result){
                    $openid = $result['openid'];
                    $message2 = "Hi 我是星数君，将下方你的专属二维码，发送到朋友圈或发给那个Ta，看看谁是你的Mr/Ms Right！";
                    $this->sendtxtmessage($message2,$openid);

                    $options = Config::get('wechat');
                    $app = new Application($options);
                    $temporary = $app->material_temporary;
                    $path = $this->erweima($openid);
                    $data = $temporary->uploadImage($path);
                    // @unlink($path);
                    $imgmessage = new Image(['media_id' => $data['media_id']]);
                    $this->sendtxtmessage($imgmessage,$openid);
                }
                //同时，通知与扫码者的关系

                $yaoqingpenid = Cookie::get('yaoqingopenid');
                $beiyaoqingopenid = Cookie::get('byaoqingopenid');


                if($yaoqingpenid && $beiyaoqingopenid){
                    //通过生日获得两人的关系
                    $yval = weixin::where('openid',$yaoqingpenid)->find();
                    $yuinfo = user::where('wid',$yval['id'])->find();
                    //被邀请者
                    $bval = weixin::where('openid',$beiyaoqingopenid)->find();
                    $buinfo = user::where('wid',$bval['id'])->find();
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

                    $yval = weixin::where('openid',$yaoqingpenid)->find();
                    $aname = $yval['nickname'];
                    //$guanxi = $this->get_guanxi($yaoqingpenid,$beiyaoqingopenid);
                    $message = "您和".$aname."的关系是：".$best."，<a href='http://weixin.matchingbus.com/index.php/weixin/gxpipei/index/yopenid/".$yaoqingpenid."/bopenid/".$beiyaoqingopenid."'>点击查看</a>";
                    $this->sendtxtmessage($message,$beiyaoqingopenid);

                    $val = weixin::where('openid',$beiyaoqingopenid)->find();
                    $bname = $val['nickname'];
                    //$guanxi2 = $this->get_guanxi($beiyaoqingopenid,$yaoqingpenid);
                    $bmessage = $bname."刚扫码成为你的好友，你和".$bname."的关系是：".$best."，<a href='http://weixin.matchingbus.com/index.php/weixin/gxpipei/index/yopenid/".$beiyaoqingopenid."/bopenid/".$yaoqingpenid."'>点击查看</a>";
                    $this->sendtxtmessage($bmessage,$yaoqingpenid);
                }

            }



		   	$yopenid = !empty(Cookie::get('yaoqingopenid'))?Cookie::get('yaoqingopenid'):'';
			if(!empty($yopenid)){
				
			   $yval = weixin::where('openid',$yopenid)->find();
			   $yuinfo = user::where('wid',$yval['id'])->find();
			   $name = $yval['nickname'];
			   $bname = $val['nickname'];


				$fdb = new friends();
				$ylab_fdata=[
						'uid'=>$yuinfo['ID'],
						'fid'=>$uid,
						'flag'=>2,
						'create_at'=>time(),
				];
				
				$fdb ->save($ylab_fdata);

                //$guanxi = $this->get_guanxi($id,$yopenid);
                //$messageGuanxi = "您和".$name."的关系是：".$guanxi."，<a href=\"http://weixin.matchingbus.com/index.php/weixin/gxpipei/index/yopenid/".$id."/bopenid/".$yopenid."\">点击查看</a>";
                //$this->sendtxtmessage($messageGuanxi,$id);
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
				'Province'=>$addr[0],
				'City'=>$addr[1],
                'Wanna'=>input("find"),
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
                $info = $file->validate(['size'=>510000000000,'ext'=>'jpg,png,gif,jpeg'])->move('uploads/photo/');
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
			$message = "赞~~你的匹配信息已完善！";
		
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
   public function getConstellation($data){
        if(strtotime($data) >=strtotime("2007-12-26") and strtotime($data)<=strtotime("2008-1-2")){
            $constellation = "魔羯座一";
        }else{
            $disval = District::where('birthday1',"<=",$data)->where('birthday2',">=",$data)->find();
            $constellation = $disval['constellation'];
        }
        return $constellation;
    }

    /*
     * 将微信的头像下载下来
     */
    function downloadWechatImage($remoteImg, $fileName){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch,CURLOPT_URL,$remoteImg);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file_content = curl_exec($ch);
        curl_close($ch);
        $downloaded_file = fopen($fileName, 'w');
        fwrite($downloaded_file, $file_content);
        fclose($downloaded_file);
        return true;
    }

    /*
     * Imagick
     */
    function toCircle($url, $openid){
        header('Content-type: image/jpeg');
        $image = new \Imagick('http://static.oschina.net/uploads/user/29/58387_100.jpg');
        $image->setImageFormat('png');
        $image->roundCorners(360,360);
        echo $image;
        $image->destroy();
        return $openid.$image;
    }

    /*
     * 将用户头像处理成圆形
     */
    function yuan_img($imgpath, $name) {
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
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagecolortransparent($img,$bg);
        imagefill($img, 0, 0, $bg);
        imagesavealpha($img, true);//这里很重要,意思是不要丢了$thumb图像的透明色;
        imagealphablending($img,false); //这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
        //拾取一个完全透明的颜色,最后一个参数127为全透明

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
        header("content-type:image/png");
        imagepng($img, 'uploads/circlehead/'.$name.'.png');
        imagedestroy($img);			// 释放内存
        return 'uploads/circlehead/'.$name.'.png';
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
    public function water_mark($src,$mark_img,$openid,$pct = 100)
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
            if($openid){
                $picname = $openid;
                imagejpeg($src_im, 'uploads/shareimg/'.$picname.'.png');
                imagedestroy($src_im);			// 释放内存
                return 'uploads/shareimg/'.$picname.'.png';
            }else{
                return imagejpeg($src_im);
            }
            //return '/upload/ewm/'.$picname.'.png';
        }
    }


}