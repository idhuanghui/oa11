<?php
namespace Common\Model;
use Common\Model\CommonModel;
class UserLeaveModel extends CommonModel
{
    /**
     * 用户离职审核
     * @param $Model
     * @param $id
     * @return mixed
     */
    //获取用户的审核信息
    function getExList($Model,$id){
        return $Model->table(C('DB_PREFIX').'user_entry ueq')
            ->field('ueq.*,user.id as uid,user.user_login as uname,users.user_login as sname')
            ->join('LEFT join '.C('DB_PREFIX').'users user on user.id=ueq.userid ')
            ->join(' join '.C('DB_PREFIX').'users users on users.id=ueq.creater ')
            ->where('ueq.userid='.$id)
            ->order("createtime DESC")
            ->select();
    }

	//获取被离职审核的员工的信息
	function getLeaveOne($Model,$id){
		return $Model->table(C('DB_PREFIX').'user_leave ul')
			->field('ul.*,ul.id as ulid,us.*,us.id as usid,po.id as pid,po.name as pname,de.id as did,de.name as dname')
			->join('LEFT join '.C('DB_PREFIX').'users us on us.id=ul.userid')
			->join('LEFT JOIN '.C('DB_PREFIX').'position po on po.id=us.positionid')
			->join('JOIN '.C('DB_PREFIX').'department de on de.id=us.departmentid')
			->where(array('ul.id'=>$id))
			->find();
	}

	//管理员获取员工列表
	function getList($Model,$where,$first,$second){
		return $Model->table(C('DB_PREFIX').'user_leave ul')
			->field('ul.*,ul.id as ulid,us.*,us.id as usid,de.id as did,de.parentid,de.name as dname,de.ishead,de.ishr,de.isfinance,po.id as pid,po.name as pname,de.header')
			->join('LEFT join '.C('DB_PREFIX').'users us on us.id=ul.userid')
			->join('join '.C('DB_PREFIX').'department de on de.id=us.departmentid ')
			->join('join '.C('DB_PREFIX').'position po on po.id=us.positionid')
			->where($where)
			->order("createtime DESC")
			->limit($first. ',' . $second)
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
		return $Model->table(C('DB_PREFIX').'user_leave ul')
			->field('ul.*,ul.id as ulid,us.*,us.id as usid,de.id as did,de.parentid,de.name as dname,de.ishead,de.ishr,de.isfinance,po.id as pid,po.name as pname,de.header')
			->join('LEFT join '.C('DB_PREFIX').'users us on us.id=ul.userid')
			->join('join '.C('DB_PREFIX').'department de on de.id=us.departmentid')
			->join('join '.C('DB_PREFIX').'position po on po.id=us.positionid')
			->join('join '.C('DB_PREFIX').'auth_user au on au.department_id=us.departmentid')
			->where($where)
			->order("createtime DESC")
			->limit($first. ',' . $second)
			->select();

	}

	/**
	 * 获取用户详情和用户所在岗位名称
	 * @param $Model
	 * @param $id		用户ID
	 * @return mixed
	 */
	function getUserInfo($Model,$id){
		return $Model->table(C('DB_PREFIX').'user_leave ul')
			->field('ul.*,ul.id as ulid,us.*,us.id as usid,de.id,de.name,po.id as poid,po.name as poname')
			->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id=ul.userid')
			->join('join '.C('DB_PREFIX').'department de on de.id=us.departmentid')
			->join('join '.C('DB_PREFIX').'position po on po.id = us.positionid')
			->where('ul.id='.$id)
			->find();
	}

}

