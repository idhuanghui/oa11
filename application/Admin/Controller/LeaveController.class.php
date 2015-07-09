<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
/**
 * 离职审核
 * 超级管理员和管理员拥有一级和二级的审核权限
 * 人事部拥有一级审核权限(初期,人事部的一级审核权限是最终审核权限,审核直接离职无需财务二次审核)
 * 财务部拥有二级审核权限
 * Class ExamineController
 * @package Admin\Controller
 *
 */
class LeaveController extends AdminbaseController{

    function _initialize(){
        parent::_initialize();
        $this->users_obj = D("Common/Users");
        $this->role_obj = D("Common/Role");
        $this->position_obj = D("Common/Position");
        $this->department_obj = D("Common/Department");
        $this->user_leave_obj = D("Common/UserLeave");
        $this->auth_user_obj = D("Common/Auth_user");
    }

    function index(){

        //分类树,搜索时使用
        $result =  $this->department_obj->field('id,parentid,name')->order(array("id" => "ASC"))->select();
        $select_categorys = json_encode($result);
        $this->assign("select_categorys", $select_categorys);

        //获取所登录用的所属部门
        $departmentid = $this->users_obj->field('departmentid')->where(array('id'=>session('ADMIN_ID')))->find();
        $sdeparmentid = $this->department_obj->field('id,parentid')->where(array('id'=>$departmentid['departmentid']))->find();
        $sdeparmentids = $this->department_obj->field('id,parentid')->where(array('parentid'=>$sdeparmentid['parentid']))->select();
        foreach($sdeparmentids as $v){
            $vdid[] = $v['id'];
        }
        $deid = implode(',',$vdid);

        $Model = M();

        //搜索开始
        $_GET['level'] 			= $s_level            = $_REQUEST['level'];
		$_GET['user_realname'] 	= $s_user_realname    = $_REQUEST['user_realname'];
		$_GET['user_login'] 	= $s_user_login       = $_REQUEST['user_login'];
		$_GET['temporary'] 		= $s_temporary        = $_REQUEST['temporary'];
		$_GET['user_status'] 	= $s_user_status      = $_REQUEST['user_status'];
		$_GET['departmentid'] 	= $s_department       = $_REQUEST['departmentid'];
		$_GET['departmentname']	= I('post.departmentname');

        if(!empty($s_user_login)){
            $s_where['user_login'] = array('EQ',$s_user_login);
        }
        if(!empty($s_temporary)){
            $s_where['temporary'] = array('EQ',$s_temporary);
        }
        if(!empty($s_department)){
            $s_where['departmentid'] = array('in',$s_department);
        }
        if(!empty($s_user_realname)){
            $s_where['user_realname'] = array('like','%'.$s_user_realname.'%');
        }
        if(!empty($s_level)){
            $s_where['level'] = array('EQ',$s_level);
        }

		if(!empty($s_user_status)){
            $s_where['oprocess'] = array('EQ',$s_user_status);
        }else{
            $s_where['oprocess'] = array(array('EQ','Q1'),array('EQ','Q11'),array('EQ','Q2'),array('EQ','Q21'),'OR');
        }

        //查询是否为财务部或者人事部。
        $Umodel = M('Role_user');
        $r_user = $Umodel->field('role_id')->where('user_id='.session('ADMIN_ID'))->select();
        $r_user2 = myfunction(',',$r_user);
        $in_role = explode(',',$r_user2);
        $isadm = in_array('2',$in_role);
        $ishr = in_array('3',$in_role);
        $oneLeave = in_array('7',$in_role);
        $twoLeave = in_array('8',$in_role);
        $this->assign('ishr',$ishr);
        $this->assign('isadm',$isadm);
        $this->assign('oneLeave',$oneLeave);
        $this->assign('twoLeave',$twoLeave);

        //多表查询
//        if(session('ADMIN_ID') == '1' || session('roid') == '2' || ($ishr && $islevel)) {     //admin
        if(session('ADMIN_ID') == '1' || session('roid') == '2' ) {     //admin
			$pwhere['oprocess'] = array(array('EQ', 'Q'), array('EQ', 'Q1'), array('EQ', 'Q11'), array('EQ', 'Q2'), array('EQ', 'Q21'), 'OR');
			$where['ul.oprocess'] = array(array('EQ', 'Q'), array('EQ', 'Q1'), array('EQ', 'Q11'), array('EQ', 'Q2'), array('EQ', 'Q21'), 'OR');
            $h_where = array_merge_recursive($where, $s_where);
			$p_where = array_merge_recursive($pwhere,$s_where);
            if (!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['user_status']) || !empty($_GET['departmentid'])) {
                $where = $h_where;
				$pwhere = $p_where;
            } else {
                $where = $where;
				$pwhere = $pwhere;
            }
			$count=$this->user_leave_obj->where($pwhere)->count();
			//自定义分页
			$page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
			$page = $this->page($count, $page_size);
			$_GET['page_size'] = $page_size;

            $res_users = $this->user_leave_obj->getList($Model, $where, $page->firstRow, $page->listRows);
        }elseif($ishr){
			$pwhere['oprocess'] = array(array('EQ', 'Q'), array('EQ', 'Q1'), array('EQ', 'Q11'), array('EQ', 'Q2'), array('EQ', 'Q21'),array('EQ', 'Q3'), 'OR');
			$pwhere['departmentid'] = array('in',$deid);
            //人事获取所有的列表
            $where['ul.oprocess'] = array(array('EQ', 'Q'), array('EQ', 'Q1'), array('EQ', 'Q11'), array('EQ', 'Q2'), array('EQ', 'Q21'),array('EQ', 'Q3'), 'OR');
//            $where['user.departmentid'] = array('in',$deid);
			$where['au.is_add']        = 1;
			$where['au.user_id']        = session('ADMIN_ID');
            $h_where = array_merge_recursive($where, $s_where);
			$p_where = array_merge_recursive($pwhere,$s_where);
			if (!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['user_status']) || !empty($_GET['departmentid'])) {
                $where = $h_where;
				$pwhere = $p_where;
            }else{
                $where = $where;
				$pwhere = $pwhere;
            }
			$count=$this->user_leave_obj->where($pwhere)->count();
			//自定义分页
			$page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
			$page = $this->page($count, $page_size);
			$_GET['page_size'] = $page_size;
//            $res_users = $this->users_obj->getList($Model, $where, $page->firstRow, $page->listRows);
            $res_users = $this->user_leave_obj->getAppointList($Model, $where, $page->firstRow, $page->listRows);
        }elseif($oneLeave){            //一级审核,拥有一级离职审核权限的人.
			$pwhere['oprocess'] = array(array('EQ','Q1'),array('EQ','Q11'),array('EQ','Q2'),array('EQ','Q21'),array('EQ','Q3'),'OR');
//			$pwhere['user_status'] = array(array('EQ','Q1'),array('EQ','Q11'),'OR');
			$pwhere['is_quit1']	= 1;
			$pwhere['user_id'] = session('ADMIN_ID');
//            $where['user.user_status'] = 'Q1';
            $where['ul.oprocess'] = array(array('EQ','Q'),array('EQ','Q1'),array('EQ','Q11'),array('EQ','Q2'),array('EQ','Q21'),array('EQ','Q3'),'OR');
            $where['au.is_quit1'] = 1;
            $where['au.user_id'] = session('ADMIN_ID');
            $h_where = array_merge_recursive($where,$s_where);
			$p_where = array_merge_recursive($pwhere,$s_where);
			if (!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['user_status']) || !empty($_GET['departmentid'])) {
                $where = $h_where;
				$pwhere = $p_where;
            }else{
                $where = $where;
				$pwhere = $pwhere;
            }
			$count=$this->user_leave_obj->where($pwhere)->count();
			//自定义分页
			$page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
			$page = $this->page($count, $page_size);
			$_GET['page_size'] = $page_size;
			$res_users = $this->user_leave_obj->getAppointList($Model,$where,$page->firstRow,$page->listRows);
        }elseif($twoLeave){      //二级审核,拥有二级离职审核权限的人.
			$pwhere['oprocess'] = array(array('EQ','Q'),array('EQ','Q2'),array('EQ','Q21'),array('EQ','Q3'),'OR');
			$pwhere['is_quit2'] = 1;
//			$pwhere['ul.userid'] = session('ADMIN_ID');
//            $where['user.user_status'] = 'Q2';
            $where['ul.oprocess'] = array(array('EQ','Q'),array('EQ','Q1'),array('EQ','Q11'),array('EQ','Q2'),array('EQ','Q21'),array('EQ','Q3'),'OR');
            $where['au.is_quit2'] = 1;
            $where['au.user_id'] = session('ADMIN_ID');
            $h_where = array_merge_recursive($where,$s_where);
			$p_where = array_merge_recursive($pwhere,$s_where);
			if (!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['user_status']) || !empty($_GET['departmentid'])) {
                $where = $h_where;
				$pwhere = $p_where;
            }else{
                $where = $where;
				$pwhere = $pwhere;
            }
			$count=$this->user_leave_obj->where($pwhere)->count();
			//自定义分页
			$page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
			$page = $this->page($count, $page_size);
			$_GET['page_size'] = $page_size;
			$res_users = $this->user_leave_obj->getAppointList($Model,$where,$page->firstRow,$page->listRows);
        }

		$users = array();
		foreach ($res_users as $r) {
			$city_data = $this->users_obj->getHeader($this->department_obj,$r['parentid']);
			$r['city_header'] = $city_data['user_realname'];
			$r['cc_manager'] = $city_data['manager'];
			$r['city_department'] = $city_data['name'];
			$area_data = $this->users_obj->getHeader($this->department_obj,$city_data['parentid']);
			$r['area_header'] = $area_data['user_realname'];
			$r['cc_manager'] = $area_data['manager'];
			$r['area_department'] = $area_data['name'];
			$users[] = $r;
		}

		//导出Excel
		if($_POST['explode'] == '1'){
			$Excel = A('Excel');
			$Excel->levalExcel($users);
		}

        $roles_src=$this->role_obj->select();
        $roles=array();
        foreach ($roles_src as $r){
            $roleid=$r['id'];
            $roles["$roleid"]=$r;
        }

