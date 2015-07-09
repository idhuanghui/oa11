<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

/**
 * 销售统计查询
 * 分个人业绩，团队业绩，分部业绩，分公司业绩
 * Class SalesCountController
 * @package Admin\Controller
 * zch
 */
class SalesCountController extends AdminbaseController {

    protected $users_obj;

    function _initialize() {
        parent::_initialize();
        $this->users_obj = D("Common/Users");
        $this->department_obj = D("Common/Department");
        $this->sales_obj = D("Common/SalesCount");
        $this->investment_obj = D("Common/Hyd_investment");
    }

    /**
     * 根据会员列取会员的业绩数据.
     */
    function index() {

        //分类树,搜索时使用
        $result_all = $this->department_obj->field('id,parentid,name')->order(array("id" => "ASC"))->select();

        //搜索开始
        $_GET['departmentid'] = $department = $_REQUEST['departmentid'];
        $_GET['departmentname'] = I('post.departmentname');
        $_GET['user_realname'] = $realname = $_REQUEST['user_realname'];  //员工
        $_GET['time_limit'] = $time_limit = $_REQUEST['time_limit'];
        $_GET['customer'] = $customer = $_REQUEST['customer'];
        $_GET['begindate'] = $begindate = $_REQUEST['begindate'];
        $_GET['enddate'] = $enddate = $_REQUEST['enddate'];
        
        if(empty($begindate)){
            $begindate = '2013-12-01';
        }
        if(empty($enddate)){
            $enddate = date('Y-m-d',time());
        }
        if($begindate==$enddate){
            $begin_end = $begindate;
        }else{
            $begin_end = $begindate.' — '.$enddate;
        }
  
        $s_where = array();
        if (!empty($department)) {
            $s_where['hi.departmentid'] = array('in', $department);
        }
        if (!empty($realname)) {
            $s_where['us.user_realname'] = array('like', '%' . $realname . '%');
        }
        if (!empty($time_limit)) {
            if ($time_limit == 0.5) { //期限
                $s_where['hi.unit'] = array('EQ', 1);
                $s_where['hi.time_limit'] = array('EQ', 15);
            } else {
                $s_where['hi.time_limit'] = array('EQ', $time_limit);
            }
        }
        if (!empty($customer)) {
            $s_where['hi.customer'] = array('like', '%' . $customer . '%');
        }
//		if(!empty($begindate) || !empty($enddate)){			//开始时间不能为空,结束时间问你则为当前时间
        if (!empty($begindate)) {
            $beg = strtotime($begindate);
            if (!empty($enddate)) {
                $end = strtotime($enddate) + 24 * 3600 - 1;
            } else {
                $end = time();
            }
            $s_where['hi.success_time'] = array(array('EGT', $beg), array('ELT', $end));
        }

        $Model = M('hydInvestment');
        if (session('ADMIN_ID') == 1) { //超级管理员显示所有的
            if (!empty($_GET['departmentid']) || !empty($_GET['user_realname']) || !empty($_GET['time_limit']) || !empty($_GET['begindate']) || !empty($_GET['customer'])) {
                $pwhere = $s_where;
            } else {
                $pwhere = '1=1';
            }
            $count = $this->sales_obj->getAllCount($Model, $pwhere);

            //自定义分页
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            foreach ($pwhere as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . '&';
            }

            $salesRes = $this->sales_obj->getAllList($Model, $pwhere, $page->firstRow, $page->listRows);
            //导出Excel
            if ($_POST['explode'] == '1') { 
                $salesRes = $this->sales_obj->getAllList($Model, $pwhere, 0, $count);
            }
            
        } else {  //部门主管，督导，城市经理，区域经理。（按职权大小）
            $departments = $this -> getAllDepartments(session('ADMIN_ID'));
            $where = array();
            if(count($departments)>0){
                $departments_str = implode(',', $departments);
                if(empty($department)){
                    $where['hi.departmentid'] = array('IN', $departments_str);
                }
                //部门树，搜索用
                $result_all1 = $this->department_obj->field('id,parentid,name')->where('id IN ('.$departments_str.')')->order(array("id" => "ASC"))->select();
                $result_all2 = $this->getParents($result_all1);
                $result_all3 = $this->getParents($result_all2);
                $result_all = array_merge($result_all1,$result_all2,$result_all3);
            }else{//普通员工
                $where['hi.user_id'] = array('EQ', session('ADMIN_ID'));
            }
            $s_where = array_merge_recursive($where, $s_where);
            
            if (!empty($_GET['departmentid']) || !empty($_GET['user_realname']) || !empty($_GET['time_limit']) || !empty($_GET['begindate']) || !empty($_GET['customer'])) {
                $pwhere = $s_where;
            } else {
                $pwhere = $where;
            }
            
            $count = $this->sales_obj->getAllCount($Model, $pwhere);
            
            //自定义分页
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            foreach ($pwhere as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . '&';
            }

            $salesRes = $this->sales_obj->getAllList($Model, $pwhere, $page->firstRow, $page->listRows);
            //导出Excel
            if ($_POST['explode'] == '1') { 
                $salesRes = $this->sales_obj->getAllList($Model, $pwhere, 0, $count);
            }
        }

        $saless = array();
        foreach ($salesRes as $r) {
            $city_data = $this->users_obj->getHeader($this->department_obj, $r['parentid']);
            $r['city_header'] = $city_data['user_realname'];
            $r['city_department'] = $city_data['name'];
            $area_data = $this->users_obj->getHeader($this->department_obj, $city_data['parentid']);
            $r['area_header'] = $area_data['user_realname'];
            $r['area_department'] = $area_data['name'];
            //督导
            $UModel = M('users');
            $udata = $UModel->where(array('id' => $r['manager']))->find();
            $r['manager'] = $udata['user_realname'];
            //标期特殊处理
            if ($r['unit'] == 1 && $r['time_limit'] == 15) {
                $r['time_limit'] = '0.5';
            }
            
            $r['begin_end'] = $begin_end;
            $saless[] = $r;
        }
        
        //导出Excel
        if ($_POST['explode'] == '1') { 
            $this->sales_obj->sales_export($saless);
        }

        $select_categorys = json_encode($result_all);
        $this->assign("select_categorys", $select_categorys);
        
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('sales', $saless);
        $this->display();
    }
    /*
     * 非超管理员拥有的三级部门
     * 部门主管，督导，城市经理，区域经理
     */
    protected function getAllDepartments($user_id){
        if(!empty($user_id)){
            //区域经理 - 一级
            $departments = array();
            $de_area = $this->department_obj->field('id')
                ->where(array('parentid' => '0','header' => $user_id))
                ->select();
            //var_dump($de_area);
            foreach ($de_area as $key => $value){
                //echo $value['id'];
                $departments = array_merge($departments,$this->getAreaDepartments($value['id']));
            }
            //城市经理
            $de_city = $this->department_obj->field('id')
                ->where(array('header' => $user_id))
                ->where('parentid <> 0')
                ->select();
            foreach ($de_city as $key => $value){
                $departments = array_merge($departments,$this->getCityDepartments($value['id'])); 
            }
            //督导
            $de_manager = $this->department_obj->field('id')
                ->where(array('manager' => $user_id))
                ->select();
            foreach ($de_manager as $key => $value){
                $departments[] = $value['id']; 
            }
            //部门主管
            if(session('level')==1){
                $departments[] = session('departmentid');
            }
            //去重
            $departments = array_unique($departments);
            //var_dump($departments);
            return $departments;
        }
    }
    /*
     * 获取一级下所有三级部门
     */
    protected function getAreaDepartments($id){
        if(!empty($id)){
            //区域经理 - 一级
            $departments = array();
            $de_city = $this->department_obj->field('id')
                ->where(array('parentid' => $id))
                ->select();

            //var_dump($de_area);
            foreach ($de_city as $key => $value){
                $departments = array_merge($departments,$this->getCityDepartments($value['id']));  
            }
            return $departments;
        }
    }
    /*
     * 获取二级下所有三级部门
     */
    protected function getCityDepartments($id){
        if(!empty($id)){
            $departments = array();
            $de = $this->department_obj->field('id')
                ->where(array('parentid' => $id))
                ->select();

            foreach ($de as $key => $value){
                $departments[] = $value['id'];
            }
            return $departments;
        }
        return false;
    }
    /*
     * 批量获取父层
     */
    protected function getParents($result){

            $departments = array();
            foreach ($result as $key => $value){
                $departments[] = $value['parentid'];
            }
            $departments_str = implode(',', $departments);
            
            $parents = $this->department_obj->field('id,parentid,name')
                ->where(array('id IN(' . $departments_str . ")" ))
                ->select();
            return $parents;

    }
}
