<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PositionController extends AdminbaseController{
	protected $position_obj,$role_obj;
	
	function _initialize() {
		parent::_initialize();
		$this->position_obj = D("Common/Position");
                $this->position_category_obj = D("Common/PositionCategory");
                $this->role_obj = D("Common/Role");
	}
	function index(){
                /*搜索开始*/
                $where_ands = array("1");
		$fields=array(
                    //'start_time'=> array("field"=>"post_date","operator"=>">"),
                    //'end_time'  => array("field"=>"post_date","operator"=>"<"),
                    'status'  => array("field"=>"p.status","operator"=>"="),
                    'name'  => array("field"=>"p.name","operator"=>"like"),
		);
                if(IS_POST){
                    foreach ($fields as $param =>$val){
                        if (isset($_POST[$param]) && !empty($_POST[$param])) {
                            $operator=$val['operator'];
                            $field   =$val['field'];
                            $get=$_POST[$param];
                            $_GET[$param]=$get;
                            if($operator=="like"){
                                    $get="%$get%";
                            }
                            if($field=="p.status"&&$get==2){
                                $get = 0;
                            }
                            array_push($where_ands, "$field $operator '$get'");
                        }
                    }
		}
                $where= join(" and ", $where_ands);
                /*----搜索条件结束*/
                
                $model = M();
		$count= $model->table(C('DB_PREFIX')."position p")
                        ->where($where)
                        ->count();
                
                //自定义分页
                $page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
		$page = $this->page($count, $page_size);
                $_GET['page_size'] = $page_size;
                        
		$positions = $model->table(C('DB_PREFIX')."position p")
                        ->field("p.id, p.category_id, p.name, p.description, p.status, pc.name as category_name")
                        ->join("LEFT join ".C('DB_PREFIX')."position_category pc on p.category_id = pc.id")
                        ->where($where)
                        ->order("p.listorder ASC,p.id DESC")
                        ->limit($page->firstRow . ',' . $page->listRows)
                        ->select();
		
		$this->assign("page", $page->show('Admin'));
                $this->assign("formget",$_GET);
                $this->assign("positions",$positions);

                $categorys = $this->position_category_obj->order("listorder asc")->select();
                $this->assign("categorys",$categorys);
            
		$this->display();
	}
	
	
	function add(){
            $categorys = $this->position_category_obj->order("listorder asc")->select();
            $this->assign("categorys",$categorys);
            
            $roles=$this->role_obj->where("status=1")->order("id desc")->select();
            $this->assign("roles",$roles);
            $this->display();
	}
	
	function add_post(){
            if(IS_POST){
                if ($this->position_obj->create()) {
                    $result=$this->position_obj->add();
                    if ($result!==false) {
                        $this->success("添加成功！", U("position/index"));
                    } else {
                        $this->error("添加失败！");
                    }
                } else {
                    $this->error($this->position_obj->getError());
                }
            }
	}
	
        
	
	function edit(){
            $id= intval(I("get.id"));
            $roles=$this->role_obj->where("status=1")->order("id desc")->select();
            $this->assign("roles",$roles);
            
            $categorys = $this->position_category_obj->order("listorder asc")->select();
            $this->assign("categorys",$categorys);
            
            $position=$this->position_obj->where(array("id"=>$id))->find();
            $this->assign($position);

            $this->display();
	}
	
	function edit_post(){
            if (IS_POST) {
                if ($this->position_obj->create()) {
                    $result=$this->position_obj->save();
                    if ($result!==false) {
                            $this->success("保存成功！");
                    } else {
                            $this->error("保存失败！");
                    }
                } else {
                        $this->error($this->position_obj->getError());
                }
            }
	}
	
	/**
	 *  删除
	 */
	function delete(){
            $id = intval(I("get.id"));

            if ($this->position_obj->where("id=$id")->delete()!==false) { //原删除
                    $this->success("删除成功！");
            } else {
                    $this->error("删除失败！");
            }
	}
        
        //重置状态
        function status_reset(){
            if($_POST){
                $data = array();
                if($_POST['status']){
                    $data['status'] = '0';
                }else{
                    $data['status'] = '1';
                }
                $res = $this->position_obj->where(array('id'=>trim($_POST['id'])))->data($data)->save();
                if($res){
                    echo '状态重置成功!';
                }else{
                    echo '状态重置失败!';
                }
            }
        }
        
                
	function category_index(){
            //搜索开始
            $s_where = array();
            $s_name = I('post.name');
            if(!empty($s_name)){
                $s_where['name'] = array('like',"%$s_name%");
                $_GET['name'] = $s_name;
            }

            $count=$this->position_category_obj
                    ->where($s_where)
                    ->count();

            //自定义分页
            $page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;
            
            $categorys = $this->position_category_obj
                    ->where($s_where)
                    ->order("listorder ASC,id DESC")
                    ->limit($page->firstRow . ',' . $page->listRows)
                    ->select();
            
            $this->assign("formget",$_GET);
            $this->assign("page", $page->show('Admin'));
            $this->assign("categorys",$categorys);
            $this->display();            
	}
	function category_add(){
            $roles=$this->role_obj->where("status=1")->order("id desc")->select();
            $this->assign("roles",$roles);
            $this->display();
	}

	function category_add_post(){
            if(IS_POST){
                if ($this->position_category_obj->create()) {
                    $result = $this->position_category_obj->add();
                    if ($result!==false) {
                        $this->success("添加成功！", U("position/category_index"));
                        
                    } else {
                        $this->error("添加失败！");
                    }
                } else {
                    $this->error($this->position_category_obj->getError());
                }
            }
	}
	function category_edit(){
            $id= intval(I("get.id"));
            $roles=$this->role_obj->where("status=1")->order("id desc")->select();
            $this->assign("roles",$roles);

            $position_category=$this->position_category_obj->where(array("id"=>$id))->find();
            $this->assign($position_category);

            $this->display();
	}
        
	function category_edit_post(){
            if (IS_POST) {
                if ($this->position_category_obj->create()) {
                    $result=$this->position_category_obj->save();
                    if ($result!==false) {
                            $this->success("保存成功！");
                    } else {
                            $this->error("保存失败！");
                    }
                } else {
                        $this->error($this->position_category_obj->getError());
                }
            }
	}
	/**
	 *  删除类别
	 */
	function category_delete(){
            $id = intval(I("get.id"));

            if ($this->position_category_obj->where("id=$id")->delete()!==false) {
                    $this->success("删除成功！");
            } else {
                    $this->error("删除失败！");
            }
	}
}