//        $this->assign("page", $page->show('Admin'));
        $this->assign("page", $page->show('Admin'));
        $this->assign("formget",$_GET);
        $this->assign("roles",$roles);
        $this->assign("users",$users);
        $this->display();
    }

    //申请离职
    function applyEdit(){
        $id = intval(I('get.id'));
        $user = $this->users_obj->where(array('id'=>$id))->find();

        $this->assign('user',$user);
        $this->display('leave');
    }

    function  applyEdit_post(){
        $id= intval(I("post.userid"));
        if(IS_POST){
            if(!empty($_POST['remark'])|| !empty($_POST['with_remark'])||!empty($_POST['leave_time'])||!empty($_POST['end_time'])){
                $udata = array();
                $udata['user_status'] = 'Q1';
                $uresult = $this->users_obj->where(array('id'=>$id))->data($udata)->save();
                if($uresult !== false){
                    if($this->user_leave_obj->create()){
                        $this->user_leave_obj->creater = session('ADMIN_ID');
                        $this->user_leave_obj->createtime = time();
                        $result = $this->user_leave_obj->add();
                        if($result !== false){
                            $this->success('申请离职成功!',U('User/index'));
                        }
                    }else{
                        $this->error($this->user_leave_obj->getError());
                    }
                }else{
                    $this->error('申请离职失败!');
                }
            }else{
                $this->error('必填信息请勿留空!');
            }
        }

    }

    //离职审核
    function edit(){
        $id= intval(I("get.id"));
		$ulid = intval(I("get.ulid"));
        $Model = M();
        $user = $this->user_leave_obj->getLeaveOne($Model,$ulid);

        $scity_data = $this->department_obj->where("id= ".$user['departmentid'])->find();		//三级名称
        $city_data = $this->department_obj->where("id= ".$scity_data['parentid'])->find();		//二级名称
        $user['city_header'] = $city_data['header'];
        $user['city_department'] = $city_data['name'];
        $area_data = $this->department_obj->where("id= ".$city_data['parentid'])->find();		//一级名称
        $user['area_header'] = $area_data['header'];
        $user['area_department'] = $area_data['name'];

//        $au_res = $this->auth_user_obj->where(array('user_id'=>session('ADMIN_ID'),'is_entry1'=>1))->select();
//        $au_res2 = $this->auth_user_obj->where(array('user_id'=>session('ADMIN_ID'),'is_entry2'=>1))->select();
        $Umodel = M('Role_user');
        $r_user = $Umodel->field('role_id')->where('user_id='.session('ADMIN_ID'))->select();
        $r_user2    = myfunction(',',$r_user);
        $in_role    = explode(',',$r_user2);
        $isadm      = in_array('2',$in_role);
        $ishr = in_array('3',$in_role);
        $oneLeave = in_array('7',$in_role);
        $twoLeave = in_array('8',$in_role);
        if($oneLeave || session('ADMIN_ID') == 1 || $isadm){
//            $ex = "<input type='radio' name='oprocess' value='Q2' />  同意离职 ";
//            $ex .= "<input type='radio' name='oprocess' value='Q11' />  不同意离职 ";
            $ex1 = "<button type='submit' name='oprocess' value='Q2' class='bg_green'>通过</button>";
            $ex2 = "<button type='submit' name='oprocess' value='Q11' class='bg_red'>驳回</button>";
        }elseif($twoLeave || session('ADMIN_ID') == 1 || $isadm){
            $ex1 = "<button type='submit' name='oprocess' value='Q' class='bg_green'>通过</button>";
            $ex2 = "<button type='submit' name='oprocess' value='Q21' class='bg_red'>驳回</button>";
        }else{
            $ex1 = "状态错误";
        }

        //查找审核记录
        $Model = M();
        $ex_log = $this->user_leave_obj->getExList($Model,$id);

        $this->assign('ex1',$ex1);
        $this->assign('ex2',$ex2);
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 离职审核提交
     * 1, session('ADMIN_ID') ==1 超级管理员可以
     * 2, session('roid') == '2' 管理员可以
     * 3, $au_res 拥有一级离职审核权限可以
     * 4, $au_res2 拥有二级离职审核权限可以
     * 5, $_POST['oprocess'] 值有效
     */
    function edit_post(){
        if(IS_POST){

//            if(!empty($_POST['with_remark'])){
            if(trim($_POST['with_remark']) != ''){
                $data = array();
				$data['oprocess'] 	= trim($_POST['oprocess']);
                $s_data = array();

                $Umodel = M('Role_user');
                $r_user = $Umodel->field('role_id')->where('user_id='.session('ADMIN_ID'))->select();
                $r_user2    = myfunction(',',$r_user);
                $in_role    = explode(',',$r_user2);
                $isadm      = in_array('2',$in_role);
                $ishr = in_array('3',$in_role);
                $oneLeave = in_array('7',$in_role);
                $twoLeave = in_array('8',$in_role);

				$UserModel = M('Users');

                if(session('ADMIN_ID') ==1 || $isadm || ($oneLeave && trim($_POST['oprocess'] == 'Q2'))) {
					$data['f_creater'] 	= session('ADMIN_ID');
					$data['f_remark'] 	= trim($_POST['with_remark']);
					$data['f_time']		= time();
					$s_data['user_status'] = trim($_POST['oprocess']);
                    $o_result = $UserModel->where(array('id'=>trim($_POST['userid'])))->save($s_data);
					if($o_result){
						$result = $this->user_leave_obj->where(array('id'=>intval($_POST['ulid']),'userid'=>trim($_POST['userid'])))->save($data);
					}
                }elseif(session('ADMIN_ID') ==1 || $isadm || ($oneLeave && trim($_POST['oprocess'] == 'Q11'))){
					$data['f_creater'] 	= session('ADMIN_ID');
					$data['f_remark'] 	= trim($_POST['with_remark']);
					$data['f_time']		= time();
					$s_data['user_status'] = 'E';
                    $o_result = $this->users_obj->where(array('id' => trim($_POST['userid'])))->save($s_data);
					if($o_result){
						$result = $this->user_leave_obj->where(array('id'=>intval($_POST['ulid']),'userid'=>trim($_POST['userid'])))->save($data);
					}
                }elseif( session('ADMIN_ID') ==1 || $isadm || ($twoLeave && trim($_POST['oprocess'] == 'Q'))){
					$data['s_creater'] 	= session('ADMIN_ID');
					$data['s_remark'] 	= trim($_POST['with_remark']);
					$data['s_time']		= time();
					$s_data['user_status'] = trim($_POST['oprocess']);
                    $o_result = $this->users_obj->where(array('id'=> trim($_POST['userid'])))->save($s_data);
					if($o_result){
						$result = $this->user_leave_obj->where(array('id'=>intval($_POST['ulid']),'userid'=>trim($_POST['userid'])))->save($data);
					}
                }elseif(session('ADMIN_ID') ==1 || $isadm || ($twoLeave && trim($_POST['oprocess'] == 'Q21'))){
					$data['s_creater'] 	= session('ADMIN_ID');
					$data['s_remark'] 	= trim($_POST['with_remark']);
					$data['s_time']		= time();
					$s_data['user_status'] = 'E';
                    $o_result = $this->users_obj->where(array('id'=>trim($_POST['userid'])))->save($s_data);
					if($o_result){
						$result = $this->user_leave_obj->where(array('id'=>intval($_POST['ulid']),'userid'=>trim($_POST['userid'])))->save($data);
					}
                }
                if($result !== false){
                    $this->success('离职审核成功!',U('Leave/index'));
                }else{
                    $this->error('离职审核失败!');
                }
            }else{
                $this->error('审批意见和备注均不能为空!');
            }
        }
    }

	function info(){
		$id = intval(I("get.id"));
		$ulid = intval(I("get.ulid"));
		if(!$id){
			$id = get_current_admin_id();
		}
		$Model = M();
		//获取用户信息
		$user = $this->user_leave_obj->getUserInfo($Model,$ulid);
//		echo $Model->getLastSql();die;
		$scity_data = $this->department_obj->where("id= ".$user['departmentid'])->find();		//三级名称
		$city_data = $this->department_obj->where("id= ".$scity_data['parentid'])->find();		//二级名称
		$user['city_header'] = $city_data['header'];
		$user['city_department'] = $city_data['name'];
		$area_data = $this->department_obj->where("id= ".$city_data['parentid'])->find();		//一级名称
		$user['area_header'] = $area_data['header'];
		$user['area_department'] = $area_data['name'];
		$this->assign('user',$user);

		$this->display();
	}

    /**
     * 取消离职,人事员工特有权限,
     */
    function cancelLeave(){
        $id = intval(I("get.id"));
        $date = array();
        $date['user_status'] = 'E';
        $res = $this->users_obj->where(array('id'=>$id))->save($date);
        if($res !== false){
			$date = array();
			$date['oprocess'] = 'Q3';
			$date['userid'] = $id;
			$date['q_creater'] = session('ADMIN_ID');
			$date['q_remark'] = '取消离职';
			$date['q_time'] = time();
//            $this->user_leave_obj->data($date)->add();
			$this->user_leave_obj->where(array('userid'=>$id))->save($date);
			$this->success('取消离职成功!',U('Leave/index'));
        }else{
            $this->error('取消离职失败!');
        }
    }
}