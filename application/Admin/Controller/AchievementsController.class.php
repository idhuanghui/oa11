<?php
/**
 * 绩效统计控制器。
 * User: yan
 * Date: 2015/6/26
 * Time: 9:14
 */

namespace Admin\Controller;
use Common\Controller\AdminbaseController;

class AchievementsController extends AdminbaseController {
    protected $achievements_obj,$department_obj,$users_obj;

    function _initialize(){
        parent::_initialize();
        $this->users_obj = D("Common/Users");
        $this->achievements_obj = D("Common/Achievements");
        $this->department_obj = D("Common/Department");
    }

    public function index(){

        //分类树,搜索时使用
        $result_all = $this->department_obj->field('id,parentid,name')->order(array("id" => "ASC"))->select();

        $select_categorys = json_encode($result_all);
        $this->assign("select_categorys", $select_categorys);

        //搜索
        $_GET['departmentid']   = $department   = $_REQUEST['departmentid'];
        $_GET['departmentname'] = I('post.departmentname');
        $_GET['user_realname']  = $realname     = $_REQUEST['user_realname'];
        $_GET['taskyear']       = $taskyear     = $_REQUEST['taskyear'];
        $_GET['taskmonth']      = $taskmonth    = $_REQUEST['taskmonth'];

        $s_where = array();
        if (!empty($department)) {
            $s_where['departmentid'] = array('in', $department);
		}
		if(!empty($realname)){
			$s_where['user_realname'] = array('like','%'.$realname.'%');
		}
        if(!empty($taskyear)){
            $s_where['tasknum'] = array('EQ',$taskyear.$taskmonth);
        }

        $investment = M('HydInvestment');
        if(session('ADMIN_ID') == 1 || session('roid') == 2) {   //超级管理员或者是管理员

            //搜索条件
            if(!empty($_GET['departmentid']) || !empty($_GET['departmentname']) || !empty($_GET['taskyear']) || !empty($_GET['taskmonth'])){
                $where = $s_where;                      //搜索条件
                $sdate = $taskyear.'-'.$taskmonth;      //显示列表日期
                $nowTime = $taskyear.$taskmonth;        //当前日期
            }else{
                $where['tasknum'] = array('EQ',date('Ym',time()));  //默认无搜索条件
                $sdate = date('Y-m',time());                        //显示列表日期
                $nowTime = date('Ym',time());                       //默认当前时间
            }

            //查询员工的基本信息
            $result_users = $this->users_obj->field('id')
                ->where($where)
                ->where("id > 1")
                ->select();
            $userInfo = $this->users_obj->getBasicUsersInfo($result_users);

            //查询所有的折比系数
            $Task = M('Task');
            $taskdetails = $this->achievements_obj->getTaskRatio($Task,$nowTime);
            $taskdetail = array();
            foreach($taskdetails as $k => $v){      //折比系数遍历成以time_limit为下标的二维数组.
                $taskdetail[$v['time_limit']]['tkid']       = $taskdetails[$k]['tkid'];
                $taskdetail[$v['time_limit']]['disnum']     = $taskdetails[$k]['disnum'];
                $taskdetail[$v['time_limit']]['begintime']  = $taskdetails[$k]['begintime'];
                $taskdetail[$v['time_limit']]['endtime']    = $taskdetails[$k]['endtime'];
                $taskdetail[$v['time_limit']]['id']         = $taskdetails[$k]['id'];
                $taskdetail[$v['time_limit']]['discountid'] = $taskdetails[$k]['discountid'];
                $taskdetail[$v['time_limit']]['time_limit'] = $taskdetails[$k]['time_limit'];
                $taskdetail[$v['time_limit']]['ratio']      = $taskdetails[$k]['ratio'];
            }
            //查询任务起始时间
            $tasktime = $Task->where(array('tasknum'=>$nowTime))->find();
            $count = $this->achievements_obj->getUserInvestmentCount($this->users_obj, $where);

            //自定义分页
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            foreach ($where as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . '&';
            }

            //查询所有员工
            $users = $this->achievements_obj->getUserInvestment($this->users_obj,$where,$page->firstRow, $page->listRows);

            //查询所有的业绩信息
            $ach_where['success_time'] = array(array('EGT',$tasktime['begintime']),array('ELT',$tasktime['endtime']),'and');
            foreach($users as $k => $v){
                $res[] = $this->achievements_obj->getInvestment($investment,$v['id'],$ach_where);
            }

            //去除为空的字数组
            foreach($res as $rk => $rv){
                if(!empty($res[$rk])){
                    $investment_list[] = $rv;
                }
            }

            //计算折比业绩
            $ratios = array();
            $allRatios = array();
            foreach($investment_list as $ik => $iv ){
                $ratio = array();
                for($i = 0; $i < count($iv);$i++){
                    if($iv[$i]['time_limit'] == $taskdetail[$iv[$i]['time_limit']]['time_limit']){
                        $ratio['allRatios'] += $iv[$i]['borrow_money'];
                        $ratio['ratio'] += $iv[$i]['borrow_money'] * $taskdetail[$iv[$i]['time_limit']]['ratio'];

                    }else{
                        $ratio['allRatios'] +=  $iv[$i]['borrow_money'];
                        $ratio['ratio'] +=  $iv[$i]['borrow_money'] * 1;
                    }
                }
                $ratios[$iv[0]['user_id']] = $ratio;
            }

            //员工基本数组和折比业绩数组合并
            $userRatio = array();
            foreach($users as $k => $v){
                $userRatio[$k]['month']             = $sdate;
                $userRatio[$k]['id']                = $v['id'];
                $userRatio[$k]['hyd_id']            = $v['hyd_id'];
                $userRatio[$k]['user_realname']     = $v['user_realname'];
                $userRatio[$k]['departmentid']      = $v['departmentid'];
                $userRatio[$k]['temporary']         = $v['temporary'];
                $userRatio[$k]['area_header']       = $userInfo[$v['id']]['area_header'];
                $userRatio[$k]['city_header']       = $userInfo[$v['id']]['city_header'];
                $userRatio[$k]['manager']           = $userInfo[$v['id']]['manager'];
                $userRatio[$k]['area_department']   = $userInfo[$v['id']]['area_department'];
                $userRatio[$k]['city_department']   = $userInfo[$v['id']]['city_department'];
                $userRatio[$k]['department_name']   = $userInfo[$v['id']]['department_name'];
                $userRatio[$k]['reg_num']           = isset($v['reg_num']) ? $v['reg_num'] : 0;
                $userRatio[$k]['open_num']          = isset($v['open_num']) ? $v['open_num'] : 0;
                $userRatio[$k]['this_num1']         = isset($v['this_num1']) ? $v['this_num1'] : '0.00';
                $userRatio[$k]['this_num2']         = isset($v['this_num2']) ? $v['this_num2'] : '0.00';
                $userRatio[$k]['this_num3']         = isset($v['this_num3']) ? $v['this_num3'] : '0.00';
                $userRatio[$k]['ratio']             = isset($ratios[$v['id']]['ratio']) ? $ratios[$v['id']]['ratio'] : '0.00' ;
                $userRatio[$k]['allmoney']          = isset($ratios[$v['id']]['allRatios']) ? $ratios[$v['id']]['allRatios'] : '0.00' ;
            }
        }else{          //部门主管,督导,城市经理,区域经理.(按职权大小)
            $departments = $this->department_obj->getAllDepartments($this->department_obj,session('ADMIN_ID'));
            $this->assign('departments', count($departments));

            $dwhere = array();
            if(count($departments)>0){
                $departments_str = implode(',', $departments);
                if(empty($department)){
                    $dwhere['departmentid'] = array('IN', $departments_str);
                }
                //部门树，搜索用
                $result_all1 = $this->department_obj->field('id,parentid,name')->where('id IN ('.$departments_str.')')->order(array("id" => "ASC"))->select();
                $result_all2 = $this->department_obj->getParents($this->department_obj,$result_all1);
                $result_all3 = $this->department_obj->getParents($this->department_obj,$result_all2);
                $result_all = array_merge($result_all1,$result_all2,$result_all3);

            }else{//普通员工
                $dwhere['departmentid'] = array('EQ', session('ADMIN_ID'));
            }

            $swhere = array_merge_recursive($s_where,$dwhere);

            //搜索条件
            if(!empty($_GET['departmentid']) || !empty($_GET['departmentname']) || !empty($_GET['taskyear']) || !empty($_GET['taskmonth'])){
                $where = $swhere;
                $sdate = $taskyear.'-'.$taskmonth;
                $nowTime  = $taskyear.$taskmonth;
            }else{
                $where['tasknum'] = array('EQ',date('Ym',time()));
                $sdate = date('Y-m',time());
                $nowTime = date('Ym',time());
            }

            //查询员工的基本信息
            $result_users = $this->users_obj->field('id')
                ->where($where)
                ->where("id > 1")
                ->select();
            $userInfo = $this->users_obj->getBasicUsersInfo($result_users);

//            $nowTime = date('Ym',time());
//            $nowTime = date('Ym',time()-37*(24*3600));

            //查询所有的折比系数
            $Task = M('Task');
            $taskdetails = $this->achievements_obj->getTaskRatio($Task,$nowTime);
            $taskdetail = array();
            foreach($taskdetails as $k => $v){      //折比系数遍历成以time_limit为下标的二维数组.
                $taskdetail[$v['time_limit']]['tkid']       = $taskdetails[$k]['tkid'];
                $taskdetail[$v['time_limit']]['disnum']     = $taskdetails[$k]['disnum'];
                $taskdetail[$v['time_limit']]['begintime']  = $taskdetails[$k]['begintime'];
                $taskdetail[$v['time_limit']]['endtime']    = $taskdetails[$k]['endtime'];
                $taskdetail[$v['time_limit']]['id']         = $taskdetails[$k]['id'];
                $taskdetail[$v['time_limit']]['discountid'] = $taskdetails[$k]['discountid'];
                $taskdetail[$v['time_limit']]['time_limit'] = $taskdetails[$k]['time_limit'];
                $taskdetail[$v['time_limit']]['ratio']      = $taskdetails[$k]['ratio'];
            }
            //查询任务起始时间
            $tasktime = $Task->where(array('tasknum'=>$nowTime))->find();

            $count = $this->achievements_obj->getUserInvestmentCount($this->users_obj, $where);

            //自定义分页
            $page_size = $_GET['page_size'] ? $_GET['page_size'] : ($_POST['page_size'] ? $_POST['page_size'] : 10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            foreach ($where as $key => $val) {
                $page->parameter .= "$key=" . urlencode($val) . '&';
            }

            //查询所有员工
            $users = $this->achievements_obj->getUserInvestment($this->users_obj,$where,$page->firstRow, $page->listRows);

            //查询所有的业绩信息
            $ach_where['success_time'] = array(array('EGT',$tasktime['begintime']),array('ELT',$tasktime['endtime']),'and');
            foreach($users as $k => $v){
                $res[] = $this->achievements_obj->getInvestment($investment,$v['id'],$ach_where);
            }

            //去除为空的字数组
            foreach($res as $rk => $rv){
                if(!empty($res[$rk])){
                    $investment_list[] = $rv;
                }
            }

            //计算折比业绩
            $ratios = array();
            $allRatios = array();
            foreach($investment_list as $ik => $iv ){
                $ratio = array();
                for($i = 0; $i < count($iv);$i++){
                    if($iv[$i]['time_limit'] == $taskdetail[$iv[$i]['time_limit']]['time_limit']){
                        $ratio['allRatios'] += $iv[$i]['borrow_money'];
                        $ratio['ratio'] += $iv[$i]['borrow_money'] * $taskdetail[$iv[$i]['time_limit']]['ratio'];

                    }else{
                        $ratio['allRatios'] +=  $iv[$i]['borrow_money'];
                        $ratio['ratio'] +=  $iv[$i]['borrow_money'] * 1;
                    }
                }
                $ratios[$iv[0]['user_id']] = $ratio;
            }

            //员工基本数组和折比业绩数组合并
            $userRatio = array();
            foreach($users as $k => $v){
                $userRatio[$k]['month']             = date('Y-m',time()-37*(24*3600));
                $userRatio[$k]['id']                = $v['id'];
                $userRatio[$k]['hyd_id']            = $v['hyd_id'];
                $userRatio[$k]['user_realname']     = $v['user_realname'];
                $userRatio[$k]['departmentid']      = $v['departmentid'];
                $userRatio[$k]['temporary']         = $v['temporary'];
                $userRatio[$k]['area_header']       = $userInfo[$v['id']]['area_header'];
                $userRatio[$k]['city_header']       = $userInfo[$v['id']]['city_header'];
                $userRatio[$k]['manager']           = $userInfo[$v['id']]['manager'];
                $userRatio[$k]['area_department']   = $userInfo[$v['id']]['area_department'];
                $userRatio[$k]['city_department']   = $userInfo[$v['id']]['city_department'];
                $userRatio[$k]['department_name']   = $userInfo[$v['id']]['department_name'];
                $userRatio[$k]['reg_num']           = isset($v['reg_num']) ? $v['reg_num'] : 0;
                $userRatio[$k]['open_num']          = isset($v['open_num']) ? $v['open_num'] : 0;
                $userRatio[$k]['this_num1']         = isset($v['this_num1']) ? $v['this_num1'] : '0.00';
                $userRatio[$k]['this_num2']         = isset($v['this_num2']) ? $v['this_num2'] : '0.00';
                $userRatio[$k]['this_num3']         = isset($v['this_num3']) ? $v['this_num3'] : '0.00';
                $userRatio[$k]['ratio']             = isset($ratios[$v['id']]['ratio']) ? $ratios[$v['id']]['ratio'] : '0.00' ;
                $userRatio[$k]['allmoney']          = isset($ratios[$v['id']]['allRatios']) ? $ratios[$v['id']]['allRatios'] : '0.00' ;
            }
        }

        $this->assign("formget", $_GET);
        $this->assign("page", $page->show('Admin'));
        $this->assign('achieve',$userRatio);
        $this->display();
	}

    /**
     * 通过接口查询所有数据,并插入本地数据库
     */
	function addAchievements(){

        $Task = M('Task');
        $nowTime = date('Ym',time()-98*24*3600);
        $tasktime = $Task->where(array('tasknum'=>$nowTime))->find();
        //查询所有的折比系数

        $taskdetails = $this->achievements_obj->getTaskRatio($Task,$nowTime);

		//获取所需的用户的汇盈贷的ID
		$result_users = $this->users_obj->field('hyd_id')
            //->where($pwhere)
            ->where("hyd_id <> 0")
            ->select();
        $hyd_id='';
        foreach ($result_users as $k=>$v){
            $hyd_id .= $v['hyd_id'].',';
        }
        $hyd_ids = substr($hyd_id, 0, -1);       //舍弃最后一个","

		//查找任务的开始和结束时间
		$begindate =$tasktime['begintime'];
		$enddate = $tasktime['endtime'];
		$achi = customer_inquirys(C('INTERFACE_URL_AC'),$hyd_ids,$begindate,$enddate);

		if($achi['status'] == '0'){
			$achi_list = $achi['data'];
			$date = array();
			$Achieve = M('HydAchievements');
            $date = array();
			for($i = 0; $i< count($achi_list);$i++){
				$date['user_id']    = $achi_list[$i]['user_id'];
				$date['truename']   = $achi_list[$i]['truename'];
				$date['tasknum']    = $tasktime['tasknum'];
				$date['reg_time']   = $achi_list[$i]['reg_time'];
				$date['reg_num']    = $achi_list[$i]['reg_num'];
				$date['open_num']   = $achi_list[$i]['open_num'];
				$date['this_num1']  = $achi_list[$i]['this_num1'];
				$date['this_num2']  = $achi_list[$i]['this_num2'];
				$date['this_num3']  = $achi_list[$i]['this_num3'];
//				$date['all_num1']   = $achi_list[$i]['all_num1'];
//				$date['all_num2']   = $achi_list[$i]['all_num2'];
//				$date['all_num3']   = $achi_list[$i]['all_num3'];

                $dd[] = $date;
			}
//            echo date('Y-m-d',$begindate)."<br />".date('Y-m-d',$enddate);die;
            $res = $Achieve->addAll($dd);
            if($res){
                echo $i.'添加成功';
            }
		}else{
			$this->error('接口信息返回错误!!!!');
		}
		$this->display();
	}

}

