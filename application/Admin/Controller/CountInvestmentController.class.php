<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

/**
 * 客户查询
 * 普通员工登陆查看自己(若有推荐的会员则一同列出)
 * 超级管理员和管理员列出所有
 * Class ClientsController
 * @package Admin\Controller
 */
class CountInvestmentController extends AdminbaseController {

    function _initialize(){
        parent::_initialize();
        $this->users_obj = D("Common/Users");
        $this->department_obj = D("Common/Department");
        $this->sales_obj = D("Common/Sales");
        $this->salesCount_obj = D("Common/SalesCount");
    }

    public  function index(){

        //分类树,搜索时使用
        $result_all = $this->department_obj->field('id,parentid,name')->order(array("id" => "ASC"))->select();

        //搜索
        $_GET['departmentid'] = $department = $_REQUEST['departmentid'];
        $_GET['departmentname'] = I('post.departmentname');
        $_GET['user_realname'] = $realname = $_REQUEST['user_realname'];
        $_GET['time_limit'] = $time_limit = $_REQUEST['time_limit'];
        $_GET['begindate'] = $begindate = $_REQUEST['begindate'];
        $_GET['enddate'] = $enddate = $_REQUEST['enddate'];

        $s_where = array();
        if (!empty($department)) {
            $s_where['us.departmentid'] = array('in', $department);
            $u_where['departmentid'] = array('in', $department);
        }
        if (!empty($realname)) {
            $s_where['user_realname'] = array('like', '%' . $realname . '%');
            $u_where['user_realname'] = array('like', '%' . $realname . '%');
        }

        if (!empty($time_limit)) {
            if ($time_limit == 0.5) { //期限
                $s_where['hi.unit'] = array('EQ', 1);
                $s_where['hi.time_limit'] = array('EQ', 15);
            } else {
                $s_where['hi.time_limit'] = array('EQ', $time_limit);
            }
        }

        if (!empty($begindate)) {
            $beg = strtotime($begindate);
            if (!empty($enddate)) {
                $end = strtotime($enddate) + 24 * 3600 - 1;
            } else {
                $end = time();
            }
            $s_where['hi.success_time'] = array(array('EGT', $beg), array('ELT', $end));
        }

        $Model = M('HydInvestment');
        if(session('ADMIN_ID') == 1 || session('roid') == 2){   //超级管理员或者是管理员
            if (!empty($_GET['departmentid']) || !empty($_GET['user_realname']) || !empty($_GET['time_limit']) || !empty($_GET['begindate']) || !empty($_GET['customer'])) {
                $pwhere = $s_where;
                $uwhere = $u_where;
            }else{
                $pwhere = '1=1';
                $uwhere = '1=1';
            }

            $result_users = $this->users_obj->field('id')
                ->where($uwhere)
                ->where("id > 1")
                ->select();
            $userInfo = $this->users_obj->getBasicUsersInfo($result_users);

            $count = $this->salesCount_obj->getCountInvestmentsCount($Model,$pwhere);

            //自定义分页
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            foreach ($pwhere as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . '&';
            }

            $Investments = $this->salesCount_obj->getCountInvestmentsAllList($Model,$pwhere,$page->firstRow, $page->listRows);

            foreach($Investments as $r){
                $r['area_header']       = $userInfo[$r['usid']]['area_header'];
                $r['city_header']       = $userInfo[$r['usid']]['city_header'];
                $r['manager_name']      = $userInfo[$r['usid']]['manager'];
                $r['area_department']   = $userInfo[$r['usid']]['area_department'];
                $r['city_department']   = $userInfo[$r['usid']]['city_department'];
                $r['department_name']   = $userInfo[$r['usid']]['department_name'];
                $Investment[] = $r;
            }
        }else{      //部门主管,督导,城市经理,区域经理.(按职权大小)
            $departments = $this->department_obj->getAllDepartments($this->department_obj,session('ADMIN_ID'));
            $this->assign('departments', count($departments));

            $where = array();
            if(count($departments)>0){
                $departments_str = implode(',', $departments);
                if(empty($department)){
                    $where['hi.departmentid'] = array('IN', $departments_str);
                }
                //部门树，搜索用
                $result_all1 = $this->department_obj->field('id,parentid,name')->where('id IN ('.$departments_str.')')->order(array("id" => "ASC"))->select();
                $result_all2 = $this->department_obj->getParents($this->department_obj,$result_all1);
                $result_all3 = $this->department_obj->getParents($this->department_obj,$result_all2);
                $result_all = array_merge($result_all1,$result_all2,$result_all3);
//                print_r($result_all);
            }else{//普通员工
                $where['hi.user_id'] = array('EQ', session('ADMIN_ID'));
            }
            $s_where = array_merge_recursive($where, $s_where);

            if (!empty($_GET['departmentid']) || !empty($_GET['user_realname']) || !empty($_GET['time_limit']) || !empty($_GET['begindate']) || !empty($_GET['customer'])) {
                $pwhere = $s_where;
                $uwhere = $u_where;
            } else {
                $pwhere = $where;
                $uwhere = '1=1';
            }

            $result_users = $this->users_obj->field('id')
                ->where($uwhere)
                ->where("id > 1")
                ->select();
            $userInfo = $this->users_obj->getBasicUsersInfo($result_users);

            $count = $this->salesCount_obj->getCountInvestmentsCount($Model, $pwhere);

            //自定义分页
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            foreach ($pwhere as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . '&';
            }

            $Investments = $this->salesCount_obj->getCountInvestmentsAllList($Model, $pwhere, $page->firstRow, $page->listRows);
            foreach($Investments as $r){
                $r['area_header']       = $userInfo[$r['usid']]['area_header'];
                $r['city_header']       = $userInfo[$r['usid']]['city_header'];
                $r['manager_name']      = $userInfo[$r['usid']]['manager'];
                $r['area_department']   = $userInfo[$r['usid']]['area_department'];
                $r['city_department']   = $userInfo[$r['usid']]['city_department'];
                $r['department_name']   = $userInfo[$r['usid']]['department_name'];
                $Investment[] = $r;
            }
        }

        //导出
        if($_POST['explode'] == '1'){
            set_time_limit(120);
            ini_set('memory_limit', '2048M');
            $exportInvestments = $this->salesCount_obj->getCountInvestmentsAllList($Model, $pwhere, 0, $count);
            foreach($exportInvestments as $r){
                $r['idcard']            = $userInfo[$r['usid']]['idcard'];
                $r['area_header']       = $userInfo[$r['usid']]['area_header'];
                $r['city_header']       = $userInfo[$r['usid']]['city_header'];
                $r['manager_name']      = $userInfo[$r['usid']]['manager'];
                $r['area_department']   = $userInfo[$r['usid']]['area_department'];
                $r['city_department']   = $userInfo[$r['usid']]['city_department'];
                $r['department_name']   = $userInfo[$r['usid']]['department_name'];
                $r['leave_time']        = $userInfo[$r['usid']]['leave_time'];
                $r['entrydate']         = $userInfo[$r['usid']]['entrydate'];
                $r['poname']            = $userInfo[$r['usid']]['poname'];
                $exportInvestment[] = $r;
            }
            $Excel = A('Excel');
            $Excel->to_CountInvestment($exportInvestment);
        }

        $select_categorys = json_encode($result_all);
        $this->assign("select_categorys", $select_categorys);

        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('investment', $Investment);
        $this->display();
    }
}
