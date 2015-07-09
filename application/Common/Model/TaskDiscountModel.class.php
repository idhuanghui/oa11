<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 折比管理
 * Class TaskDiscountModel
 * @package Common\Model
 */
class TaskDiscountModel extends CommonModel{

        protected $_validate = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('name', 'require', '折比名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH  ),
        );
        
	//用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private
	function mGetDate() {
                return date('Y-m-d H:i:s');
	}

}

