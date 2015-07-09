<?php
/**
 * 绩效统计模型(虚拟模型).
 * User: Yan
 * Date: 2015/6/26
 * Time: 9:18
 */

namespace Common\Model;
use Common\Model\CommonModel;

class AchievementsModel extends CommonModel {
    Protected $autoCheckFields = false;


    /**
     * @param $Model
     * @param $time
     * @return mixed
     */
    function getTaskRatio($Model,$time){
        $res = $Model->table(C('DB_PREFIX').'task tk')
            ->field('tk.id as tkid,tk.disnum,tk.begintime,tk.endtime,tdd.id,tdd.discountid,tdd.time_limit,tdd.ratio')
            ->join('LEFT JOIN '.C('DB_PREFIX').'task_discount_detail tdd on tdd.discountid = tk.disnum')
            ->where(array('tk.tasknum'=>$time))
            ->select();
        return $res;
    }

    /**
     * 查询业绩信息
     * @param $Model
     * @param $id
     * @param $where
     * @return mixed
     */
    function getInvestment($Model,$id,$where){
        $res = $Model->table(C('DB_PREFIX').'hyd_investment')
            ->field('id,user_id,hyd_id,success_time,time_limit,borrow_money')
            ->where('user_id='.$id)
            ->where($where)
            ->select();
        return $res;
    }
    /**
     * 统计总数
     * @param $Model
     * @param $where
     * @return mixed
     */
    function getUserInvestmentCount($Model,$where){
        $res = $Model->table(C('DB_PREFIX').'users us')
            ->field('us.id,us.user_realname,us.departmentid,us.temporary,us.hyd_id,hi.reg_num,hi.open_num,hi.this_num1,hi.this_num2,hi.this_num3')
            ->join('LEFT JOIN '.C('DB_PREFIX').'hyd_achievements hi on hi.user_id = us.hyd_id')
            ->where($where)
            ->where('us.id > 1')
            ->count();
        return $res;
    }

    /**
     * 查询用户基本信息及业绩信息
     * @param $Model
     * @param $where
     * @param $first
     * @param $second
     * @return mixed
     */
    function getUserInvestment($Model,$where,$first,$second){
        $res = $Model->table(C('DB_PREFIX').'users us')
            ->field('us.id,us.user_realname,us.departmentid,us.temporary,us.hyd_id,hi.reg_num,hi.open_num,hi.this_num1,hi.this_num2,hi.this_num3')
            ->join('LEFT JOIN '.C('DB_PREFIX').'hyd_achievements hi on hi.user_id = us.hyd_id')
            ->where($where)
            ->where('us.id > 1')
            ->limit($first, $second)
            ->select();
        return $res;
    }
}
