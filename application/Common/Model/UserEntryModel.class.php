<?php
namespace Common\Model;
use Common\Model\CommonModel;
class UserEntryModel extends CommonModel
{
    /**
     * 用户入职审核
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
	function getEntryOne($Model,$id){
		return $Model->table(C('DB_PREFIX')."users user")
			->field('user.*,po.id as pid,po.name as pname,de.id as did,de.name as dname')
			->join('LEFT JOIN '.C('DB_PREFIX').'position po on po.id=user.positionid')
			->join('JOIN '.C('DB_PREFIX').'department de on de.id=user.departmentid')
			->where(array('user.id'=>$id))
			->find();
	}
}

