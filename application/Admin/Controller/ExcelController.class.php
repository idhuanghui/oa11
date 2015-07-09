<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use Think\Model;

/**
 * 导出Excel表格
 * Class ExamineController
 * @package Admin\Controller
 *
 */
class ExcelController extends AdminbaseController{
    protected $users_obj,$role_obj,$position_obj,$department_obj,$user_entry_obj,$excel_obj;
    function _initialize() {
        parent::_initialize();
        $this->users_obj        = D("Common/Users");
        $this->role_obj         = D("Common/Role");
        $this->position_obj     = D("Common/Position");
        $this->department_obj   = D("Common/Department");
        $this->user_entry_obj   = D("Common/UserEntry");
        $this->excel_obj        = D("Common/ExcelView");

    }

    //导出员工Excel
    function to_Excel($users){
        $this->users_export($users);
    }

	//导出普通用户
	private function users_export($users=array())
	{
		$users_list = $users;
		$data = array();
		foreach ($users_list as $k=>$users_info){
			$data[$k][]		= 'HYD'.str_pad($users_info['id'],5,0,STR_PAD_LEFT);
			$data[$k][]		= $users_info['area_department'];
			$data[$k][]		= $users_info['area_header'];
			$data[$k][]		= $users_info['city_department'];
			$data[$k][]		= $users_info['city_header'];
			$data[$k][] 	= $users_info['cc_manager'];
			$data[$k][]		= $users_info['dname'];
			$data[$k][] 	= $this->findLeader($users_info['departmentid']);		//部门负责人
			$data[$k][]		= $this->deparmentCount($users_info['departmentid']);	//部门总人数
			$data[$k][]		= $users_info['user_realname'];
			$data[$k][]		= $users_info['sex'] == 1 ? '男' : '女';
			$data[$k][]		= $users_info['qq'];
			$data[$k][]		= $users_info['level'] == 1 ? '主管' : '员工';
//            $data[$k][]	= $users_info[''];
			$data[$k][]		= $users_info['user_login'];
			$data[$k][]		= $users_info['idcard'];
			$data[$k][]		= $users_info['reference'];
			$data[$k][]		= $users_info['acc_province'];
//            $data[$k][]	= $users_info[''];
			$data[$k][]		= $users_info['payroll_try'];
			$data[$k][]		= $users_info['acc_address'];
			$data[$k][]		= $users_info['education'];
			$data[$k][]		= $users_info['specialty'];
			$data[$k][]		= $users_info['bank_user'];
			$data[$k][]		= $users_info['bank_address'];
			$data[$k][]		= $users_info['bank_num'];
			$data[$k][]		= $users_info['user_email'];
			$data[$k][]		= $users_info['entrydate'];
			$data[$k][]		= $users_info['temporary'] == 1 ? '兼职' : '全职';
			$data[$k][]		= $users_info['hyd_name'];
//            $data[$k][]	= $users_info[''];
//            $data[$k][]	= $users_info[''];
		}
			$headArr = array();
			$headArr[]	=	'员工编号';
			$headArr[]	=	'一级分部';
			$headArr[]	=	'区域经理';
			$headArr[]	=	'二级分部';
			$headArr[]	=	'城市经理';
			$headArr[]	=	'督导';
			$headArr[]	=	'入职团队/部门名称';
			$headArr[]	=	'团队/部门 负责人';
			$headArr[]	=	'团队/部门现有人数';
			$headArr[]	=	'姓名';
			$headArr[]	=	'性别';
			$headArr[]	=	'年龄';
			$headArr[]	=	'角色';
	//      $headArr[]	=	'岗位名称';
			$headArr[]	=	'手机';
			$headArr[]	=	'员工身份证号';
			$headArr[]	=	'入职推荐人';
			$headArr[]	=	'社保归属地';
	//      $headArr[]	=	'入职公司';
			$headArr[]	=	'岗位工薪';
			$headArr[]	=	'户口所在地';
			$headArr[]	=	'学历';
			$headArr[]	=	'专业';
			$headArr[]	=	'开户姓名';
			$headArr[]	=	'开户银行名称';
			$headArr[]	=	'开户人银行账号';
			$headArr[]	=	'邮箱';
			$headArr[]	=	'入职日期';
			$headArr[]	=	'是否兼职';
			$headArr[]	=	'网站用户名';

		$filename="员工列表";
		$this->excel_obj->getExcel($filename,$headArr,$data);
	}

