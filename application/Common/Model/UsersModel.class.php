<?php
namespace Common\Model;
use Common\Model\CommonModel;
class UsersModel extends CommonModel
{
	
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('departmentid','checklevel','请选择三级部门！',1,'callback'),
            array('user_realname', 'require', '用户姓名不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
			array('user_realname', 'checkHydTrueName', '姓名与汇盈贷姓名不一致！', 1, 'callback', CommonModel:: MODEL_INSERT  ),
            array('payroll_try', 'require', '岗位工薪不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
			array('user_login', 'require', '用户名称不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE  ),
            array('acc_address', 'require', '户口所在地不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
            array('hyd_name', 'require', '汇盈贷账号不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
            array('hyd_name', 'checkHydName', '汇盈贷账号不正确或已存在！', 1, 'callback', CommonModel:: MODEL_INSERT  ),
            array('bank_address', 'require', '工资卡开户行不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
            array('bank_user', 'require', '工资卡开户姓名不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
            array('bank_num', 'require', '工资卡开户账号不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
            array('entrydate', 'require', '入职日期不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
			array('user_login', 'require', '手机号码不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
			array('user_login', 'checkMobile', '手机号码格式不正确！', 1, 'callback', CommonModel:: MODEL_BOTH ),
			array('user_login','onlyCheckPhone','手机号码已经存在！',1,'callback',CommonModel:: MODEL_INSERT ), // 验证user_login字段是否唯一
//			array('user_login','','手机号码已经存在！',0,'unique',CommonModel:: MODEL_INSERT ), // 验证user_login字段是否唯一
//			array('user_email','','邮箱帐号已经存在！',0,'unique',CommonModel::MODEL_BOTH ), // 验证user_email字段是否唯一
			array('user_email','onlyCheckEmail','邮箱帐号已经存在！',1,'callback',CommonModel::MODEL_INSERT ), // 验证user_email字段是否唯一
			array('idcard', 'require', '身份证不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
//			array('idcard', 'checkIdcard', '身份证号码格式不正确！', 1, 'callback', CommonModel:: MODEL_BOTH ),
//			array('idcard','onlyCheckIdcard','身份证号码已存在！',1,'callback',CommonModel::MODEL_INSERT ), // 验证user_email字段是否唯一
//			array('idcard', 'checkHydIdcard', '身份证号码不正确！', 1, 'callback', CommonModel:: MODEL_INSERT ),
			array('user_email','email','邮箱格式不正确！',0,'',CommonModel:: MODEL_BOTH ), // 验证user_email字段格式是否正确
			array('acc_province', 'require', '社保归属地不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
			array('education', 'require', '学历不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
			array('specialty', 'require', '专业不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
	);

    protected $_auto = array (
		array('user_pass','setPass',self::MODEL_INSERT,'callback'),
        array('create_time', 'time', self::MODEL_BOTH, 'function'),
        array('last_login_time', 'time', self::MODEL_UPDATE, 'function'),
		array('hyd_id', 'checkHydName', self::MODEL_INSERT, 'callback'),			//将通过汇盈贷的账号获取到的ID,写入到数据库
		array('idcard', 'getHydIdcard', self::MODEL_INSERT, 'callback'),
	);
    
    //自定义验证 checklevel
    protected function checklevel(){
        $Department = D('Common/Department');
        $result = $Department->order(array("listorder" => "ASC"))->select();
        $newdepartments=array();
        foreach ($result as $m){
            $newdepartments[$m['id']]=$m; 
        }

        $level = _get_level($_POST['departmentid'], $newdepartments);
            
	// 新用户注册，验证唯一
        if(empty($_POST['departmentid'])||$level!=2){
                return false;
        }else{
                return true;
        }
    }


	//验证手机号码正确性
	function checkMobile(){
		$user_login = trim($_POST['user_login']);
		if(strlen($user_login) == 11){
			$resultStr = preg_match('/^(1(([35][0-9])|(47)|[8][01256789]))\d{8}$/',$user_login);
		}
		if(intval($resultStr)){
			return true;
		}else{
			return false;
		}
	}

	//手机号码验证唯一性
	function onlyCheckPhone(){
		$user_login = trim($_POST['user_login']);
		$Model = M('Users');
		$res = $Model->where("user_login='$user_login' and (user_status != 'E11' AND user_status != 'E21' AND user_status != 'E3' AND user_status != 'Q' )")->find();
		if($res){
			return false;
		}else{
			return true;
		}
	}

	//邮箱验证唯一性
	function onlyCheckEmail(){
		$email = trim($_POST['user_email']);
		$Model = M('Users');
		$res = $Model->where("user_email='$email' and (user_status != 'E11' AND user_status != 'E21' AND user_status != 'E3' AND user_status != 'Q' )")->find();
		if($res){
			return false;
		}else{
			return true;
		}
	}

	//身份证号码唯一性验证
	function onlyCheckIdcard(){
		$idcard = trim($_POST['idcard']);
		$Model = M('Users');
		$res = $Model->where("idcard='$idcard' and (user_status != 'E11' AND user_status != 'E21' AND user_status != 'E3' AND user_status != 'Q' )")->find();
		if($res){
			return false;
		}else{
			return true;
		}
	}

	//正则验证身份证/(^([d]{15}|[d]{18}|[d]{17}x)$)/
	function checkIdcard(){
		$idcard = trim($_POST['idcard']);
		if(strlen($idcard) == 18){
			$resultStr = preg_match("/^[1-9]\\d{5}[1-9]\\d{3}((0\\d)|(1[0-2]))(([0|1|2]\\d)|3[0-1])\\d{3}(\\d|x|X)$/",$idcard);
		}elseif(strlen($idcard) == 15){
			$resultStr = preg_match("/^[1-9]\\d{7}((0\\d)|(1[0-2]))(([0|1|2]\\d)|3[0-1])\\d{3}$/",$idcard);
		}
		if(intval($resultStr) == 1){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	/**
	 * 通过传来的汇盈贷账户名, 检测账户在汇盈贷是否存在,并取到对应的ID.
	 * @return bool
	 */
	function checkHydName(){
		$url = C('INTERFACE_URL');
		$date = array();
		$date['module'] 	= 'oa';
		$date['q'] 			= 'personalOA';
		$date['username'] 	= trim($_POST['hyd_name']);
		$res = POST_Api($url,$date);
		$resArr = decodeUrlArr($res);
		if($resArr['error'] == '0'){
			$hydId = $resArr['data']['user_id'];
			$Model_user = M('Users');
			$res = $Model_user->field('hyd_id')->where("hyd_id='$hydId' and (user_status != 'E11' AND user_status != 'E21' AND user_status != 'E3' AND user_status != 'Q' )")->find();
			if($res){
				return false;
			}else{
				return $hydId;
			}
		}else{
			return false;
		}
	}

	/**
	 * 验证提交的身份证和汇盈贷的身份证号码是否一致
	 * @return bool
	 */
	function getHydIdcard(){
		$url = C('INTERFACE_URL');
		$date = array();
		$date['module'] 	= 'oa';
		$date['q'] 			= 'personalOA';
		$date['username'] 	= trim($_POST['hyd_name']);
		$res = POST_Api($url,$date);
		$resArr = decodeUrlArr($res);
		if($resArr['error'] == '0'){
			return $resArr['data']['idcard'];
		}else{
			return false;
		}
	}

	/**
	 * 验证提交的姓名和汇盈贷的姓名是否一致
	 * @return bool
	 */
	function checkHydTrueName(){
		$url = C('INTERFACE_URL');
		$date = array();
		$date['module'] 	= 'oa';
		$date['q'] 			= 'personalOA';
		$date['username'] 	= trim($_POST['hyd_name']);
		$res = POST_Api($url,$date);
		$resArr = decodeUrlArr($res);
		if($resArr['error'] == '0'){
			$hydTrueName = $resArr['data']['truename'];
			$trueName = trim($_POST['user_realname']);
			if($hydTrueName == $trueName){
				return true;
			}else{
				return false;
			}
		}
	}

	//用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private
	function mGetDate() {
		return date('Y-m-d H:i:s');
	}

	//添加时自动设置密码为手机号码后六位
	protected function setPass(){
		return sp_password(substr(trim($_POST['user_login']),-6,6));
	}
	
	protected function _before_write(&$data) {
		parent::_before_write($data);
		
		if(!empty($data['user_pass']) && strlen($data['user_pass'])<25){
			$data['user_pass']=sp_password($data['user_pass']);
		}
	}

    /**
     * 获取用户所在部门
     * @param $obj
     * @param $id
     * @return array
     */
    function getDepartmentName($obj,$id){ //3级
        $department = array();
        $department = $obj->where(array("id"=>$id))->find();
        return  $department;
    }

	/**
	 * 管理员获取员工列表
	 * @param $Model
	 * @param $where	查询条件
	 * @param $first
	 * @param $second
	 * @return mixed
	 */
    function getList($Model,$where,$first,$second){
        return $Model->table(C('DB_PREFIX').'users user')
                ->field('user.*,user.id as useid,de.id as did,de.parentid,de.name as dname,de.ishead,de.ishr,de.isfinance,po.id as pid,po.name as pname,de.header')
                ->join('LEFT join '.C('DB_PREFIX').'department de on de.id=user.departmentid ')
                ->join('join '.C('DB_PREFIX').'position po on po.id=user.positionid')
                ->where($where)
                ->order("create_time DESC")
                ->limit($first. ',' . $second)
                ->select();
    }

	//查询导出列表
	function getAllList($Model,$where){
		return $Model->table(C('DB_PREFIX').'users user')
			->field('user.*,user.id as useid,de.id as did,de.parentid,de.name as dname,de.ishead,de.ishr,de.isfinance,po.id as pid,po.name as pname,de.header')
			->join('LEFT join '.C('DB_PREFIX').'department de on de.id=user.departmentid ')
			->join('join '.C('DB_PREFIX').'position po on po.id=user.positionid')
			->where($where)
			->order("create_time DESC")
//			->limit($first. ',' . $second)
			->select();
	}

    /**
     * 其他人员获取员工列表。
     * 根据不同的条件,可以分别获取入职一级审核,入职二级审核,离职一级审核,离职二级审核等需要审核的员工.
     * @param $Model
     * @param $where
     * @param $first
     * @param $second
     * @return mixed
     */
    function getAppointList($Model,$where,$first,$second){
        return $users = $Model->table(C('DB_PREFIX')."users user")
            ->field('user.*,de.id as did,de.parentid,de.name as dname,de.ishead,de.ishr,de.isfinance,po.id as pid,po.name as pname,de.header')
            ->join('LEFT join '.C('DB_PREFIX').'department de on de.id=user.departmentid')
            ->join('join '.C('DB_PREFIX').'position po on po.id=user.positionid')
            ->join('join '.C('DB_PREFIX').'auth_user au on au.department_id=user.departmentid')
            ->where($where)
            ->order("create_time DESC")
            ->limit($first. ',' . $second)
            ->select();
    }

	//查询导出列表
	function getAllAppointList($Model,$where){
		return $users = $Model->table(C('DB_PREFIX')."users user")
			->field('user.*,de.id as did,de.parentid,de.name as dname,de.ishead,de.ishr,de.isfinance,po.id as pid,po.name as pname,de.header')
			->join('LEFT join '.C('DB_PREFIX').'department de on de.id=user.departmentid')
			->join('join '.C('DB_PREFIX').'position po on po.id=user.positionid')
			->join('join '.C('DB_PREFIX').'auth_user au on au.department_id=user.departmentid')
			->where($where)
			->order("create_time DESC")
			->select();
	}

	/**
	 * 获取用户组ID
	 * @param $Model
	 * @param $uid		用户ID
	 * @return mixed
	 */
    function getRoleId($Model,$uid){
        return $Model->table(C('DB_PREFIX').'users user')
            ->field('user.positionid,ro.role_id,ro.user_id')
            ->join('LEFT join '.C('DB_PREFIX').'role_user ro on ro.user_id='.$uid)
            ->select();
    }

	/**
	 * 获取用户组ID ,单条
	 * @param $Model
	 * @param $uid		用户ID
	 * @return mixed
	 */
    function getRoleIdOne($Model,$uid){
        return $Model->table(C('DB_PREFIX').'users user')
            ->field('user.positionid,ro.role_id,ro.user_id')
            ->join('LEFT join '.C('DB_PREFIX').'role_user ro on ro.user_id='.$uid)
            ->order("create_time DESC")
            ->find();
    }

	/**
	 * 获取用户详情
	 * @param $Model
	 * @param $id		用户ID
	 * @return mixed
	 */
    function getUserInfo($Model,$id){
        return $Model->table(C('DB_PREFIX').'users user')
            ->field('user.*,de.id,de.name,po.id as poid,po.name as poname')
            ->join('LEFT join '.C('DB_PREFIX').'department de on de.id=user.departmentid')
			->join('join '.C('DB_PREFIX').'position po on po.id = user.positionid')
            ->where('user.id='.$id)
            ->find();
    }

	/**
	 * 获取用户详情和用户所在岗位名称
	 * @param $Model
	 * @param $id		用户ID
	 * @return mixed
	 */
	function getUserPosition($Model,$id){
		return $Model->table(C('DB_PREFIX').'users user')
			->field('user.id as usid,user.positionid,po.id as pid,po.name as pname')
			->join('LEFT join '.C('DB_PREFIX').'position po on po.id=user.positionid')
			->where('user.id='.$id)
			->find();
	}

	/**
	 * 获取所有第三级部门的ID
	 * 添加总部人事Leader时需要为所有的部门默认分配权限
	 */
	function getAllThird($Model){
		//获取所有的第三级的ID,添加人事的Leader时需要默认为所有三级部门添加默认权限
		$departmentres = $Model->field('id,parentid,name')->select();
		$newdepartments=array();
		foreach ($departmentres as $m){
			$newdepartments[$m['id']]=$m;
		}
		foreach ($departmentres as $n=> $r) {
			$departmentres[$n]['level'] = _get_level($r['id'], $newdepartments);
			if($departmentres[$n]['level'] == 2){
				$allthird[] = $departmentres[$n]['id'];
			}

		}
		return $allthird;
	}

	/**
	 * 查找城市经理
	 * @param $Model
	 * @param $id
	 * @return mixed
	 */
	function getHeader($Model,$id){
		return $Model->table(C('DB_PREFIX').'department de')
			->field('de.id,de.name,de.parentid,de.header,de.manager,us.id,us.user_realname')
			->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = de.header')
			->where('de.id='.$id)
			->find();
	}

    /**
     * 查询离职时间
     * (根据用户ID 查询用户的离职时间)
     * @param $Model
     * @param $id
     * @return mixed
     */
    function getLeaveTime($Model,$id){
        return $Model->field('id,userid,leave_time')->where(array('userid'=>$id))->find();
    }

    /**
     * 查找部门督导
     * @param $Model
     * @param $id
     * @return mixed
     */
    function getManager($Model,$id){
        return $Model->table(C('DB_PREFIX').'department de')
            ->field('de.id,de.manager,us.user_realname')
            ->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = de.manager')
            ->where('de.id='.$id)
            ->find();
    }

    /**
     * 获取用户的基本信息
     * (根据用户的ID查询,使用时传入用户ID)
     * @param $userid   (用户ID)
     * @return array
     */
    function getBasicUsersInfo($userid){
        $User_Model = M('Users');
        $Depart_Model = M('Department');
        $Leave_Model = M('UserLeave');
        $user_info = array();
        if(is_array($userid)){
            foreach($userid as $k=>$v){
                $result_users = array();
                $result_users = $User_Model->where(array('id' => $v['id']))->getField("id as uid,hyd_id,user_realname,departmentid,idcard,temporary,entrydate,user_status");
                if($result_users){
                    if(substr($result_users[$v['id']]['user_status'], 0, 1) == 'Q'){
                        $result_leave[$v['id']] = $this->getLeaveTime($Leave_Model,$result_users[$v['id']]['uid']);
                    }
                    $userPos = $this->getUserPosition($User_Model,$result_users[$v['id']]['uid']);
                    $user_info[$v['id']]['poname'] = $userPos['pname'];
                    $user_info[$v['id']]['uid'] = $result_users[$v['id']]['uid'];
                    $user_info[$v['id']]['idcard'] = $result_users[$v['id']]['idcard'];
                    $user_info[$v['id']]['temporary'] = $result_users[$v['id']]['temporary'];
                    $user_info[$v['id']]['entrydate'] = $result_users[$v['id']]['entrydate'];
                    $user_info[$v['id']]['leave_time'] = $result_leave[$v['id']]['leave_time'];
                    $local_data = $this->getHeader($Depart_Model, $result_users[$v['id']]['departmentid']);
                    $user_info[$v['id']]['department_name'] = $local_data['name'];
                    $de_manager = $User_Model->field('id,user_realname')->where(array('id' => $local_data['manager']))->find();
                    $user_info[$v['id']]['manager'] = $de_manager['user_realname'];
                    $city_data = $this->getHeader($Depart_Model, $local_data['parentid']);
                    $user_info[$v['id']]['city_header'] = $city_data['user_realname'];
                    $user_info[$v['id']]['city_department'] = $city_data['name'];
                    $area_data = $this->getHeader($Depart_Model, $city_data['parentid']);
                    $user_info[$v['id']]['area_header'] = $area_data['user_realname'];
                    $user_info[$v['id']]['area_department'] = $area_data['name'];
                }
            }
        }
        return $user_info;
    }

    /**
     * 获取用户基本信息(通过HYD_ID),主要用户接口
     * 客户查询,充值查询,待还查询,提现查询
     * 用于查找所在三级部门和各个部门领导人
     * @param $hyd_id   (普通员工时传入的为单个的ID,管理员时传入的为数组所有的ID)
     * @return array
     */
    function getUsersInfo($hyd_id){
        $User_Model = M('Users');
        $Depart_Model = M('Department');
        $user_info = array();
        if(is_array($hyd_id)){
            foreach($hyd_id as $k=>$v){
                $result_users = array();
                $result_users = $User_Model->where(array('hyd_id' => $v['hyd_id']))->getField("hyd_id,user_realname,departmentid,id as uid");
                if($result_users){
                    $user_info[$v['hyd_id']]['uid'] = $result_users[$v['hyd_id']]['uid'];
                    $user_info[$v['hyd_id']]['user_realname'] = $result_users[$v['hyd_id']]['user_realname'];
                    $local_data = $this->getHeader($Depart_Model, $result_users[$v['hyd_id']]['departmentid']);
                    $user_info[$v['hyd_id']]['department_name'] = $local_data['name'];
                    $de_manager = $User_Model->field('id,user_realname')->where(array('id' => $local_data['manager']))->find();
                    $user_info[$v['hyd_id']]['manager'] = $de_manager['user_realname'];
                    $city_data = $this->getHeader($Depart_Model, $local_data['parentid']);
                    $user_info[$v['hyd_id']]['city_header'] = $city_data['user_realname'];
                    $user_info[$v['hyd_id']]['city_department'] = $city_data['name'];
                    $area_data = $this->getHeader($Depart_Model, $city_data['parentid']);
                    $user_info[$v['hyd_id']]['area_header'] = $area_data['user_realname'];
                    $user_info[$v['hyd_id']]['area_department'] = $area_data['name'];
                }
            }

        }else{
            $result_users = $User_Model->where(array('hyd_id' => $hyd_id))->getField("hyd_id,user_realname,departmentid");
            if($result_users){
                $user_info['user_realname'] = $result_users[$hyd_id]['user_realname'];
                $local_data = $this->getHeader($Depart_Model, $result_users[$hyd_id]['departmentid']);
                $user_info['department_name'] = $local_data['name'];
                $de_manager = $User_Model->field('id,user_realname')->where(array('id' => $local_data['manager']))->find();
                $user_info['manager'] = $de_manager['user_realname'];
                $city_data = $this->getHeader($Depart_Model, $local_data['parentid']);
                $user_info['city_header'] = $city_data['user_realname'];
                $user_info['city_department'] = $city_data['name'];
                $area_data = $this->getHeader($Depart_Model, $city_data['parentid']);
                $user_info['area_header'] = $area_data['user_realname'];
                $user_info['area_department'] = $area_data['name'];
            }
        }
        return $user_info;
    }
    /**
     * 业绩查询
     * 用于查找所在三级部门和各个部门领导人
     * @param $hyd_id   (普通员工时传入的为单个的ID,管理员时传入的为数组所有的ID)
     * @return array
     */
    function getUsersInfo2($hyd_id){
        $User_Model = M('Users');
        $Depart_Model = M('Department');
        $user_info = array();
        if(is_array($hyd_id)){
            foreach($hyd_id as $k=>$v){
                $result_users = array();
                $result_users = $User_Model->where(array('id' => $v['id']))->getField("id,user_realname,departmentid");
                if($result_users){
                    $user_info[$v['id']]['user_realname'] = $result_users[$v['id']]['user_realname'];
                    $local_data = $this->getHeader($Depart_Model, $result_users[$v['id']]['departmentid']);
                    $user_info[$v['id']]['department_name'] = $local_data['name'];
                    $de_manager = $User_Model->field('id,user_realname')->where(array('id' => $local_data['manager']))->find();
                    $user_info[$v['id']]['manager'] = $de_manager['user_realname'];
                    $city_data = $this->getHeader($Depart_Model, $local_data['parentid']);
                    $user_info[$v['id']]['city_header'] = $city_data['user_realname'];
                    $user_info[$v['id']]['city_department'] = $city_data['name'];
                    $area_data = $this->getHeader($Depart_Model, $city_data['parentid']);
                    $user_info[$v['id']]['area_header'] = $area_data['user_realname'];
                    $user_info[$v['id']]['area_department'] = $area_data['name'];
                }
            }

        }
        return $user_info;
    }
}

