<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\index\model;
use think\Exception;
use think\Session;
use app\admin\model\Models;

require 'bos/BosClientSample.php';

class Kmodule extends BaseController
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
        $db=Models::alias('a')
		->where('a.name','like',$keyword.'%');
        if(!empty($start_at) && !empty($end_at)){
			$db->whereTime('a.create_at', 'between', [$start_at, $end_at]);
        }
        $db->order('a.id desc');
        $list=$db->paginate('10',false,$pageParam);
		
        $page=$list->render();
        $this->assign('page',$page);
        $this->assign('list',$list);
		
		return $this->fetch();
    }

    public function add_kmodel()
    {
		$id = input('id');
		$item = Models::where('id',$id)->find();
		
	    if(request()->ispost()){
            $downurl2=input('downurl2');
            $db = new Models();
            //上传模型
            if($downurl2 ==''){
                // 获取表单上传模型
                $file = request()->file('downurl');
                // 根目录/uploads/ 目录下
                if(true){
					
                    //$info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->move('uploads/plug/');
					$info = $file->move('uploads/module/');
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
            }else{
                $url =$downurl2;   
            }
			
			$data=[
					'mtype' => input('mtype'),
					'cate' => input('cate'),
					'name' => input('name'),
					'url'=>$url,
			];
			
            if(input('id')!=''){
				
                //编辑模型
                $db ->allowField(true)->save($data,['id'=>$id]); 
            }else{
                $db ->allowField(true)->save($data);  
            } 
            
            if($db){
                $this->success('上传模型成功','index');
            }
        }else{
						
			$this->assign('item',$item);
			return $this->fetch("add");
			
		}
    }
	
	public function del(){
		$id = input('id');
		$db = Models::get($id);
		unlink($db['url']);
        $data = $db -> delete();
		if($data){
			$this->success('删除模型成功','index');
		}else{
			$this->error('删除模型失败','index');
		}
	}
	
	public function check_name(){
		$name = input('name');
		$data = Models::field('id')->where('name',$name)->find();
		if($data['id']){
			echo 1;
		}else{
			echo 0;
		}
	}
}