    //导出离职员工
    function levalExcel($users){
        $this->leave_users_export($users);
    }

    //导出离职员工信息
    private function leave_users_export($users=array())
    {
        $users_list = $users;
        $data = array();
        foreach ($users_list as $k=>$users_info){
            $data[$k][id]				= 'HYD'.strPad($users_info['id']);
            $data[$k][area_department]	= $users_info['area_department'];   //总部不显示先
            $data[$k][denames]	        = $users_info['city_department'];
            $data[$k][header]	        = $users_info['header'];
            $data[$k][manager]	        = $users_info['cc_manager'];
            $data[$k][dname]	        = $users_info['dname'];
            $data[$k][idcard]	        = $users_info['idcard'];
            $data[$k][user_realname]	= $users_info['user_realname'];
            $data[$k][sex]	            = $users_info['sex'] == 1 ? '男' : '女';
            $data[$k][qq]	            = $users_info['qq'];
            $data[$k][mobile]	        = $users_info['mobile'];
            $data[$k][acc_province]	    = $users_info['acc_province'];
            $data[$k][entrydate]	    = $users_info['entrydate'];
            $data[$k][leave_time]       = $users_info['leave_time'];
            $data[$k][end_time]	        = $users_info['end_time'];
            $data[$k][with_remark]		= $users_info['with_remark'];
			$data[$k][oprocess]			= userExportStatus($users_info['oprocess']);
            $data[$k][hyd_name]	        = $users_info['hyd_name'];
        }
			$headArr = array();
			$headArr[]	=	'员工编号';
			$headArr[]	=	'分公司';
			$headArr[]	=	'分部';
			$headArr[]	=	'城市经理';
			$headArr[]	=	'城市督导';
			$headArr[]	=	'入职 团队/部门 名称';
			$headArr[]	=	'员工身份证号';
			$headArr[]	=	'姓名';
			$headArr[]	=	'性别';
			$headArr[]	=	'年龄';
			$headArr[]	=	'手机';
			$headArr[]	=	'社保归属地';
			$headArr[]	=	'入职日期';
			$headArr[]	=	'离职日期';
			$headArr[]	=	'工薪截止日期';
			$headArr[]	=	'离职原因';
			$headArr[]	=	'离职状态';
			$headArr[]	=	'网站用户名';
		/**foreach ($data as $field=>$v){
            if($field == 'id') 				$headArr[]='员工编号';
            if($field == 'user_login') 		$headArr[]='分公司';
            if($field == 'user_nicename') 	$headArr[]='分部';
            if($field == 'user_email') 		$headArr[]='城市经理';
            if($field == 'birthday') 		$headArr[]='城市督导';
            if($field == 'dname') 			$headArr[]='入职 团队/部门 名称';
            if($field == 'idcard') 			$headArr[]='员工身份证号';
            if($field == 'user_realname') 	$headArr[]='姓名';
            if($field == 'sex') 			$headArr[]='性别';
            if($field == 'qq')				$headArr[]='年龄';
            if($field == 'mobile') 			$headArr[]='手机';
            if($field == 'acc_province')	$headArr[]='社保归属地';
            if($field == 'entrydate')		$headArr[]='入职日期';
			if($field == 'leave_time') 		$headArr[]='离职日期';
            if($field == 'end_time')		$headArr[]='工薪截止日期';
            if($field == 'with_remark') 	$headArr[]='离职原因';
			if($field == 'oprocess')		$headArr[]='离职状态';
            if($field == 'hyd_name')		$headArr[]='网站用户名';
        }*/
        $filename="离职员工列表";

        $this->excel_obj->getLeavesExcel($filename,$headArr,$data);
    }

	//导出客户
	public function  to_Customer($customer){
		$this->customer_export($customer);
	}

