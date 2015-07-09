<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class UserController extends AdminbaseController{
	protected $users_obj,$role_obj,$position_obj,$department_obj,$user_entry_obj,$auth_user_obj;
	
	function _initialize() {
		parent::_initialize();
		$this->users_obj        = D("Common/Users");
		$this->role_obj         = D("Common/Role");
        $this->position_obj     = D("Common/Position");
        $this->department_obj   = D("Common/Department");
        $this->user_entry_obj   = D("Common/UserEntry");
		$this->auth_user_obj	= D("Common/Auth_user");
	}

	/**
	 *
	 */
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

        //搜索开始
		$_GET['user_login']		= $s_user_login   = $_REQUEST['user_login'];
		$_GET['temporary']		= $temporary      = $_REQUEST['temporary'];
        $_GET['departmentid']	= $department     = $_REQUEST['departmentid'];
		$_GET['departmentname']	  = I('post.departmentname');
        $_GET['user_realname']	= $realname       = $_REQUEST['user_realname'];
        $_GET['level']			= $slevel         = $_REQUEST['level'];

        if(!empty($s_user_login)){
            $s_where['user_login'] = array('EQ',$s_user_login);
        }
        if(!empty($temporary)){
            $s_where['temporary'] = array('EQ',$temporary);
        }
        if(!empty($department)){
            $s_where['departmentid'] = array('in',$department);
        }
        if(!empty($realname)){
            $s_where['user_realname'] = array('like','%'.$realname.'%');
        }
        if(!empty($slevel)){
            $s_where['level'] = array('EQ', $slevel);
        }


		//查询是否为财务部或者人事部。
		$Umodel = M('Role_user');
		$r_user = $Umodel->field('role_id')->where('user_id='.session('ADMIN_ID'))->select();
		$r_user2 = myfunction(',',$r_user);
		$in_role = explode(',',$r_user2);
		$isadm = in_array('2',$in_role);
		$ishr = in_array('3',$in_role);

        $Model = M();
        //多表查询
        if(session('ADMIN_ID') == '1') {     //admin
			$pwhere['user_status']   = array('EQ','E');
			$pwhere['id']			= array('GT',1);
			$pwhere['user_type'] = array('NEQ',3);
//			$pwhere = "user_status = 'E' AND id > 1 AND user_type != 3";
            $where['user.user_status']   = array('EQ','E');
            $where['user.user_type'] = array('NEQ',3);

            $h_where = array_merge_recursive($where,$s_where);  //追加数组.
			$p_where = array_merge_recursive($pwhere,$s_where);
            if(!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['departmentid'])){
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

			foreach($pwhere as $key=>$val) {
				$page->parameter   .=   "$key=".urlencode($val).'&';
			}

            $res_users = $this->users_obj->getList($Model,$where,$page->firstRow,$page->listRows); //获取用户基本信息
			$export_users = $this->users_obj->getList($Model,$where);
		}elseif(session('roid') == '2'){    //管理员
			$pwhere['user_type'] = array(array('GT',1),array('NEQ',3),'and');
			$pwhere['user_status'] = array('EQ','E');
            $where['user.user_type'] = array(array('GT',1),array('NEQ',3),'and');
            $where['user.user_status']   = array('EQ','E');

            $h_where = array_merge_recursive($where,$s_where);  //追加数组.
			$p_where = array_merge_recursive($pwhere,$s_where);
			if(!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['departmentid'])){
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

			$res_users = $this->users_obj->getList($Model,$where,$page->firstRow,$page->listRows); //获取用户基本信息
			$export_users = $this->users_obj->getList($Model,$where); //获取用户基本信息
		}elseif($ishr){

			$pwhere['user_type']	= array(array('GT',1),array('NEQ',3),'and');
			$pwhere['user_status']	= 'E';
			$pwhere['is_list']		= 1;
			$pwhere['departmentid']	= array('in',$deid);
			$pwhere['user_id']		= session('ADMIN_ID');
            $where = array();
            $where['user_type'] = array(array('GT',1),array('NEQ',3),'and');
            $where['user.user_status']  = 'E';
            $where['au.is_list']        = 1;
//			$where['user.departmentid'] = array('in',$deid);
            $where['au.user_id']        = session('ADMIN_ID');

            $h_where = array_merge_recursive($where,$s_where);
			$p_where = array_merge_recursive($pwhere,$s_where);
			if(!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['departmentid'])){
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

			$res_users = $this->users_obj->getAppointList($Model,$where,$page->firstRow,$page->listRows);  //获取用户基本信息
			$export_users = $this->users_obj->getAllAppointList($Model,$where);  //获取用户基本信息
        }elseif(session('level') == '2'){       //普通员工,显示自己
			$pwhere['id'] = session('ADMIN_ID');
            $where = array();
            $where['user.id'] = session('ADMIN_ID');
            $h_where = array_merge_recursive($where,$s_where);  //追加数组.
			$p_where = array_merge_recursive($pwhere,$s_where);
			if(!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['departmentid'])){
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
			$res_users = $this->users_obj->getList($Model,$where,$page->firstRow,$page->listRows);
			$export_users = $this->users_obj->getList($Model,$where);
        }else{      //部门领导人,部门下的员工.
			$pwhere['user_status'] = array('EQ','E');
			$pwhere['departmentid'] = session('departmentid');
			$where = array();
            $where['user.user_status']   = array('EQ','E');
            $where['user.departmentid'] = session('departmentid');
            $h_where = array_merge_recursive($where,$s_where);  //追加数组.
			$p_where = array_merge_recursive($pwhere,$s_where);
			if(!empty($_GET['level']) || !empty($_GET['user_realname']) || !empty($_GET['user_login']) || !empty($_GET['temporary']) || !empty($_GET['departmentid'])){
                $where = $h_where;
				$pwhere = $p_where;
            }else{
                $where = $where;
				$pwhere = $pwhere;
            }

			$count=$this->users_obj->where($pwhere)->count();             //统计总数
			//自定义分
			$page_size = $_GET['page_size']?$_GET['page_size']:($_POST['page_size']?$_POST['page_size']:10);
			$page = $this->page($count, $page_size);
			$_GET['page_size'] = $page_size;

			$res_users = $this->users_obj->getList($Model,$where,$page->firstRow,$page->listRows);
			$export_users = $this->users_obj->getList($Model,$where);
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
			$export = array();
			foreach ($export_users as $r) {
				$city_data = $this->users_obj->getHeader($this->department_obj,$r['parentid']);
				$r['city_header'] = $city_data['user_realname'];
				$r['cc_manager'] = $city_data['manager'];
				$r['city_department'] = $city_data['name'];
				$area_data = $this->users_obj->getHeader($this->department_obj,$city_data['parentid']);
				$r['area_header'] = $area_data['user_realname'];
				$r['cc_manager'] = $area_data['manager'];
				$r['area_department'] = $area_data['name'];
				$export[] = $r;
			}
			$Excel = A('Excel');
			$Excel->to_Excel($export);
		}

        $roles_src=$this->role_obj->select();
		$roles=array();
		foreach ($roles_src as $r){
			$roleid=$r['id'];
			$roles["$roleid"]=$r;
		}
		$this->assign("formget",$_GET);
		$this->assign("page", $page->show('Admin'));
//        $this->assign("quit_res",$quit_res);
		$this->assign("roles",$roles);
		$this->assign("users",$users);
        $this->assign("ishr",$ishr);
        $this->assign("isadm",$isadm);
		$this->display();
	}

	/**
	 * 添加员工
	 * 添加人事员工的时候要默认的为员工
	 */
	function add(){
        //用户所属部门
        import("Tree");
        $tree = new \Tree();
        $parentid = 1;
        $department = M('department');
        $auth_user = M('auth_user');
        $result = $department->order(array("listorder" => "ASC"))->select();
        $ishr = $department->field('ishr')->where(array('id'=>session('departmentid')))->find();
        $ures = $auth_user->where(array('user_id'=>session('ADMIN_ID'),'is_add'=>1))->getField('department_id',true);	//列取有添加权限的部门


        //if($ures && $ishr['ishr'] == 1){        //属于人事部,并且auth_user表不为空
		if($ures){
            //可选部门
            $newdepartments=array();
            foreach ($result as $m){
                $newdepartments[$m['id']]=$m;
            }
            foreach ($result as $r) {
                $r['level'] = _get_level($r['id'], $newdepartments);
                if( $r['level'] == 2){
                    if(!in_array($r['id'], $ures)){
                        continue;
                    }
                }
                $array[] = $r;
            }
        }else{
            foreach ($result as $r) {
                $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
                $array[] = $r;
            }
        }
//        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $str = "<option value='\$id' >\$spacer \$name</option>";
        $tree->init($array);
        $select_categorys = $tree->get_tree(0, $str);
        $this->assign('select_categorys',$select_categorys);

        $Role = D('role_user');
        $urole = $Role->where(array('user_id'=>session('ADMIN_ID')))->select();
        foreach($urole as $r=>$k){
            $r[$k] = $k;
        }

        $Model = M();
        $users = $this->users_obj->getRoleId($Model,session('ADMIN_ID'));

        //print_r($users);
        $this->assign('user',$users);
		$this->display();
	}

    //添加提交
	function add_post(){
		if(IS_POST){
            if(trim($_POST['level']) == '1'){       //判断部门Leader是否存在,存在不能添加
				$allthird = $this->users_obj->getAllThird($this->department_obj);
                $where['departmentid'] = trim($_POST['departmentid']);
                $where['level'] = 1;
				$where['user_status'] = array(array('NEQ','E11'),array('NEQ','E21'),array('NEQ','E3'),array('NEQ','Q'));
                if($this->users_obj->where($where)->find()){
                    $this->error('部门Leader已存在,请勿重复添加!');
                    exit;
                }
            }

            $Model = M();
            $users = $this->users_obj->getRoleIdOne($Model,session('ADMIN_ID'));

			//添加用户时,通过提交的deparmentid 判断部门的属性，并自动添加默认权限
			$deparmentid = I('post.departmentid');
			$res_id = $this->department_obj->field('id,parentid,ishead,ishr,isfinance')->where(array('id'=>$deparmentid))->find();
			$res_ids = $this->department_obj->field('id,parentid')->where(array('parentid'=>$res_id['parentid']))->select();

            $Ro_model = M('role_user');
            if ($users['role_id'] == 1 || $users['role_id'] == 2 || session('ADMIN_ID') == 1) { //超级管理员和管理员权限组的人添加员工直接入职
				$date = array();
                if ($this->users_obj->create()) {
                    $result = $this->users_obj->add();
                    if ($result !== false) {
                        $en_res = $this->user_entry_obj->add(array('oprocess' => 'E', 'ostatus' => '1', 'userid' => $result, 'creater' => session('ADMIN_ID'), 'remark' => '管理员添加', 'createtime' => time()));
                        if ($en_res) {    //默认新添加的用户到普通员工组
							if($res_id['ishr']){	//如果添加的为人事的员工,默认到人事组
								$Ro_model->add(array('role_id' => 3, 'user_id' => $result));
							}else{
								$Ro_model->add(array('role_id' => 9, 'user_id' => $result));
							}
                        }
						if($res_id['ishr']){	//被添加的员工如果属于人事部 执行
							for($i = 0;$i < count($res_ids);$i++){
								$date['user_id']		= $result;
								$date['department_id'] 	= $res_ids[$i]['id'];
								$date['is_list']		= 1;
								$date['is_add']			= 1;
								$date['is_quit']		= 1;
								$this->auth_user_obj->data($date)->add();
							}
						}
                        $this->success("添加成功！", U("user/index"));
                    } else {
                        $this->error("添加失败！");
                    }
                } else {
                    $this->error($this->users_obj->getError());
                }
            } else {
                if ($this->users_obj->create()) {
                    $result = $this->users_obj->add();
                    if ($result !== false) {
                        $user_entry_model = M('User_entry');
                        $en_res = $user_entry_model->add(array('oprocess' => 'E1', 'ostatus' => '1', 'userid' => $result, 'creater' => session('ADMIN_ID'), 'remark' => '申请入职', 'createtime' => time()));
                        if ($en_res) {        //默认新添加的用户到普通员工组
							if($res_id['ishr']){
								$Ro_model->add(array('role_id' => 3, 'user_id' => $result));
							}else{
                            	$Ro_model->add(array('role_id' => 9, 'user_id' => $result));
							}
                        }
						if($res_id['ishr']){	//被添加的员工如果属于人事部 执行
							for($i = 0;$i < count($res_ids);$i++){
								$date['user_id']		= $result;
								$date['department_id'] 	= $res_ids[$i]['id'];
								$date['is_list']		= 1;
								$date['is_add']			= 1;
								$date['is_quit']		= 1;
								$this->auth_user_obj->data($date)->add();
							}
						}
                        $this->success("添加成功！", U("user/index"));
                    } else {
                        $this->error("添加失败！");
                    }
                } else {
                    $this->error($this->users_obj->getError());
                }
            }
		}
	}
	
	//修改员工信息
	function edit(){
		$id= intval(I("get.id"));

		$user = $this->users_obj->getUserPosition($this->users_obj,$id);
		$this->assign('user',$user);

        //用户所属部门
        import("Tree");
        $tree = new \Tree();
        $parentid = $user['departmentid'];
        $department = M('department');
        $result = $department->order(array("listorder" => "ASC"))->select();
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
            $array[] = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $select_categorys = $tree->get_tree(0, $str);
        $this->assign('select_categorys',$select_categorys);

        $puser = $this->users_obj->field('id,positionid')->where(array('id'=>$id))->find();
		$position = $this->position_obj->field('id,name,category_id')->where(array('id'=>$puser['positionid']))->find();
		$positions = $this->position_obj->field('id,name,category_id')->where(array('category_id'=>$position['category_id']))->select();
		$this->assign('positions',$positions);
		$this->display();
	}

    //修改提交
	function edit_post(){
		if (IS_POST) {
			$id= intval(I("post.uid"));

			$data = array();
			$data['departmentid'] 	= I('post.departmentid');
			$data['level'] 			= I('post.level');
			$data['positionid'] 	= I('post.positionid');
			$data['payroll_try'] 	= I('post.payroll_try');
			$data['user_realname'] 	= I('post.user_realname');
			$data['sex'] 			= I('post.sex');
			$data['age'] 			= I('post.age');
			$data['user_login'] 	= I('post.user_login');
			$data['idcard'] 		= I('post.idcard');
			$data['acc_address'] 	= I('post.acc_address');
			$data['temporary'] 		= I('post.temporary');
			$data['hyd_name'] 		= I('post.hyd_name');
			$data['bank_address'] 	= I('post.bank_address');
			$data['bank_user'] 		= I('post.bank_user');
			$data['bank_num'] 		= I('post.bank_num');
			$data['user_email'] 	= I('post.user_email');
			$data['reference'] 		= I('post.reference');
			$data['acc_province'] 	= I('post.acc_province');
			$data['entrydate'] 		= I('post.entrydate');
			$data['education'] 		= I('post.education');
			$data['specialty'] 		= I('post.specialty');

			$result = $this->users_obj->where(array('id'=>$id))->data($data)->save();
			if($result !== false){
				$this->success('更新成功!',U("user/index"));
			}else{
				$this->error('更新失败!');
			}
		}
	}
	
	/**
	 *  删除
     * 不真实删除信息。
     * 修改信息属性,使之隐藏即可.
	 */
	function delete(){
		$id = intval(I("get.id"));
		if($id==1){
			$this->error("最高管理员不能删除！");
		}
        $user = M('Users');
        $data = array();
        $data['user_type'] = 3;
        if($this->users_obj->where("id=$id")->save($data) !==false){ //只更改状态,实现隐藏
		//if ($this->users_obj->where("id=$id")->delete()!==false) { //原删除
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}

    /**
     * 用户详情
     *
     */
    function info(){
        $id = intval(I("get.id"));
        if(!$id){
            $id = get_current_admin_id();
        }
        $Model = M();
        //获取用户信息
        $user = $this->users_obj->getUserInfo($Model,$id);

		$scity_data = $this->department_obj->where("id= ".$user['departmentid'])->find();		//三级名称
		$city_data = $this->department_obj->where("id= ".$scity_data['parentid'])->find();		//二级名称
		$user['city_header'] = $city_data['header'];
		$user['city_department'] = $city_data['name'];
		$area_data = $this->department_obj->where("id= ".$city_data['parentid'])->find();		//一级名称
		$user['area_header'] = $area_data['header'];
		$user['area_department'] = $area_data['name'];
		$this->assign('user',$user);

//        获取用户审核信息
        $ex_info = $this->users_obj->getUserInfo($this->user_entry_obj,$id);
        $this->assign('ex_info',$ex_info);
        $this->display();
    }

    //重置密码
    function password(){
        $this->display();
    }

    function password_post(){
        if (IS_POST) {
            if(empty($_POST['old_password'])){
                $this->error("原始密码不能为空！");
            }
            if(empty($_POST['password'])){
                $this->error("新密码不能为空！");
            }
            $user_obj = D("Common/Users");
            $uid=get_current_admin_id();
            $admin=$user_obj->where(array("id"=>$uid))->find();
            $old_password=$_POST['old_password'];
            $password=$_POST['password'];
            if(sp_password($old_password)==$admin['user_pass']){
                if($_POST['password']==$_POST['repassword']){
                    if($admin['user_pass']==sp_password($password)){
                        $this->error("新密码不能和原始密码相同！");
                    }else{
                        $data['user_pass']=sp_password($password);
                        $data['id']=$uid;
                        $r=$user_obj->save($data);
                        if ($r!==false) {
                            $this->success("修改成功！");
                        } else {
                            $this->error("修改失败！");
                        }
                    }
                }else{
                    $this->error("密码输入不一致！");
                }
            }else{
                $this->error("原始密码不正确！");
            }
        }
    }

    //管理员和人事直接重置密码
    function resetPasswd(){
        if($_POST){
			$repass = $this->users_obj->field('user_login,user_pass')->where(array('id'=>trim($_POST['id'])))->find();
			$data = array();
			$data['user_pass'] = sp_password(_substr($repass['user_login'],-6,6));
//			$data['user_pass'] = sp_password('123456');
			if($repass['user_pass'] == $data['user_pass']){
				echo '密码重置成功!';exit;
			}else{
				$res = $this->users_obj->where(array('id'=>trim($_POST['id'])))->data($data)->save();
				if($res){
					echo '密码重置成功!';exit;
				}else{
					echo '密码重置失败!';exit;
				}
			}
        }
    }


	/**
	 * 获取部门关联岗位名称
	 */
	public function relationDeparment(){
		if(IS_POST){
			$id = trim($_POST['departmentid']);
                        
			$dp_model = M();
                        $data = array();
                        $data = $dp_model->table(C('DB_PREFIX')."position as po")
                                    ->field("po.id as poid,po.name as poname,po.category_id as pocategory_id,dp.*")
                                    ->join("LEFT join ".C('DB_PREFIX')."department dp on po.category_id=dp.position_category")
                                    ->where('dp.id = '.$id)
                                    ->select();
			echo json_encode($data);
		}
	}

	/**
	 * 通过接口,根据用户输入的汇盈贷账号,获取用户的真实姓名.
	 */
	public function getHydUser(){
		if(IS_POST){
			$url = C('INTERFACE_URL');
			$date = array();
			$date['module'] 	= 'oa';
			$date['q'] 			= 'personalOA';
			$date['username'] 	= trim($_POST['username']);
			$res = POST_Api($url,$date);
			echo $res;
		}
	}

	/**
	 * 针对导入数据库中的信息,汇盈贷账户和汇盈贷ID不一致
	 *  需要同步一下。
	 */
	public function synchronousInfo(){
		if(IS_POST){
			$id = intval($_POST['id']);
			$userInfo = $this->users_obj->field('id,idcard,hyd_name')->where(array('id'=>$id))->find();

			//获取汇盈贷的信息,根据汇盈贷账户名
			$url = C('INTERFACE_URL');
			$date = array();
			$date['module'] 	= 'oa';
			$date['q'] 			= 'personalOA';
			$date['username'] 	= $userInfo['hyd_name'];
			$res = POST_Api($url,$date);
			$resArr = decodeUrlArr($res);

			if($resArr['error'] == '0'){
				$update = array();
				$update['hyd_id'] = $resArr['data']['user_id'];
                if($userInfo['idcard'] != $resArr['data']['idcard']){       //如果身份证号码和汇盈贷的身份证号码不一致,同步.
                    $update['idcard'] = $resArr['data']['idcard'];
                }
				$upres = $this->users_obj->where(array('id'=>$id))->save($update);
				if($update){
					echo '同步成功!';
				}else{
					echo '同步失败!';
				}
			}else{
				echo '获取汇盈贷账户信息失败!';
			}
		}
	}

	/**
	 * 放百度搜索下拉框,自动提示
	 * 暂时不启用
	 */
	private function seachSuggest(){
		if(IS_POST){
			$Model = M();
			$keywords = iconv("utf-8","utf-8//IGNORE",@$_POST['keywords']);
			$where['user_realname'] = array('like','%'.$keywords.'%');
			$res = $this->users_obj->field('id,user_realname')->where($where)->select();
			$resCount = count($res);
			if($resCount < 1){
				echo "no";exit();
			}elseif($resCount == 1){
				echo "[{'keywords':'".iconv_substr($res[0]['user_realname'],0,14,"utf-8")."'}]";
			}else{
				for($i = 0;$i<$resCount;$i++) {
					$result = "[{'keywords':'" . iconv_substr($res[$i]['user_realname'], 0, 14, "utf-8") . "'}";
					$result .= ']';
					echo $result;
				}
			}

		}
	}

	/***************************************
	 * 以下是导入Excel表格				   *
	 * 以及在导入表格时需要处理导入数据的一些方法*
	 ***************************************/
	/**
	 * 导入时获取汇盈贷ID
	 * @param $name   汇盈贷用户名
	 * @param $id	  用户ID
	 */
    private function hydId($name,$id){
		$url = C('INTERFACE_URL');
		$date = array();
		$date['module'] 	= 'oa';
		$date['q'] 			= 'personalOA';
		$date['username'] 	= $name;
		$res = POST_Api($url,$date);
		$resArr = decodeUrlArr($res);
		if($resArr['error'] == '0'){
			$update = array();
			$update['hyd_id'] = $resArr['data']['user_id'];
			$upres = $this->users_obj->where(array('id'=>$id))->save($update);
		}
	}

	//excel日期转换函数
    private function excelTime($days, $time=false){
		if(is_numeric($days)){
			//based on 1900-1-1
			$jd = GregorianToJD(1, 1, 1970);
			$gregorian = JDToGregorian($jd+intval($days)-25569);
			$myDate = explode('/',$gregorian);
			$myDateStr = str_pad($myDate[2],4,'0', STR_PAD_LEFT)
				."-".str_pad($myDate[0],2,'0', STR_PAD_LEFT)
				."-".str_pad($myDate[1],2,'0', STR_PAD_LEFT)
				.($time?" 00:00:00":'');
			return $myDateStr;
		}
		return $days;
	}

	/**
	 * 导入Excel上传
	 */
	public function upload()
	{
		header("Content-Type:text/html;charset=utf-8");
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     3145728 ;// 设置附件上传大小
		$upload->exts      =     array('xls', 'xlsx');// 设置附件上传类
		$upload->savePath  =      '/'; // 设置附件上传目录
		// 上传文件
		$info   =   $upload->uploadOne($_FILES['excelData']);
		$filename = './Uploads'.$info['savepath'].$info['savename'];
		$exts = $info['ext'];
		//print_r($info);exit;
		if(!$info) {// 上传错误提示错误信息
			$this->error($upload->getError());
		}else{// 上传成功
			$this->employee_import($filename, $exts);
		}
	}

    private function employee_import($filename, $exts='xls')
	{
		set_time_limit(90);
		//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
		import("Org.Util.PHPExcel");
		//创建PHPExcel对象，注意，不能少了\
		$PHPExcel=new \PHPExcel();
		//如果excel文件后缀名为.xls，导入这个类
		if($exts == 'xls'){
			import("Org.Util.PHPExcel.Reader.Excel5");
			$PHPReader=new \PHPExcel_Reader_Excel5();
		}else if($exts == 'xlsx'){
			import("Org.Util.PHPExcel.Reader.Excel2007");
			$PHPReader=new \PHPExcel_Reader_Excel2007();
		}
		//载入文件
		$PHPExcel=$PHPReader->load($filename);
		//获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
		$currentSheet=$PHPExcel->getSheet(0);
		//获取总列数
		$allColumn=$currentSheet->getHighestColumn();
		//获取总行数
		$allRow=$currentSheet->getHighestRow();
		//循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
		for($currentRow=1;$currentRow<=$allRow;$currentRow++){
			//从哪列开始，A表示第一列
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				//数据坐标
				$address=$currentColumn.$currentRow;
				//读取到的数据，保存到数组$arr中
				$data[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
			}
		}
		$this->save_import($data);
	}

	//保存导入数据
    private function save_import($data)
	{
		$Ro_model = M('role_user');
		foreach ($data as $k=>$v){
			if($k >= 2){
				$departmentid=$v['F'];
				$info[$k-2]['departmentid'] = $departmentid;	//入职部门

				$level=$v['G'];
				$info[$k-2]['level'] = $level;					//角色

				$positionid=$v['H'];
				$info[$k-2]['positionid'] = $positionid;		//岗位

				$payroll_try=$v['I'];
				$info[$k-2]['payroll_try'] = $payroll_try;		//岗位工薪

				$hyd_name=$v['Q'];
				$info[$k-2]['hyd_name'] = $hyd_name;			//汇盈贷账号

				$user_realname=$v['J'];
				$info[$k-2]['user_realname'] = $user_realname;	//姓名

				$idcard=$v['N'];
				$info[$k-2]['idcard'] = $idcard;				//身份证号码

				$sex=$v['K'];
				$info[$k-2]['sex'] = $sex;						//性别

				$age=$v['L'];
				$info[$k-2]['age'] = $age;						//年龄

				$user_login=$v['M'];
				$info[$k-2]['user_login'] = $user_login;		//手机号码

				$info[$k-2]['user_pass'] =  sp_password(_substr($user_login,-6,6));		//密码

				$acc_address=$v['O'];
				$info[$k-2]['acc_address'] = $acc_address;		//户口所在地

				$temporary=$v['P'];
				$info[$k-2]['temporary'] = $temporary;			//是否兼职

				$bank_address=$v['R'];
				$info[$k-2]['bank_address'] = $bank_address;	//工资卡开户行

				$bank_user=$v['S'];
				$info[$k-2]['bank_user'] = $bank_user;			//工资卡开户姓名

				$bank_num=$v['T'];
				$info[$k-2]['bank_num'] = $bank_num;			//工资卡开户账号

				$user_email=$v['U'];
				$info[$k-2]['user_email'] = $user_email;		//邮箱

				$reference=$v['V'];
				$info[$k-2]['reference'] = $reference;			//入职推荐人

				$acc_province=$v['W'];
				$info[$k-2]['acc_province'] = $acc_province;	//社保归属地

				$entrydate=$v['X'];
				$info[$k-2]['entrydate'] = $this->excelTime($entrydate);			//入职日期

				$education=$v['Y'];
				$info[$k-2]['education'] = $education;			//学历

				$specialty=$v['Z'];
				$info[$k-2]['specialty'] = $specialty;			//专业

				$result = $this->users_obj->add($info[$k-2]);
				$this->hydId($hyd_name,$result);
				$res_id = $this->department_obj->field('id,parentid,ishead,ishr,isfinance')->where(array('id'=>$departmentid))->find();
				$res_ids = $this->department_obj->field('id,parentid')->where(array('parentid'=>$res_id['parentid']))->select();

//				导入,自动授权
				if($res_id['ishr']){
					$Ro_model->add(array('role_id' => 3, 'user_id' => $result));
					for($i = 0;$i < count($res_ids);$i++){
						$date['user_id']		= $result;
						$date['department_id'] 	= $res_ids[$i]['id'];
						$date['is_list']		= 1;
						$date['is_add']			= 1;
						$date['is_quit']		= 1;
						$this->auth_user_obj->data($date)->add();
					}
				}else{
					$Ro_model->add(array('role_id' => 9, 'user_id' => $result));
				}
			}

		}

		if($result){
			$this->success('员工导入成功', U("user/index"));
		}else{
			$this->error('员工导入失败');
		}
	}

}
