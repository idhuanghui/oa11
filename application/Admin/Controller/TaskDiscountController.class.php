<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

/**
 * 折比管理
 * Class TaskDiscountController
 * @package Admin\Controller
 */
class TaskDiscountController extends AdminbaseController{
	protected $task_obj,$role_obj;
	
	function _initialize() {
		parent::_initialize();
		$this->task_obj = D("Common/TaskDiscount");
	}

	function index(){
                //搜索开始
                $s_where = "";
                $count=$this->task_obj
                        ->where($s_where)
                        ->count();

                //自定义分页
                $page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
                $page = $this->page($count, $page_size);
                $_GET['page_size'] = $page_size;

                $tasks = $this->task_obj
                        ->where($s_where)
                        ->order("addtime DESC")
                        ->limit($page->firstRow . ',' . $page->listRows)
                        ->select();
                $this->assign("tasks",$tasks);
                $this->assign("formget",$_GET);
                $this->assign("page", $page->show('Admin'));
                $this->display();
	}

        /**
         * 添加折比
         */
	function add(){
            $this->display();
	}
	
	function add_post(){
            $TaskDiscountDetail = M("TaskDiscountDetail");
            if(IS_POST){
                $data['name'] = $_POST['name'];
                $data['addtime'] = $this->task_obj->mGetDate();
                $data['updatetime'] = $this->task_obj->mGetDate();
                if($this->task_obj->create()){
                    $result = $this->task_obj->data($data)->add();
                    //var_dump($result);
                    $count = $_POST['count'];
                    if($count){
                        for($i=1;$i<=$count;$i++){
                            $time_limit = $_POST['time_limit_'.$i];
                            $ratio = $_POST['ratio_'.$i];
                            $dataList[] = array('discountid'=>$result,'time_limit'=>$time_limit,'ratio'=>$ratio);
                        }
                    }
                    assoc_unique($dataList,"time_limit");
                    $TaskDiscountDetail->addAll($dataList);
                    $this->success("添加成功！", U("TaskDiscount/index"));
                }else{
                    $this->error($this->task_obj->getError());
                }
            }
	}

        /**
         * 编辑
         */
	function edit(){
            if (IS_POST) {
                $TaskDiscountDetail = M("TaskDiscountDetail");
                $data = array();
                $data = $TaskDiscountDetail->where(array('discountid'=>$_POST['discountid']))
                        ->order("time_limit ASC")
                        ->select();
                echo json_encode($data);
            }
	}
	
	function edit_post(){
            if (IS_POST) {
                $TaskDiscountDetail = M("TaskDiscountDetail");
                $data['discountid'] = $_POST['discountid'];
                $data['name'] = $_POST['name'];
                $data['addtime'] = $this->task_obj->mGetDate();
                $data['updatetime'] = $this->task_obj->mGetDate();
                
                $TaskDiscountDetail->where(array('discountid'=>$data['discountid']))->delete();
                //var_dump($result);
                $count = $_POST['count'];
                if($count){
                    for($i=1;$i<=$count;$i++){
                        $time_limit = $_POST['time_limit_'.$i];
                        $ratio = $_POST['ratio_'.$i];
                        $dataList[] = array('discountid'=>$data['discountid'],'time_limit'=>$time_limit,'ratio'=>$ratio);
                    }
                }
                assoc_unique($dataList,"time_limit");
                $TaskDiscountDetail->addAll($dataList);
                $this->success("添加成功！", U("TaskDiscount/index"));
  
            }
	}
	
	/**
	 *  删除
	 */
	function delete(){
            $id = intval(I("get.id"));
            $TaskDiscountDetail = M("TaskDiscountDetail");
            if ($this->task_obj->where("id=$id")->delete()!==false) {
                    $TaskDiscountDetail->where(array('discountid'=>$id))->delete();
                    $this->success("删除成功！");
            } else {
                    $this->error("删除失败！");
            }
	}
        
        
}