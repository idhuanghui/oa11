<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

/**
 * 任务管理(新加任务)
 * Class TaskController
 * @package Admin\Controller
 */
class TaskController extends AdminbaseController{
	protected $task_obj,$role_obj;
	
	function _initialize() {
		parent::_initialize();
		$this->task_obj = D("Common/Task");
	}

	function index(){

        $Task = M('TaskDiscount');
        $taskdis = $Task->field('id,name')->select();
//        print_r($taskdis);

        //搜索开始
        $s_where = array();
        $s_tasknum = I('post.tasknum');
        if(!empty($s_tasknum)){
            $s_where['tasknum'] = array('like',"%$s_tasknum%");
            $_GET['tasknum'] = $s_tasknum;
        }

        $count=$this->task_obj
                ->where($s_where)
                ->count();

        //自定义分页
        $page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
        $page = $this->page($count, $page_size);
        $_GET['page_size'] = $page_size;

        $tasks = $this->task_obj
                ->where($s_where)
                ->order("tasknum DESC")
                ->limit($page->firstRow . ',' . $page->listRows)
                ->select();

        $this->assign("tasks",$tasks);
        $this->assign('taskdis',$taskdis);
        $this->assign("formget",$_GET);
        $this->assign("page", $page->show('Admin'));
        $this->display();
	}

    /**
     * 添加任务
     */
	function add(){
        $this->display();
	}
	
	function add_post(){
        if(IS_POST){
            if($this->task_obj->create()){
                $result = $this->task_obj->add();
                $this->success("添加成功！", U("Task/index"));
            }else{
                $this->error($this->task_obj->getError());
            }
        }
	}

    /**
     * 编辑
     */
	function edit(){

            $this->display();
	}
	
	function edit_post(){
        if (IS_POST) {
            $id = I('post.id');
            $tasknum = I('post.yearnum').I('post.monthnum');
            $res = $this->task_obj->where('tasknum='.$tasknum.' and id != '.$id)->find();
            if($res){
                $this->error("任务编号已存在");
            }else{
                if ($this->task_obj->create()) {
                    $result=$this->task_obj->save();
                    if ($result!==false) {
                        $this->success("更新成功！");
                    } else {
                        $this->error("更新失败！");
                    }
                } else {
                    $this->error($this->task_obj->getError());
                }
            }
        }
	}
	
	/**
	 *  删除
	 */
	function delete(){
        $id = intval(I("get.id"));

        if ($this->task_obj->where("id=$id")->delete()!==false) {
                $this->success("删除成功！");
        } else {
                $this->error("删除失败！");
        }
	}
        
        
}