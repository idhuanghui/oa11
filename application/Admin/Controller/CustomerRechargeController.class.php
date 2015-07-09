<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

/**
 * 充值查询
 * Class CustomerRechargeController
 * @package Admin\Controller
 */
class CustomerRechargeController extends AdminbaseController {
    protected $users_obj,$department_obj;

    function _initialize(){
        parent::_initialize();
        $this->users_obj = D("Common/Users");
        $this->department_obj = D("Common/Department");

    }

    function index(){
        //分类树,搜索时使用
        $result = $this->department_obj->field('id,parentid,name')->order(array("id" => "ASC"))->select();
        $select_categorys = json_encode($result);
        $this->assign("select_categorys", $select_categorys);

        //搜索开始
        $_GET['departmentid'] = $department = $_REQUEST['departmentid'];
        $_GET['departmentname'] = I('post.departmentname');
        $_GET['user_realname'] = $realname = $_REQUEST['user_realname'];  //员工

        $_GET['customer'] = $customer = $_REQUEST['customer'];
        $_GET['begindate'] = $begindate = $_REQUEST['begindate'];
        $_GET['enddate'] = $enddate = $_REQUEST['enddate'];

        $s_where = array();
        if (!empty($department)) {
            $s_where['departmentid'] = array('in', $department);
        }
        if (!empty($realname)) {
            $s_where['user_realname'] = array('like', '%' . $realname . '%');
        }

        if (!empty($customer)) {
            $s_where['customer'] = array('like', '%' . $customer . '%');
        }
        
        if (session('ADMIN_ID') == 1 || session('roid') == '2') { //超级管理员显示所有的
            if (!empty($_GET['departmentid']) || !empty($_GET['user_realname'])) {
                $pwhere = $s_where;
            } else {
                $pwhere = '1=1';
            }
            $result_users = $this->users_obj->field('hyd_id')
                ->where($pwhere)
                ->where("hyd_id <> 0")
                ->select();
            $userInfo = $this->users_obj->getUsersInfo($result_users);

            $hyd_id='';
            foreach ($result_users as $k=>$v){
                $hyd_id .= $v['hyd_id'].',';
            }

            $hyd_ids = substr($hyd_id, 0, -1);       //舍弃最后一个","
            //自定义分页
            $p = $_GET['p']?$_GET['p']:1;
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);

            $rechargesRes = customer_inquiry(C('INTERFACE_URL_CZ'),$hyd_ids,$page_size,$p,$customer,$begindate,$enddate);

            $count = $rechargesRes['record_count']?$rechargesRes['record_count']:0;
            $page = $this->page($count, $page_size);

            foreach ($rechargesRes['list'] as $r) {

                $r['user_realname']     = $userInfo[$r['pid']]['user_realname'];
                $r['department_name']   = $userInfo[$r['pid']]['department_name'];
                $r['city_department']   = $userInfo[$r['pid']]['city_department'];
                $recharges[] = $r;
            }
            $_GET['page_size'] = $page_size;

        } else {  //其他员工显示自己的            
            $hyd_id = session('hyd_id');
            $userInfo = $this->users_obj->getUsersInfo($hyd_id);

            $p = $_GET['p']?$_GET['p']:1;
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $recharge = customer_inquiry(C('INTERFACE_URL_CZ'),$hyd_id,$page_size,$p,$customer,$begindate,$enddate);
            $count = $recharge['record_count'];

            $page = $this->page($count, $page_size);

            foreach ($recharge['list'] as $r) {
                $r['user_realname']     = $userInfo['user_realname'];
                $r['department_name']   = $userInfo['department_name'];
                $r['city_department']   = $userInfo['city_department'];
                $recharges[] = $r;
            }
            $_GET['page_size'] = $page_size;

        }
        
        //导出Excel
        if ($_POST['explode'] == '1') {
            set_time_limit(120);
            ini_set('memory_limit', '2048M');

            $customerRes = customer_inquiry(C('INTERFACE_URL_CZ'),$hyd_id,$count,1,$customer,$begindate,$enddate);
            if(session('ADMIN_ID') == 1 || session('roid') == '2'){
                foreach ($customerRes['list'] as $r) {
                    $r['area_header']   	= $userInfo[$r['pid']]['area_header'];
                    $r['city_header']     	= $userInfo[$r['pid']]['city_header'];
                    $r['manager_name']     	= $userInfo[$r['pid']]['manager'];
                    $r['area_department']   = $userInfo[$r['pid']]['area_department'];
                    $r['city_department']   = $userInfo[$r['pid']]['city_department'];
                    $r['department_name']   = $userInfo[$r['pid']]['department_name'];
                    $r['user_realname']   	= $userInfo[$r['pid']]['user_realname'];
                    $r['userid']   	    = 'HYD'.str_pad($userInfo[$r['pid']]['uid'],5,0,STR_PAD_LEFT);
                    $customerRecharges[] = $r;
                }
            }else{
                foreach ($customerRes['list'] as $r) {
                    $r['area_header']   	= $userInfo['area_header'];
                    $r['city_header']     	= $userInfo['city_header'];
                    $r['manager_name']     	= $userInfo['manager'];
                    $r['area_department']   = $userInfo['area_department'];
                    $r['city_department']   = $userInfo['city_department'];
                    $r['department_name']   = $userInfo['department_name'];
                    $r['user_realname']   	= $userInfo['user_realname'];
                    $r['userid']   	        = 'HYD'.str_pad(session('ADMIN_ID'),5,0,STR_PAD_LEFT);
                    $customerRecharges[] = $r;
                }
            }

            $Excel = A('Excel');
            $Excel->to_CustomerRecharge($customerRecharges);
        }

        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('recharges', $recharges);
        $this->display();
    }

}
?>