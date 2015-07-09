<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

/**
 * 还款查询
 * 非管理员仅看个人
 * Class ClientsRepayController
 * @package Admin\Controller
 */
class CustomerRepayController extends AdminbaseController {

    protected $users_obj;

    function _initialize() {
        parent::_initialize();
        $this->users_obj = D("Common/Users");
        $this->department_obj = D("Common/Department");
        //$this->sales_obj = D("Common/ClientsRepay");
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
            //var_dump($result_users);
            $user_info = $this->users_obj->getUsersInfo($result_users);
            
            $users_id='';
            foreach ($result_users as $k=>$v){
                $users_id .= $v['hyd_id'].',';
            }
            
            $sales = array();
            //自定义分页
            $p = $_GET['p']?$_GET['p']:1;
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
           
            $salesRes = customer_inquiry(C('INTERFACE_URL_CR'),$users_id,$page_size,$p,$customer,$begindate,$enddate);       //var_dump($users_id);   var_dump($salesRes);
            $count = $salesRes['record_count']?$salesRes['record_count']:0;
            $page = $this->page($count, $page_size);
            
            set_time_limit(90);
            ini_set('memory_limit', '1024M');
            //导出Excel
            if ($_POST['explode'] == '1') {
                $salesRes = array();
                $salesRes = customer_inquiry(C('INTERFACE_URL_CR'),$users_id,1000,1,$customer,$begindate,$enddate);        //var_dump($salesRes);
            }
            foreach ($salesRes['list'] as $r) {
                //print_r($user_info);
                $r['user_realname']     = $user_info[$r['pid']]['user_realname'];
                $r['department_name']   = $user_info[$r['pid']]['department_name'];
                $r['city_department']   = $user_info[$r['pid']]['city_department'];
                $r['area_department']   = $user_info[$r['pid']]['area_department'];
                $r['manager']           = $user_info[$r['pid']]['manager'];
                $r['city_header']       = $user_info[$r['pid']]['city_header'];
                $r['area_header']       = $user_info[$r['pid']]['area_header'];
                $r['userid']   	        = 'HYD'.str_pad($user_info[$r['pid']]['uid'],5,0,STR_PAD_LEFT);
                //标期特殊处理
                if ($r['unit'] == 1 && $r['time_limit'] == 15) {
                    $r['time_limit'] = 0.5;
                }
                $sales[] = $r;
            }
            $_GET['page_size'] = $page_size; 
            
        } else {  //其他员工显示自己的            
            $user_info = $this->users_obj->getUsersInfo(session('hyd_id'));
            //print_r($user_info);
            
            $sales = array();
            $salesRes = array();
            
            //自定义分页
            $p = $_GET['p']?$_GET['p']:1;
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            
            $salesRes = customer_inquiry(C('INTERFACE_URL_CR'),session('hyd_id'),$page_size,$p,$customer,$begindate,$enddate);        //var_dump($salesRes);
            $count = $salesRes['record_count'];
            $page = $this->page($count, $page_size);
            
            //导出Excel
            if ($_POST['explode'] == '1') {
                $salesRes = array();
                $salesRes = customer_inquiry(C('INTERFACE_URL_CR'),session('hyd_id'),$count,1,$customer,$begindate,$enddate);        //var_dump($salesRes);
            }
            foreach ($salesRes['list'] as $r) {
                $r['user_realname']     = $user_info['user_realname'];
                $r['department_name']   = $user_info['department_name'];
                $r['city_department']   = $user_info['city_department'];
                $r['area_department']   = $user_info['area_department'];
                $r['manager']           = $user_info['manager'];
                $r['city_header']       = $user_info['city_header'];
                $r['area_header']       = $user_info['area_header'];
                $r['userid']   	        = 'HYD'.str_pad($user_info[$r['pid']]['uid'],5,0,STR_PAD_LEFT);
                //标期特殊处理
                if ($r['unit'] == 1 && $r['time_limit'] == 15) {
                    $r['time_limit'] = 0.5;
                }
                $sales[] = $r;
            }
            $_GET['page_size'] = $page_size; 
            
        }
        
        //导出Excel
        if ($_POST['explode'] == '1') {
            $this -> _sales_export($sales);
        }
        

        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('sales', $sales);
        $this->display();
    }

    private function _sales_export($sales = array()){
            $data = array();
            foreach ($sales as $k=>$v){
                    $data[$k]['success_time']	= date('Y-m-d H:i:s', $v['repay_last_time']); //还款日期
                    $data[$k]['area_header']	= $v['area_header']; //区域经理
                    $data[$k]["city_header"]	= $v['city_header']; //城市经理
                    $data[$k]['manager']	= $v['manager'];   //督导
                    $data[$k]['area_department']	= $v['area_department'];//一级分部
                    
                    $data[$k]['city_department']	= $v['city_department'];//二级分部
                    $data[$k]['department_name']	= $v['department_name'];//部门名称
                    $data[$k]['truename']	= $v['truename'];//客户姓名
                    $data[$k]['username']	= $v['username'];//客户用户名 
                    $data[$k]['user_id']	= $v['user_id'];//客户ID 
                    
                    $data[$k]['reg_time']	= date('Y-m-d H:i:s', $v['reg_time']);//注册时间
                    $data[$k]['borrow_nid']	= $v['borrow_nid'];//标的编号
                    $data[$k]['name']	= $v['name'];//标的名称
                    $data[$k]['project_type']	= $v['project_type'];//标的类型
                    $data[$k]['recover_account_sum']	= $v['recover_account_sum'];//待还金额
                    
                    $data[$k]['wait_capital']	= $v['wait_capital'];//待还本金
                    $data[$k]['recover_interest']	= $v['recover_interest'];//待还利息
                    $data[$k]['user_realname']	= $v['user_realname'];//员工
                    $data[$k]['userid']	= $v['userid'];//员工编号
            }
            $headArr = array();
            $headArr[]= '还款日期';
            $headArr[]='区域经理';
            $headArr[]='城市经理';
            $headArr[]='督导';
            $headArr[]='一级分部';
            
            $headArr[]='二级分部';
            $headArr[]='部门名称';
            $headArr[]='客户姓名';
            $headArr[]='客户用户名';
            $headArr[]='客户ID';

            $headArr[]='注册时间';
            $headArr[]='标的编号';
            $headArr[]='标的名称';
            $headArr[]='标的类型';
            $headArr[]='待还金额';

            $headArr[]='待还本金';
            $headArr[]='待还利息';
            $headArr[]='员工';
            $headArr[]='员工编号';
      
            $this->_getExcel($filename,$headArr,$data);
    }
    private function _getExcel($fileName,$headArr,$data){

        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Y_m_d",time());
        $fileName .= "还款查询_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        //print_r($headArr);exit;
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        //合并单元格
        //$objPHPExcel->getActiveSheet()->mergeCells('A18:E22');

        //设置单元格的宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(45);  
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);

        //print_r($data);exit;
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }
}
