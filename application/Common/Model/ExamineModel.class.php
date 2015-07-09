<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 审核模型,包含入职审核和离职审核
 * Class ExamineModel
 * @package Common\Model
 */
class ExamineModel extends CommonModel
{
    //管理员显示列表(入职和离职)
    function getListAll(){

    }

    //拥有一级审核权限的,列表(入职和离职)
    function getListOneEx($Model,$where,$first,$second){
        return $Model->table(C('DB_PREFIX').'users user')
            ->join('LEFT join '.C('DB_PREFIX').'department de on de.id=user.departmentid')
            ->join('join '.C('DB_PREFIX').'position po on po.id=user.positionid')
            ->join('join '.C('DB_PREFIX').'auth_user au on au.department_id=user.departmentid')
            ->where($where)
            ->order('user.id ASC')
            ->limit($first.','.$second)
            ->select();
   }

    //拥有二级审核权限的列表(入职和离职)
    function getListTwoEx($Model,$where,$first,$second){

    }
}

