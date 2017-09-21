<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\index\model;
use think\Exception;
use think\Session;
use app\admin\model\Download;
use app\admin\model\Zhiling;

require 'bos/BosClientSample.php';

class Upload extends BaseController
{
    public function index()
    {
		$keyword=input('get.keyword');
        $this->assign('keyword',$keyword);
        $start_at= input('get.start_at');
        $end_at= input('get.end_at');
        $this->assign('start_at',$start_at);
        $this->assign('end_at',$end_at);
        $pageParam['query']['keyword']=$keyword;
        $pageParam['query']['start_at'] = $start_at;
        $pageParam['query']['end_at'] = $end_at;
        $db=Download::alias('a')
		->where('a.version','like',$keyword.'%');
        if(!empty($start_at) && !empty($end_at)){
        $db->whereTime('a.create_at', 'between', [$start_at, $end_at]);
        }
        $db->order('a.id desc');
        $list=$db->paginate('10',false,$pageParam);
		
		//版本排序
		for($i = 0; $i <= count($list); $i++) {  
			for($j=$i+1; $j<=count($list)-1; $j++){
				if(version_compare($list[$i]['version'],$list[$j]['version'])==-1){
					$data=$list[$i];
					$list[$i]=$list[$j];
					$list[$j]=$data;
				}
			}
		}
        $page=$list->render();
        $this->assign('page',$page);
        $this->assign('list',$list);
		
		return $this->fetch();
    }

    public function issue()
    {
		$id = input('id');
		$data = Download::where('id',$id)->find();
		
	    if(request()->ispost()){
            $downurl2=input('downurl2');
			$zhilingurl2=input('zhilingurl2');
			$version = input('version');
			if($this->check_version()){
				$this->error('已存在此版本号！','index');
				exit();
			}
            $db = new Download();
            //上传附件
            if($downurl2 ==''){
                // 获取表单上传附件  
                $file = request()->file('downurl');
                // 根目录/uploads/ 目录下
                if(true){
                    //$info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->move('uploads/plug/');
					$info = $file->move('uploads/plug/','');
					
                    if($info){
                        // 成功上传后 获取上传信息
                        $downurl_file = $info->getSaveName();
						$filename = $info->getfilename();
						$arr = explode('.',$filename);
						$name = $arr[0]."_".$version;
                    }else{
                        // 上传失败获取错误信息
                        echo $file->getError();
                    }
                }

                $url =$downurl_file;
            }else{
                $url =$downurl2;
            }
			
			$data=[
				'version' => $version,
				'name' => $name,
				'url'=>$url,
				//'message'=>input('message'),
			];
			
			
			if(input('id')!=''){
                //编辑插件
                $db ->allowField(true)->save($data,['id'=>$id]); 
            }else{
                $db ->allowField(true)->save($data);  
            }
			
			
			
			
			
			//$o = '{"a":1,"b":2,"c":3,"d":4,"e":5}';
			if($zhilingurl2 ==''){
				
				$db2 = new Zhiling();
				
				$file = request()->file('zhiling');
				  
				$info = $file->validate(['ext'=>'jpg,jpeg'])->move('uploads/plug/');
				
				if($info){
					$path = $info->getPath().'/'.$info->getFilename();
				}else{
					$this->error('上传格式错误,必须是jpg,jpeg格式！');
				}
				$license = $path;
				
				
                    //$info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->move('uploads/plug/');
					/*$info = $file->move('uploads/plug/');
                    if($info){
                         $file=$info->getSaveName();
						$handle = fopen('uploads/plug/'.$file, "r");
						$contents = fread($handle, filesize('uploads/plug/'.$file));
						fclose($handle);
						
						$arr = json_decode($contents, true);
						$data=array();
						$i=0;
						foreach($arr as $key=>$val){
							$data[$i]['id']=$val;
							$data[$i]['name']=$key;
							$i++;
						} 

						$db2 = new Zhiling();
						$db2->saveAll($data,false);
						//unlink('uploads/plug/'.$file);
						
                    }else{
                        echo $file->getError();
                    }*/
			}else{
				$license = $zhilingurl2;   
			}
			
			$data2=[
				'name' => $license,
			];
			
            $db2 ->allowField(true)->save($data2);  

            $this->success('上传插件成功','index');
            
        }else{
						
			$this->assign('item',$data);
			return $this->fetch("upload");
			
		}
		
/*          $data=$_FILES['downurl'];
         //var_dump($data);
         //echo $data['tmp_name'];
         //$name=input('downname');
         $name=$data['name'];
         $bos=new \BosClientTest();
         //$url=$bos->upload($name,json_encode($data));
         try{
             $url=$bos->upload($name,$data['tmp_name']);

             //echo $uploadresult;
             //$url=$bos->geturl($name,$data['tmp_name']);
             $message=[
                 'version'=>input('version'),
                 'url'=>$url,
                 'name'=>$name,
                 'message'=>''
             ];
             $download=new Download();
             $result=$download->save($message);

             if($result)
             {
                  $this->success('上传成功', 'index');
             }
             else
             {
                 $this->error('上传失败', 'index');
             }
         }
         catch(Exception $e)
         {
             $this->error('上传出错了', 'index');
         } */
    }
	
	public function check_version(){
		$version = input('version');
		$data = Download::field('id')->where('version',$version)->find();
		if($data['id']){
			echo true;
		}else{
			echo false;
		}
	}
	
	public function del(Request $request){
		
		$id = $request->param('id');
		$data = Download::field('url')->where('id',$id)->find();
		unlink("/uploads/plug/".$data['url']);
		$samples = Download::where('id',$id)->delete();
		if($samples){
			$this->success('删除成功', 'index');
		}else{
			$this->error('删除失败', 'index');
		}
	}
}
