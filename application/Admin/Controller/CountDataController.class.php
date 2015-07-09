<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

/**
 * 数据统计-数据统计
 * 分个人业绩，团队业绩，分部业绩，分公司业绩
 * Class CountDataController
 * @package Admin\Controller
 * zch
 */
class CountDataController extends AdminbaseController {

    protected $users_obj;

    function _initialize() {
        parent::_initialize();
        $this->users_obj = D("Common/Users");
        $this->department_obj = D("Common/Department");
        $this->cd_obj = D("Common/CountData");
    }

    /**
     * 根据会员列取会员的业绩数据.
     */
    function index() {
        //$this -> _set_super_data();
        //分类树,搜索时使用
        $result_all = $this->department_obj->field('id,parentid,name')->order(array("id" => "ASC"))->select();

        //搜索开始
        $_GET['departmentid'] = $department = $_REQUEST['departmentid'];
        $_GET['departmentname'] = I('post.departmentname');
        $_GET['user_realname'] = $realname = $_REQUEST['user_realname'];  //员工
 
        $_GET['begindate'] = $begindate = $_REQUEST['begindate'];
        $_GET['enddate'] = $enddate = $_REQUEST['enddate'];
  
        $s_where = array();$u_where = array();
        if (!empty($department)) {
            $s_where['hr.departmentid'] = array('in', $department);
            $u_where['departmentid'] = array('in', $department);
        }
        if (!empty($realname)) {
            $s_where['us.user_realname'] = array('like', '%' . $realname . '%');
            $u_where['user_realname'] = array('like', '%' . $realname . '%');
        }

        if (!empty($begindate)) {
            $beg = $begindate;
            if (!empty($enddate)) {
                $end = $enddate;
            } else {
                $end = date('Y-m-d',time());
            }
            $s_where['hr.query_date'] = array(array('EGT', $beg), array('ELT', $end));
        }

        $Model = M('hydReport');
        if (session('ADMIN_ID') == 1) { //超级管理员显示所有的
            if (!empty($_GET['departmentid']) || !empty($_GET['user_realname']) || !empty($_GET['begindate'])) {
                $pwhere = $s_where;
                $uwhere = $u_where;
            } else {
                $pwhere = '1=1';
                $uwhere = '1=1';
            }
            
            $result_users = $this->users_obj->field('id')
                ->where($uwhere)
                ->where("id > 1")
                ->select();
            $userInfo = $this->users_obj->getUsersInfo2($result_users);
            
            $count = $this->cd_obj->getAllCount($Model, $pwhere);

            //自定义分页
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            foreach ($pwhere as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . '&';
            }

            $salesRes = $this->cd_obj->getAllList($Model, $pwhere, $page->firstRow, $page->listRows);
            //导出Excel
            if ($_POST['explode'] == '1') { 
                $salesRes = $this->cd_obj->getAllList($Model, $pwhere, 0, $count);
            }
            
        } else {  //部门主管，督导，城市经理，区域经理。（按职权大小）
            $departments = $this->department_obj-> getAllDepartments($this->department_obj,session('ADMIN_ID'));
            $where = array();
            if(count($departments)>0){
                $departments_str = implode(',', $departments);
                if(empty($department)){
                    $where['hr.departmentid'] = array('IN', $departments_str);
                }
                //部门树，搜索用
                $result_all1 = $this->department_obj->field('id,parentid,name')->where('id IN ('.$departments_str.')')->order(array("id" => "ASC"))->select();
                $result_all2 = $this->department_obj->getParents($this->department_obj,$result_all1);
                $result_all3 = $this->department_obj->getParents($this->department_obj,$result_all2);
                $result_all = array_merge($result_all1,$result_all2,$result_all3);
            }else{//普通员工
                $where['hr.user_id'] = array('EQ', session('ADMIN_ID'));
            }
            $s_where = array_merge_recursive($where, $s_where);
            
            if (!empty($_GET['departmentid']) || !empty($_GET['user_realname']) || !empty($_GET['begindate']) ) {
                $pwhere = $s_where;
            } else {
                $pwhere = $where;
            }
            
            $count = $this->cd_obj->getAllCount($Model, $pwhere);
            
            //自定义分页
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            foreach ($pwhere as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . '&';
            }

            $salesRes = $this->cd_obj->getAllList($Model, $pwhere, $page->firstRow, $page->listRows);
            //导出Excel
            if ($_POST['explode'] == '1') { 
                $salesRes = $this->cd_obj->getAllList($Model, $pwhere, 0, $count);
            }
        }
        set_time_limit(90);
        ini_set('memory_limit', '1024M');
        $saless = array();
        foreach ($salesRes as $r) {
            $r['area_header']       = $userInfo[$r['user_id']]['area_header'];
            $r['city_header']       = $userInfo[$r['user_id']]['city_header'];
            $r['manager_name']      = $userInfo[$r['user_id']]['manager'];
            $r['area_department']   = $userInfo[$r['user_id']]['area_department'];
            $r['city_department']   = $userInfo[$r['user_id']]['city_department'];
            $r['department_name']   = $userInfo[$r['user_id']]['department_name'];
            $r['userid']   	        = 'HYD'.str_pad($r['user_id'],5,0,STR_PAD_LEFT);
            $saless[] = $r;
        }
        
        //导出Excel
        if ($_POST['explode'] == '1') { 
            $this->cd_obj->sales_export($saless);
        }

        $select_categorys = json_encode($result_all);
        $this->assign("select_categorys", $select_categorys);
        
        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('sales', $saless);
        $this->display();
    }
    
