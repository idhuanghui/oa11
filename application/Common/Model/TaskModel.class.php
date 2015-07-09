<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 任务管理
 * Class TaskModel
 * @package Common\Model
 */
class TaskModel extends CommonModel{

	protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('yearnum', 'require', '任务年不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH  ),
        array('monthnum', 'require', '任务月不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH  ),
        array('begintime', 'require', '开始日期不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH  ),
        array('endtime', 'require', '结束日期不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH  ),
        array('tasknum', 'ckeckTaskNum', '任务编号已存在！', 1, 'callback', CommonModel:: MODEL_INSERT ),
	);

    protected $_auto = array(
        array('tasknum', 'getTaskNum', self::MODEL_BOTH, 'callback'),
        array('begintime', 'getStartTime', self::MODEL_BOTH, 'callback'),
        array('endtime', 'getEndTime', self::MODEL_BOTH, 'callback'),
    );

    //拼接任务编号
    function getTaskNum(){
        $year = trim($_POST['yearnum']);
        $month = trim($_POST['monthnum']);

        $tasknum = $year.$month;
        return $tasknum;
    }

    //验证任务编号唯一性
    function ckeckTaskNum(){
        $TaskModel = M('Task');
        $tasknum = trim($_POST['yearnum']).trim($_POST['monthnum']);
        $res = $TaskModel->where('tasknum='.$tasknum)->find();
        if($res){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 将任务开始时间转换成时间戳(0:0:0)
     */
    function getStartTime(){
        return strtotime($_POST['begintime']);
    }

    /**
     * 将任务结束时间格式化成时间戳(23:59:59)
     * @return int
     */
    function getEndTime(){
        return strtotime($_POST['endtime'])+24*60*60-1;
    }
}

