<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
/**
 * 入职审核
 * 超级管理员和管理员拥有审核权限
 * 人事部拥有一级审核权限(初期,人事部的一级审核权限是最终审核权限,审核直接入职无需财务二次审核)
 * 财务部拥有二级审核权限
 * Class ExamineController
 * @package Admin\Controller
 *
 */
class EntryController extends AdminbaseController{

    function _initialize(){
        parent::_initialize();
        $this->users_obj            = D("Common/Users");
        $this->role_obj             = D("Common/Role");
        $this->position_obj         = D("Common/Position");
        $this->department_obj       = D("Common/Department");
        $this->user_entry_obj       = D("Common/UserEntry");
        $this->auth_user_obj        = D("Common/Auth_user");
    }

    function index(){
        /**
         * 部分人事和财务,所有人权限相同,
         * 有审核权限的用户加入审核权限组,
         * 根据auth_user判断用户的一级审核权限或者二级审核权限.
         */
//		print_r(session('roid'));
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

        $Model = M('role_user');

        //搜索开始
		$_GET['user_login']		= $s_user_login   = $_REQUEST['user_login'];
		$_GET['temporary']		= $temporary      = $_REQUEST['temporary'];
		$_GET['departmentid']	= $department     = $_REQUEST['departmentid'];
		$_GET['departmentname']	  = I('post.departmentname');
		$_GET['user_realname']	= $realname       = $_REQUEST['user_realname'];
		$_GET['level']			= $slevel         = $_REQUEST['level'];
		$_GET['user_status'] 	= $s_user_status      = $_REQUEST['user_status'];

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
            $s_where['user_status'] = array('EQ',$s_user_status);
        }else{
            $s_where['user_status'] = array(array('EQ','E1'),array('EQ','E2'),array('EQ','E11'),array('EQ','E21'),'OR');
        }

		//查询是否为财务部或者人事部。
		$Umodel = M('Role_user');
		$r_user = $Umodel->field('role_id')->where('user_id='.session('ADMIN_ID'))->select();
		$r_user2    = myfunction(',',$r_user);
		$in_role    = explode(',',$r_user2);
		$isadm      = in_array('2',$in_role);
		$ishr       = in_array('3',$in_role);
        $oneEntry   = in_array('5',$in_role);
        $twoEntry   = in_array('6',$in_role);
        $this->assign('isadm',$isadm);
		$this->assign('ishr',$ishr);
		$this->assign('oneEntry',$oneEntry);
		$this->assign('twoEntry',$twoEntry);
        //人事Leader
//        $islevel = $this->users_obj->where(array('id'=>session('ADMIN_ID'),'level'=>1))->find();
        $Model = M();
        /**
         *  是人事,并且是主管列出所有
         *  一级审核权限的,列出需要一级审核的员工
         * 二级审核列出二级审核的员工.
         */
//        if(session('ADMIN_ID') == '1' || $isadm || ($ishr && $islevel)) {     //管理员和人事主管能查看所有的正在入职的员工。
        if(session('ADMIN_ID') == '1' || $isadm ) {     //管理员和人事主管能查看所有的正在入职的员工。
            $pwhere['user_status'] = array(array('EQ', 'E1'), array('EQ', 'E2'), array('EQ', 'E11'), array('EQ', 'E21'), array('EQ', 'E3'), 'OR');
            $where['user.user_status'] = array(array('EQ', 'E1'), array('EQ', 'E2'), array('EQ', 'E11'), array('EQ', 'E21'), array('EQ', 'E3'), 'OR');

            $h_where = array_merge_recursive($where, $s_where);
            $p_where = array_merge_recursive($pwhere,$s_where);
			if(!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['user_status']) || !empty($_GET['departmentid'])){
                $where = $h_where;
                $pwhere = $p_where;
            } else {
                $where = $where;
                $pwhere = $pwhere;
            }
            $count=$this->users_obj->where($pwhere)->count();             //统计总数
            //自定义分页
            $page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

