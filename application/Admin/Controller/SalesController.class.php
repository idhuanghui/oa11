<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

/**
 * 销售查询
 * 非管理员仅看个人业绩
 * Class SalesController
 * @package Admin\Controller
 */
class SalesController extends AdminbaseController {

    protected $users_obj;

    function _initialize() {
        parent::_initialize();
        $this->users_obj = D("Common/Users");
        $this->department_obj = D("Common/Department");
        $this->sales_obj = D("Common/Sales");
        $this->investment_obj = D("Common/Hyd_investment");
    }

    /**
     * 根据会员列取会员的业绩数据.
     */
    function index() {

        //分类树,搜索时使用
        $result = $this->department_obj->field('id,parentid,name')->order(array("id" => "ASC"))->select();
        $select_categorys = json_encode($result);
        $this->assign("select_categorys", $select_categorys);

        //搜索开始
        $_GET['departmentid'] = $department = $_REQUEST['departmentid'];
        $_GET['departmentname'] = I('post.departmentname');
        $_GET['user_realname'] = $realname = $_REQUEST['user_realname'];  //员工
        $_GET['time_limit'] = $time_limit = $_REQUEST['time_limit'];
        $_GET['borrow_type'] = $borrow_type = $_REQUEST['borrow_type'];
        $_GET['customer'] = $customer = $_REQUEST['customer'];
        $_GET['begindate'] = $begindate = $_REQUEST['begindate'];
        $_GET['enddate'] = $enddate = $_REQUEST['enddate'];

        $s_where = array();
        if (!empty($department)) {
            $s_where['hi.departmentid'] = array('in', $department);
            $u_where['departmentid'] = array('in', $department);
        }
        if (!empty($realname)) {
            $s_where['us.user_realname'] = array('like', '%' . $realname . '%');
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
        if (!empty($borrow_type) || $borrow_type === '0') {   //Trace($borrow_type.'abc');
            $s_where['hi.borrow_type'] = array('EQ', $borrow_type);
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
        if (session('ADMIN_ID') == 1 || session('roid') == '2') { //管理员显示所有的
            if (!empty($_GET['departmentid']) || !empty($_GET['user_realname'])) {
                $uwhere = $u_where;
            } else {
                $uwhere = '1=1';
            }
            $result_users = $this->users_obj->field('id')
                ->where($uwhere)
                ->where("hyd_id <> 0")
                ->select();
            //var_dump($result_users);
            $user_info = $this->users_obj->getUsersInfo2($result_users);            //var_dump($user_info);
             
            if (!empty($_GET['departmentid']) || !empty($_GET['user_realname']) || !empty($_GET['time_limit']) || !empty($_GET['begindate']) || !empty($_GET['borrow_type']) || $borrow_type === '0' || !empty($_GET['customer'])) {
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
            set_time_limit(90);
            ini_set('memory_limit', '1024M');
            $sales = array();
            foreach ($salesRes as $r) {
                $r['department_name']   = $user_info[$r['user_id']]['department_name'];
                $r['manager']           = $user_info[$r['user_id']]['manager'];
                $r['city_header']       = $user_info[$r['user_id']]['city_header'];
                $r['city_department']   = $user_info[$r['user_id']]['city_department'];
                $r['area_header']       = $user_info[$r['user_id']]['area_header'];
                $r['area_department']   = $user_info[$r['user_id']]['area_department'];
                //标期特殊处理
                if ($r['unit'] == 1 && $r['time_limit'] == 15) {
                    $r['time_limit'] = 0.5;
                }
                $sales[] = $r;
            }
        } else {  //其他员工显示自己的
            $user_info = $this->users_obj->getUsersInfo(session('hyd_id'));
            
            $where = array();
            $where['user_id'] = array('EQ', session('ADMIN_ID'));
            $s_where = array_merge_recursive($where, $s_where);

            if (!empty($_GET['time_limit']) || !empty($_GET['begindate']) || !empty($_GET['borrow_type']) || $borrow_type === '0' || !empty($_GET['customer'])) {
                $pwhere = $s_where;
            } else {
                $pwhere = $where;
            }
            
            $count = $this->sales_obj->getAppointCount($Model, $pwhere);
            //自定义分页
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            foreach ($pwhere as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . '&';
            }

            $salesRes = $this->sales_obj->getAppointList($Model, $pwhere, $page->firstRow, $page->listRows);
            //导出Excel
            if ($_POST['explode'] == '1') { 
                $salesRes = $this->sales_obj->getAppointList($Model, $pwhere, 0, $count);
            }
            
            $sales = array();
            foreach ($salesRes as $r) {
                $r['department_name']   = $user_info['department_name'];
                $r['manager']           = $user_info['manager'];
                $r['city_header']       = $user_info['city_header'];
                $r['city_department']   = $user_info['city_department'];
                $r['area_header']       = $user_info['area_header'];
                $r['area_department']   = $user_info['area_department'];
                //标期特殊处理
                if ($r['unit'] == 1 && $r['time_limit'] == 15) {
                    $r['time_limit'] = 0.5;
                }
                $sales[] = $r;
            }
        }
        
        //导出Excel
        if ($_POST['explode'] == '1') {
            $this->sales_obj->sales_export($sales);
        }

        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('sales', $sales);
        $this->display();
    }
    
    //同步所有数据，谨慎操作，时间较长。
    function super_data(){
        //清空数据库表
        M()->execute("truncate " . C('DB_PREFIX') . 'hyd_investment');
        $this ->_set_super_data();
    }
    
    //更新同步前一天数据
    function super_data_date(){
        $starttime = strtotime(date('Y-m-d',strtotime('-1 day')));
        $endtime = strtotime(date('Y-m-d'));
        $this ->_set_super_data($starttime,$endtime);
    }
    
    private function _set_super_data($starttime=null,$endtime=null) {        
        //echo "123";&per=27&page=0&session_uid=39537&starttime=1030373952
        $result_users = $this->users_obj
                ->where("id<>1")
                ->getField("id,hyd_id,departmentid");
        //var_dump($result_users);

        foreach ($result_users as $key => $value) { //var_dump($value);
            //员工数据插入
            $this->_set_hyd_investment($key, $value['hyd_id'], $value['departmentid'],$starttime,$endtime);

            //员工推荐的客户数据插入
            $customers = $this->_get_referee_customer($value['hyd_id']);
            if (count($customers) > 0) {
                foreach ($customers as $k => $v) {
                    $this->_set_hyd_investment($key, $v, $value['departmentid'],$starttime,$endtime);
                }
            }
        }
        
    }

    /*
     * 设置投资数据
     */

    private function _set_hyd_investment($user_id, $hyd_id, $departmentid, $starttime=null, $endtime=null) {
        $hyd_model = M('hydInvestment');
        $url = C('INTERFACE_URL');
        $data = array();
        $data['module'] = 'oa';
        $data['q'] = 'gettenderlistOA';
        $data['session_uid'] = trim($hyd_id);
        $data['starttime'] = $starttime;
        $data['endtime'] = $endtime;
        $data['per'] = 100; //设置当前返回量
        $data['page'] = 1; //当前页
        $res = POST_Api($url, $data);
        $resArr = decodeUrlArr($res);
        if ($resArr['error'] == '0') {
            $hyd_arr = $resArr['data']['list'];
            $total = $resArr['data']['total']; //总数
            $total_page = $resArr['data']['total_page'];
            //var_dump($hyd_arr);
            if (count($hyd_arr) > 0) {
                $this->_add_hyd_investment($user_id, $hyd_id, $departmentid, $hyd_arr);
            }

            if ($total > $data['per']) {
                $remain = $total - $data['per'];
                $times = ceil($remain / $data['per']) + 1; //echo $times;
                for ($i = 2; $i <= $times; $i++) {
                    $temp_data = array();
                    $temp_data = $this->_get_hyd_investment('gettenderlistOA', $hyd_id, $data['per'], $i);
                    $this->_add_hyd_investment($user_id, $hyd_id, $departmentid, $temp_data);
                }
            }
        }
    }

    /*
     * 员工推荐的客户
     */

    private function _get_referee_customer($hyd_id) {
        $url = C('INTERFACE_URL');
        $data = array();
        $data['module'] = 'oa';
        $data['q'] = 'getSpreadsId';
        $data['session_uid'] = trim($hyd_id);
        $res = POST_Api($url, $data);
        $resArr = decodeUrlArr($res);
        if ($resArr['error'] == '0') {
            return $resArr['data']['list'];
        }
    }

    /*
     * 获取投资数据
     */

    private function _get_hyd_investment($q, $hyd_id, $per, $page) {
        $url = C('INTERFACE_URL');
        $data = array();
        $data['module'] = 'oa';
        $data['q'] = $q;
        //$data['q']                      = 'gettenderlistOA';
        $data['session_uid'] = trim($hyd_id);
        $data['per'] = $per; //设置当前返回量
        $data['page'] = $page; //当前页
        $res = POST_Api($url, $data);
        $resArr = decodeUrlArr($res);
        if ($resArr['error'] == '0') {
            return $resArr['data']['list'];
        }
    }

    //oa数据库插入
    private function _add_hyd_investment($user_id, $hyd_id, $departmentid, $data) {
        $hyd_model = M('hydInvestment');

        foreach ($data as $k => $v) {
            //"unit"=>$v['borrow_time_name'],//天,月,年
            //"user_id"=>$v['borrow_apr'],//利率
            if (trim($v['borrow_time_name']) == '天') {
                $v['borrow_time_name'] = 1;
            } else if (trim($v['borrow_time_name']) == '月') {
                $v['borrow_time_name'] = 2;
            } else if (trim($v['borrow_time_name']) == '年') {
                $v['borrow_time_name'] = 3;
            }
            if ($v['success_time'] == '') {
                continue;
            }
            
            $result = array();
            $result = $hyd_model->field('id')
                ->where(array("nid" => $v['nid']))
                ->select();
            if(count($result)>0){
                continue;
            }
            $hyd_model->add(array("user_id" => $user_id,
                "hyd_id" => $hyd_id,
                "departmentid" => $departmentid,
                "success_time" => $v['success_time'],
                "customer" => $v['truename'],
                "username" => $v['username'],
                "borrow_nid" => $v['borrow_nid'],
                "borrow_name" => $v['borrow_name'],
                "frozen_time" => $v['frozen_time'],
                "recover_time" => $v['recover_time'],
                "unit" => $v['borrow_time_name'],
                "time_limit" => $v['borrow_period_name'],
                "borrow_money" => $v['account'],
                "borrow_type" => $v['type'],
                "borrow_apr" => $v['borrow_apr'],
                "nid" => $v['nid']
            ));
            
        }
    }

    /*
     * 获取无主单
     */

    private function _get_hyd_nomaster() {
        $userid = json_encode($this->_get_hyd_userid());
        $url = C('INTERFACE_URL');
        $data = array();
        $data['module'] = 'oa';
        $data['q'] = 'gettenderOA';
        $data['session_uid'] = $userid;
        $data['per'] = 10; //设置当前返回量
        $data['page'] = 1; //当前页
        $res = POST_Api($url, $data);
        $resArr = decodeUrlArr($res);
        if ($resArr['error'] == '0') {
            $hyd_arr = $resArr['data']['list'];
            $total = $resArr['data']['total']; //总数
            $total_page = $resArr['data']['total_page'];
            //var_dump($hyd_arr);
            if (count($hyd_arr) > 0) {
                $this->_add_hyd_investment(0, 0, 0, $hyd_arr);
            }

            if ($total > $data['per']) {
                $remain = $total - $data['per'];
                $times = ceil($remain / $data['per']) + 1; //echo $times;
                for ($i = 2; $i <= $times; $i++) {
                    $temp_data = array();
                    $temp_data = $this->_get_hyd_investment('gettenderOA', $hyd_id, $data['per'], $i);
                    $this->_add_hyd_investment(0, $userid, 0, $temp_data);
                }
            }
        }
    }

    /*
     * 获取员工ID数组
     */

    private function _get_hyd_userid() {
        $users = $this->users_obj->field('hyd_id')
                ->where(array('user_status' => 'E'))
                ->where("hyd_id <> 0")
                ->select();

        $usersid = array();
        foreach ($users as $key => $value) {
            $usersid[] = $value['hyd_id'];
        }

        return $usersid;
    }

}