    /*
     * 同步所有数据，谨慎操作，时间较长。
     */
    function super_data(){
        //清空数据库表
        M()->execute("truncate " . C('DB_PREFIX') . 'hyd_report');
        $this ->_set_super_data();
    }
    
    /*
     * 时间段内更新同步数据（最好不要跨月执行）
     * 默认更新前一天数据
     */
    function super_data_date(){
        if($_GET['starttime']&&$_GET['endtime']){
            $starttime = $_GET['starttime'];
            $endtime = $_GET['endtime'];
        }else{
            $starttime =date('Y-m-d',strtotime('-1 day'));
            $endtime = date('Y-m-d',strtotime('-1 day'));
        }
        $this ->_set_super_data($starttime,$endtime);
    }
    
    private function _set_super_data($starttime,$endtime) {
        $result_users = $this->users_obj
                ->where("id<>1")
                ->getField("id,hyd_id,departmentid,entrydate");
        //var_dump($result_users);
        
        //员工所有数据插入
        foreach ($result_users as $key => $value) {
            $starttime_flag = $starttime;
            $endtime_flag = $endtime;
            //入职时间之前不做数据统计
            if($starttime < $value['entrydate']){
                if($endtime < $value['entrydate']){
                    continue;
                }
                $starttime_flag = $value['entrydate'];
            }
            
            $reportRes = customer_inquiry(C('INTERFACE_URL_RU'),$value['hyd_id'],0,0,'',$starttime_flag,$endtime_flag);
            
//            if(!$reportRes['data']){
//                echo $value['id'].'|'.$value['hyd_id'].'<br>';
//            }
            foreach ($reportRes['data'] as $k=>$v){ 
                $this->_add_hyd_report($value['id'],$value['hyd_id'],$value['departmentid'],$v);
            }
            //break;
        }
    }

    /*
     * oa数据库插入-单条
     */
    private function _add_hyd_report($user_id, $hyd_id, $departmentid, $data) {
        $hyd_model = M('hydReport');

        $data["user_id"]= $user_id; 
        $data["hyd_id"] = $hyd_id;  
        $data["departmentid"] = $departmentid;
        
        $result = array();
        $result = $hyd_model->field('id')
            ->where(array("query_date" => $data['query_date'],"user_id" => $user_id))
            ->select();
        if(count($result)>0){
            return FALSE;
        }
        $hyd_model->add($data);
    }

}
