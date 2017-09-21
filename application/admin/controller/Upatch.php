<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\index\model;
use think\Exception;
use think\Session;
use app\admin\model\Patch;
use app\admin\model\Download;

require 'bos/BosClientSample.php';

class Upatch extends BaseController
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
        $db=Patch::alias('a')
		->where('a.version','like',$keyword.'%');
        if(!empty($start_at) && !empty($end_at)){
        $db->whereTime('a.create_at', 'between', [$start_at, $end_at]);
        }
        $db->order('a.id desc');
        $list=$db->paginate('10',false,$pageParam);
		
		// 版本排序
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
		$did = input('did');
		$data1 = Patch::where('id',$id)->find();
		$list = download::select();
		
	    if(request()->ispost()){
            $downurl2=input('downurl2');
            $db = new Patch();
            //上传补丁
            if($downurl2 ==''){
                // 获取表单上传补丁
                $file = request()->file('downurl');
				$fname = $_FILES['downurl']['name'];
				$arrfname = explode(".",$fname);
                // 根目录/uploads/ 目录下
                if(true){
					
                    //$info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->move('uploads/plug/');
					$info = $file->move('uploads/patch/');
                    if($info){
                        // 成功上传后 获取上传信息
                        $downurl_file=$info->getSaveName();
						$name = $info->getfilename();
                    }else{
                        // 上传失败获取错误信息
                        echo $file->getError();
                    }
                }
    
                $url =$downurl_file;
				$name = $arrfname[0];
            }else{
                $url =$downurl2;
				$name = $data1['name'];
            }
			
			$data=[
					'version' => input('version'),
					'name' => $arrfname[0],
					'pversion' => $did,
					'url'=>$url,
					//'message'=>input('message'),
			];
			
            if(input('id')!=''){
				
                //编辑插件
                $db ->allowField(true)->save($data,['id'=>$id]); 
            }else{
                $db ->allowField(true)->save($data);  
            } 
            
            if($db){
                $this->success('上传补丁成功','index');
            }
        }else{
		    $this->assign('list',$list);
			$this->assign('item',$data1);
			return $this->fetch("patch");
			
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
		$data = Patch::field('id')->where('version',$version)->find();
		if($data['id']){
			echo true;
		}else{
			echo false;
		}
	}
}