	private function customer_export($customer = array()){
		$customer_list = $customer;
		$data = array();
		foreach($customer_list as $k=>$customer_info){
			$data[$k][reg_time] 		= date("Y-m-d H:i:s",$customer_info['reg_time']);
			$data[$k][area_header] 		= $customer_info['area_header'];
			$data[$k][city_header] 		= $customer_info['city_header'];
			$data[$k][manager_name] 	= $customer_info['manager_name'];
			$data[$k][area_department] 	= $customer_info['area_department'];
			$data[$k][city_department] 	= $customer_info['city_department'];
			$data[$k][department_name] 	= $customer_info['department_name'];
			$data[$k][truename] 	    = $customer_info['truename'];
			$data[$k][username]         = $customer_info['username'];
			$data[$k][user_id]          = $customer_info['user_id'];
			$data[$k][sex]              = $customer_info['sex'];
			$data[$k][age]              = $customer_info['age'];
			$data[$k][birthday]         = $customer_info['birthday'];
			$data[$k][mobile]           = $customer_info['mobile'];
			$data[$k][balance]          = $customer_info['balance'];
			$data[$k][dhze]             = $customer_info['dhze'];
			$data[$k][ayes]             = $customer_info['ayes'];
			$data[$k][money_count]      = $customer_info['money_count'];
			$data[$k][account_all]      = $customer_info['account_all'];
			$data[$k][total]            = $customer_info['total'];
            $data[$k][user_realname] 	= $customer_info['user_realname'];
			$data[$k][userid]           = $customer_info['userid'];
		}

		$headerArr = array();
		$headerArr[] = '注册时间';
		$headerArr[] = '区域经理';
		$headerArr[] = '城市经理';
		$headerArr[] = '督导';
		$headerArr[] = '一级分部';
		$headerArr[] = '二级分部';
		$headerArr[] = '部门名称';
		$headerArr[] = '客户姓名';
		$headerArr[] = '客户用户名';
		$headerArr[] = '客户ID';
		$headerArr[] = '性别';
		$headerArr[] = '年龄';
		$headerArr[] = '生日';
		$headerArr[] = '联系电话';
		$headerArr[] = '当前可用余额';
		$headerArr[] = '当前待还金额';
		$headerArr[] = '累积还款金额';
		$headerArr[] = '累积充值金额';
		$headerArr[] = '累积投资金额';
		$headerArr[] = '累积提现金额';
		$headerArr[] = '员工';
		$headerArr[] = '员工编号';

		$filename="客户查询";

		$this->excel_obj->getCustomerExcel($filename,$headerArr,$data);
	}

    /**
     * 客户充值记录查询
     * @param $customerRecharges
     */
    public function to_CustomerRecharge($customerRecharges){
        $this->CustomerRecharge_export($customerRecharges);
    }

    private function CustomerRecharge_export($customerRecharges = array()){
        $customerRecharge_list = $customerRecharges;
        $data = array();
        foreach($customerRecharge_list as $k=>$customerRecharge_info){
            $data[$k][create_time]      = date('Y-m-d H:i:s',$customerRecharge_info['create_time']);
            $data[$k][area_header]      = $customerRecharge_info['area_header'];
            $data[$k][city_header]      = $customerRecharge_info['city_header'];
            $data[$k][manager_name]     = $customerRecharge_info['manager_name'];
            $data[$k][area_department]  = $customerRecharge_info['area_department'];
            $data[$k][city_department]  = $customerRecharge_info['city_department'];
            $data[$k][department_name]  = $customerRecharge_info['department_name'];
            $data[$k][truename]         = $customerRecharge_info['truename'];
            $data[$k][username]         = $customerRecharge_info['username'];
            $data[$k][user_id]          = $customerRecharge_info['user_id'];
            $data[$k][reg_time]         = date('Y-m-d H:i:s',$customerRecharge_info['reg_time']);
            $data[$k][gate_type]        = $customerRecharge_info['gate_type'];
            $data[$k][bank]             = $customerRecharge_info['bank'];
            $data[$k][money]            = $customerRecharge_info['money'];
            $data[$k][fee]              = $customerRecharge_info['fee'];
            $data[$k][balance]          = $customerRecharge_info['balance'];
            $data[$k][user_realname]    = $customerRecharge_info['user_realname'];
            $data[$k][userid]           = $customerRecharge_info['userid'];
        }

        $headerArr = array();
        $headerArr[] = '充值时间';
        $headerArr[] = '区域经理';
        $headerArr[] = '城市经理';
        $headerArr[] = '督导';
        $headerArr[] = '一级分部';
        $headerArr[] = '二级分部';
        $headerArr[] = '部门名称';
        $headerArr[] = '客户姓名';
        $headerArr[] = '客户用户名';
        $headerArr[] = '客户ID';
        $headerArr[] = '注册时间';
        $headerArr[] = '充值类型';
        $headerArr[] = '充值银行';
        $headerArr[] = '操作金额';
        $headerArr[] = '手续费扣除';
        $headerArr[] = '到账金额';
        $headerArr[] = '员工';
        $headerArr[] = '员工编号';

        $filename="充值查询";

        $this->excel_obj->getCustomerRecharge($filename,$headerArr,$data);
    }