            $res_users = $this->users_obj->getList($Model, $where, $page->firstRow, $page->listRows);
        }elseif($ishr){
            $pwhere['user_status']	= array(array('EQ','E1'),array('EQ','E11'),array('EQ','E2'),array('EQ','E21'),array('EQ','E3'),'OR');
            $pwhere['departmentid']	= array('in',$deid);
//            人事默认可以查看所有在入职中的员工
            $where['user.user_status'] = array(array('EQ','E1'),array('EQ','E11'),array('EQ','E2'),array('EQ','E21'),array('EQ','E3'),'OR');
//            $where['user.departmentid'] = array('in',$deid);
			$where['au.is_add']        = 1;
			$where['au.user_id']        = session('ADMIN_ID');
            $h_where = array_merge_recursive($where, $s_where);
            $p_where =array_merge_recursive($pwhere,$s_where);
			if(!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['user_status']) || !empty($_GET['departmentid'])){
                $where = $h_where;
                $pwhere = $p_where;
            } else {
                $where = $where;
                $pwhere = $pwhere;
            }
            $count=$this->users_obj->where($pwhere)->count();             //统计总数
            //自定义分页
            $page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;
            $res_users = $this->users_obj->getAppointList($Model,$where,$page->firstRow,$page->listRows);
        }elseif($oneEntry){       //人事员工和一级审核权限的人,一级未审核的入职员工列表.
            $pwhere['user_status'] = array(array('EQ','E1'),array('EQ','E11'),array('EQ','E2'),array('EQ','E21'),array('EQ','E3'),'OR');
            $pwhere['is_entry1'] = 1;
            $pwhere['user_id']  = session('ADMIN_ID');
            $where['user.user_status'] = array(array('EQ','E1'),array('EQ','E11'),array('EQ','E2'),array('EQ','E21'),array('EQ','E3'),'OR');
//			$where['user.user_status'] = array('NEQ','E');
            $where['au.is_entry1'] = '1';
            $where['au.user_id'] = session('ADMIN_ID');

            $h_where = array_merge_recursive($where,$s_where);
            $p_where = array_merge_recursive($pwhere,$s_where);
			if(!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['user_status']) || !empty($_GET['departmentid'])){
                $where = $h_where;
                $pwhere = $p_where;
            }else{
                $where = $where;
                $pwhere = $pwhere;
            }
            $count=$this->users_obj->where($pwhere)->count();             //统计总数
            //自定义分页
            $page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;

			$res_users = $this->users_obj->getAppointList($Model,$where,$page->firstRow,$page->listRows);
        }elseif($twoEntry){      //二级审核,拥有二级入职审核权限的人.
            $pwhere['user_status'] = array(array('EQ','E1'),array('EQ','E11'),array('EQ','E2'),array('EQ','E21'),array('EQ','E3'),'OR');
            $pwhere['is_entry2'] = 1;
            $pwhere['user_id'] = session('ADMIN_ID');
            $where['user.user_status'] = array(array('EQ','E1'),array('EQ','E11'),array('EQ','E2'),array('EQ','E21'),array('EQ','E3'),'OR');
            $where['au.is_entry2'] = 1;
            $where['au.user_id'] = session('ADMIN_ID');

            $h_where = array_merge_recursive($where,$s_where);
            $p_where = array_merge_recursive($pwhere,$s_where);
			if(!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['user_status']) || !empty($_GET['departmentid'])){
                $where = $h_where;
                $pwhere = $p_where;
            }else{
                $where = $where;
                $pwhere = $pwhere;
            }
            $count=$this->users_obj->where($pwhere)->count();             //统计总数
            //自定义分页
            $page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
            $page = $this->page($count, $page_size);
            $_GET['page_size'] = $page_size;
			$res_users = $this->users_obj->getAppointList($Model,$where,$page->firstRow,$page->listRows);
        }

		$page = $this->page(count($res_users), $page_size);

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

        $roles_src=$this->role_obj->select();
        $roles=array();
        foreach ($roles_src as $r){
            $roleid=$r['id'];
            $roles["$roleid"]=$r;
        }

        $this->assign("page", $page->show('Admin'));
        $this->assign("formget",$_GET);
        $this->assign("roles",$roles);
        $this->assign("users",$users);
        $this->display();
    }

    //更新员工入职状态.
    function edit(){
        $id= intval(I("get.id"));

        $Model = M();
		$user = $this->user_entry_obj->getEntryOne($Model,$id);

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
        $oneEntry   = in_array('5',$in_role);
        $twoEntry   = in_array('6',$in_role);
        if($oneEntry || session('ADMIN_ID') == 1 || $isadm){
            //取消二级审批,由一级直接审批入职.
//            $ex = "<input type='radio' name='oprocess' value='E' />  同意入职 ";
//            $ex .= "<input type='radio' name='oprocess' value='E11' />  不同意入职 ";
//            $ex = "<input type='radio' name='oprocess' value='E2' />  同意入职 ";
//            $ex .= "<input type='radio' name='oprocess' value='E11' />  不同意入职 ";
            $ex1 = "<button name='oprocess' value='E2' type='submit' class='bg_green'>通过</button>";
            $ex2 = "<button type='submit' name='oprocess' value='E11' class='bg_red'>驳回</button>";
        }elseif($twoEntry || session('ADMIN_ID') == 1 || $isadm){
            $ex1 = "<button name='oprocess' value='E' type='submit' class='bg_green'>通过</button>";
            $ex2 = "<button type='submit' name='oprocess' value='E21' class='bg_red'>驳回</button>";
        }else{
            $ex = "状态错误";
        }

        //查找审核记录
        $Model = M();
        $ex_log = $this->user_entry_obj->getExList($Model,$id);

        $this->assign('ex1',$ex1);
        $this->assign('ex2',$ex2);
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 入职审核提交
     * 1, session('ADMIN_ID') ==1 超级管理员可以
     * 2, session('roid') == '2' 管理员可以
     * 3, $au_res 拥有一级入职审核权限可以
     * 4, $au_res2 拥有二级入职审核权限可以
     * 5, $_POST['oprocess'] 值有效
     */
    function edit_post(){
        if(IS_POST){
            if(trim($_POST['remark']) != '' ){
//            if(!empty($_POST['remark'])){
                $data = array();
                $s_data = array();
                $data['oprocess']   = trim($_POST['oprocess']);
                $data['ostatus']    = 1;
                $data['userid']     = trim($_POST['userid']);
                $data['creater']    = session('ADMIN_ID');
                $data['remark']     = trim($_POST['remark']);
                $data['createtime'] = time();
                $s_data['user_status'] = trim($_POST['oprocess']);

//                $au_res = $this->auth_user_obj->where(array('user_id'=>session('ADMIN_ID'),'is_entry1'=>1))->select();
//                $au_res2 = $this->auth_user_obj->where(array('user_id'=>session('ADMIN_ID'),'is_entry2'=>1))->select();
                $Umodel = M('Role_user');
                $r_user = $Umodel->field('role_id')->where('user_id='.session('ADMIN_ID'))->select();
                $r_user2    = myfunction(',',$r_user);
                $in_role    = explode(',',$r_user2);
                $isadm      = in_array('2',$in_role);
                $oneEntry   = in_array('5',$in_role);
                $twoEntry   = in_array('6',$in_role);
                //if($au_res && trim($_POST['oprocess'] == 'E2')) {  //取消二级审批,直接一级审批通过.
                if(session('ADMIN_ID') ==1 || $isadm || $oneEntry && trim($_POST['oprocess'] == 'E2')) {
                    $o_result = $this->users_obj->where(array('id' => trim($_POST['userid'])))->save($s_data);
                }elseif(session('ADMIN_ID') ==1 || $isadm || $oneEntry && trim($_POST['oprocess'] == 'E11')){
                    $o_result = $this->users_obj->where(array('id' => trim($_POST['userid'])))->save($s_data);
                }elseif(session('ADMIN_ID') ==1 || $isadm || $twoEntry && trim($_POST['oprocess'] == 'E')){
                    $o_result = $this->users_obj->where(array('id'=>trim($_POST['userid'])))->save($s_data);
                }elseif(session('ADMIN_ID') ==1 || $isadm || $twoEntry && trim($_POST['oprocess'] == 'E21')){
                    $o_result = $this->users_obj->where(array('id'=>trim($_POST['userid'])))->save($s_data);
                }

                if($o_result){
                    $result = $this->user_entry_obj->add($data);
                }
                if($result !== false){
                    $this->success('入职审核成功!',U('Entry/index'));
                }else{
                    $this->error('入职审核失败!');
                }
            }else{
                $this->error('备注均不能为空!');
            }
        }
    }

	/**
	 * 取消入职
	 */
	function cancelEntry(){
		$id = intval(I("get.id"));
		$date = array();
		$date['user_status'] = 'E3';
		$res = $this->users_obj->where(array('id'=>$id))->save($date);
		if($res !== false){
			$this->user_entry_obj->add(array('oprocess' => 'E3', 'ostatus' => '1', 'userid' =>$id, 'creater' => session('ADMIN_ID'), 'remark' => '取消入职', 'createtime' => time()));
			$this->success('取消入职成功!',U('Entry/index'));
		}else{
			$this->error('取消入职失败!');
		}
	}


}