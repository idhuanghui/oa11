<?php
namespace Common\Model;
use Common\Model\CommonModel;
class PositionCategoryModel extends CommonModel
{
	protected $_validate = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('name', 'require', '岗位类别名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH  ),
            array('name','check_name','此类别已经存在！',1,'callback')
	);
        //自定义验证 check_name
        protected function check_name(){
                $PositionCategory = M('PositionCategory');
                if($_POST['id']>0){
                    $result = $PositionCategory
                            ->where(array("name"=>$_POST['name']))
                            ->where('id<>'.$_POST['id'])
                            ->select();
                }else{
                    $result = $PositionCategory->where(array("name"=>$_POST['name']))->select();
                }
                if (is_array($result)){            
                        return false;
                }else{
                        return true;
                }
        }
	//用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private
	function mGetDate() {
            return date('Y-m-d H:i:s');
	}	
}