    /**
     * 投资统计导出
     * @param $countInvestment
     */
    public function to_CountInvestment($countInvestment){
        $this->CountInvestment_export($countInvestment);
    }

    private function CountInvestment_export($countInvestment = array()){
        $countInvestment_list = $countInvestment;
        $data = array();

        foreach($countInvestment_list as $k=>$countInvestment_info){
//            $data[$k][success_time]     = date('Y-m-d H:i:s',$countInvestment_info['success_time']);
            $data[$k][success_time]     = $countInvestment_info['successtime'];
            $data[$k][area_header]      = $countInvestment_info['area_header'];
            $data[$k][city_header]      = $countInvestment_info['city_header'];
            $data[$k][manager_name]     = $countInvestment_info['manager_name'];
            $data[$k][area_department]  = $countInvestment_info['area_department'];
            $data[$k][city_department]  = $countInvestment_info['city_department'];
            $data[$k][department_name]  = $countInvestment_info['department_name'];
            $data[$k][user_realname]    = $countInvestment_info['user_realname'];
            $data[$k][user_id]          = 'HYD'.str_pad($countInvestment_info['usid'],5,0,STR_PAD_LEFT);
            $data[$k][poname]           = $countInvestment_info['poname'];
            $data[$k][temporary]        = $countInvestment_info['temporary'] == 1 ? '兼职' : '全职';
            $data[$k][idcard]           = $countInvestment_info['idcard'];
            $data[$k][entrydate]        = $countInvestment_info['entrydate'];
            $data[$k][leave_time]        = $countInvestment_info['leave_time'];
            $data[$k][time_limit]        = $countInvestment_info['time_limit'];
            $data[$k][borrow_money]        = $countInvestment_info['type_sum'];
        }

        $headerArr = array();
        $headerArr[] = '投资日期';
        $headerArr[] = '区域经理';
        $headerArr[] = '城市经理';
        $headerArr[] = '督导';
        $headerArr[] = '一级分部';
        $headerArr[] = '二级分部';
        $headerArr[] = '部门名称';
        $headerArr[] = '员工';
        $headerArr[] = '员工编号';
        $headerArr[] = '岗位名称';
        $headerArr[] = '是否兼职';
        $headerArr[] = '身份证号';
        $headerArr[] = '入职日期';
        $headerArr[] = '离职日期';
        $headerArr[] = '期限';
        $headerArr[] = '投标金额';

        $filename="投资统计";

        $this->excel_obj->getCountInvestment($filename,$headerArr,$data);
    }

	/**
	 * 查找所属部门的leader, 分公司人事的leader为总部人事的leader
	 * @param $id		所属部门ID
	 * @return mixed
	 */
	function findLeader($id){
		$Model = M('Users');
		$where['departmentid'] 	= $id;
		$where['level']			= 1;
		$staff = $Model->field('id,user_realname,departmentid')->where($where)->find();		//普通用户查找leader
		return $staff['user_realname'];
	}

	//统计部门人数
	function deparmentCount($id){
		$Model = M('Users');
		$where['departmentid'] 	= $id;
		$count = $Model->where($where)->count();
		return $count;
	}


}