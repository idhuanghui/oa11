<?php
namespace Common\Model;
use Common\Model\CommonModel;
class DepartmentModel extends CommonModel
{
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '部门名称不能为空！', 1, 'regex', CommonModel:: MODEL_BOTH  ),
        array('position_category', 'check_position_category', '请选择岗位组！', 1, 'callback'),
        array('cuttype', 'check_cuttype', '请选择提成发放方式！', 1, 'callback'),
        array('name','check_name','此部门已经存在！',1,'callback')
        
    );
    
    //自定义验证 check_name
    protected function check_name(){
            $Position = M('Department');
            if($_POST['id']>0){
                $result = $Position
                        ->where(array("name"=>$_POST['name'],"parentid"=>$_POST['parentid']))
                        ->where('id<>'.$_POST['id'])
                        ->select();
            }else{
                $result = $Position->where(array("name"=>$_POST['name'],"parentid"=>$_POST['parentid']))->select();
            }
            if (is_array($result)){            
                    return false;
            }else{
                    return true;
            }
    }
    //自定义验证 check_position_categorys
    protected function check_position_category(){
            if($_POST['level']==2 && $_POST['position_category']<1){
                    return false;
            }else{
                    return true;
            }
    }
    //自定义验证 check_position_categorys
    protected function check_cuttype(){
            if($_POST['level']==2 && $_POST['cuttype']<1){
                    return false;
            }else{
                    return true;
            }
    }
    //用于获取时间，格式为2012-02-03 12:12:12,注意,方法不能为private
    function mGetDate() {
        return date('Y-m-d H:i:s');
    }

    /**
     * 非超管理员拥有的三级部门
     * 部门主管，督导，城市经理，区域经理
     */
    function getAllDepartments($Model,$user_id){
        if(!empty($user_id)){
            //区域经理 - 一级
            $departments = array();
            $de_area = $Model->field('id')
                ->where(array('parentid' => '0','header' => $user_id))
                ->select();
            foreach ($de_area as $key => $value){
                $departments = array_merge($departments,$this->getAreaDepartments($Model,$value['id']));
            }
            //城市经理
            $de_city = $Model->field('id')
                ->where(array('header' => $user_id))
                ->where('parentid <> 0')
                ->select();
            foreach ($de_city as $key => $value){
                $departments = array_merge($departments,$this->getCityDepartments($Model,$value['id']));
            }
            //督导
            $de_manager = $Model->field('id')
                ->where(array('manager' => $user_id))
                ->select();
            foreach ($de_manager as $key => $value){
                $departments[] = $value['id'];
            }
            //部门主管
            if(session('level')==1){
                $departments[] = session('departmentid');
            }
            //去重
            $departments = array_unique($departments);
            return $departments;
        }
    }

    /**
     * 获取一级下所有三级部门
     */
    protected function getAreaDepartments($Model,$id){
        if(!empty($id)){
            //区域经理 - 一级
            $departments = array();
            $de_city = $Model->field('id')
                ->where(array('parentid' => $id))
                ->select();

            foreach ($de_city as $key => $value){
                $departments = array_merge($departments,$this->getCityDepartments($Model,$value['id']));
            }
            return $departments;
        }
    }
    /**
     * 获取二级下所有三级部门
     */
    protected function getCityDepartments($Model,$id){
        if(!empty($id)){
            $departments = array();
            $de = $Model->field('id')
                ->where(array('parentid' => $id))
                ->select();

            foreach ($de as $key => $value){
                $departments[] = $value['id'];
            }
            return $departments;
        }
        return false;
    }

    /**
     * 批量获取父层
     * @param $result
     * @return mixed
     */
    function getParents($Model,$result){

        $departments = array();
        foreach ($result as $key => $value){
            $departments[] = $value['parentid'];
        }
        $departments_str = implode(',', $departments);

        $parents = $Model->field('id,parentid,name')
            ->where(array('id IN(' . $departments_str . ")" ))
            ->select();
        return $parents;

    }
        
    //导出数据方法
    function departments_export($departments=array()){
        $departments_list = $departments;
        $data = array();
        foreach ($departments_list as $k=>$departments_info){
            //$data[$k]['id']               = $departments_info['id'];
            $data[$k]['area_header']        = $departments_info['area_header'];
            $data[$k]['city_header']        = $departments_info['city_header'];
            $data[$k]['manager']            = $departments_info['manager'];
            $data[$k]['area_department']    = $departments_info['area_department'];
            $data[$k]['city_department']    = $departments_info['city_department'];
            $data[$k]['name']               = $departments_info['name'];
            $data[$k]['department_heaer']   = $departments_info['department_heaer'];
            $data[$k]['member_count']       = $departments_info['member_count'];
        }

        foreach ($data as $field=>$v){
//            if($field == 'id'){
//                $headArr[]='部门ID';
//            }
            
            if($field == 'area_header'){
                $headArr[]='大区经理';
            }
            
            if($field == 'city_header'){
                $headArr[]='城市经理';
            }
            
            if($field == 'manager'){
                $headArr[]='督导';
            }
            
            if($field == 'area_department'){
                $headArr[]='一级分部';
            }
            
            if($field == 'city_department'){
                $headArr[]='二级分部';
            }
            
            if($field == 'name'){
                $headArr[]='部门';
            }
            
            if($field == 'department_heaer'){
                $headArr[]='部门负责人';
            }
            
            if($field == 'member_count'){
                $headArr[]='部门员工人数';
            }
        }

        $filename="departments_list";

        $this->_getExcel($filename,$headArr,$data);
    }


    private  function  _getExcel($fileName,$headArr,$data){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        //print_r($headArr);exit;
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        //合并单元格
        //$objPHPExcel->getActiveSheet()->mergeCells('A18:E22');
        
        //设置单元格的宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        
        //print_r($data);exit;
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }
}

