<?php
namespace Common\Model;
use Common\Model\CommonModel;
/**
 * 销售统计查询模型
 * Class SalesCountModel
 * @package Common\Model
 * zch
 */
class SalesCountModel extends CommonModel{

	/**
	 * 虚拟模型,没有对应的数据库
	 */
	Protected $autoCheckFields = false;

    /**
     * 投资统计查询(以天为单位)
     * @param $Model
     * @param $where
     * @param $first
     * @param $second
     * @return mixed
     */
    function getCountInvestmentsAllList($Model,$where,$first,$second){
        return $Model->table(C('DB_PREFIX').'hyd_investment hi')
//            ->field("hi.time_limit,DATE_FORMAT(FROM_UNIXTIME(hi.success_time),'%Y-%m-%d') as successtime,hi.unit,sum(hi.borrow_money) as type_sum,hi.success_time,hi.id as hiid,us.id as usid,us.user_realname,us.departmentid,us.idcard,us.hyd_name,us.level,us.temporary,us.entrydate,po.name as position,de.parentid,de.manager,de.name as department_name")
            ->field("hi.user_id,hi.time_limit,hi.id as hiid,DATE_FORMAT(FROM_UNIXTIME(hi.success_time),'%Y-%m-%d') as successtime,hi.unit,sum(hi.borrow_money) as type_sum,us.id as usid,us.user_realname,us.departmentid,us.idcard,us.temporary,us.hyd_name,us.hyd_id as ushydid,de.id as deid,de.parentid,de.name as department_name,po.id,po.name as poname")
            ->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hi.user_id')
            ->join(' JOIN '.C('DB_PREFIX').'department de on de.id = us.departmentid')
            ->join(' JOIN '.C('DB_PREFIX').'position po on po.id = us.positionid')
            ->where($where)
            ->limit($first,$second)
            ->group('hi.user_id, hi.time_limit,successtime')
            ->order(' successtime desc , hi.user_id desc , hi.time_limit ASC')
            ->select();
    }

    /**
     * 投资统计 (总计)
     * @param $Model
     * @param $where
     * @return mixed
     */
    function getCountInvestmentsCount($Model,$where){
        $subQuery = $Model->table(C('DB_PREFIX').'hyd_investment hi')
            ->field("hi.time_limit,DATE_FORMAT(FROM_UNIXTIME(hi.success_time),'%Y-%m-%d') as successtime,hi.unit,sum(hi.borrow_money) as type_sum,hi.success_time,us.user_realname,us.departmentid,us.idcard,us.hyd_name,us.level,us.temporary,us.entrydate,po.name as position,de.parentid,de.manager,de.name as department_name")
            ->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hi.user_id')
            ->join(' JOIN '.C('DB_PREFIX').'department de on de.id = us.departmentid')
            ->join(' JOIN '.C('DB_PREFIX').'position po on po.id = us.positionid')
            ->where($where)
            ->group('hi.user_id, hi.time_limit,successtime')
			->select(false);
        return $Model->table($subQuery.'a')->count();
    }

	/**
	 * 查询所有的业绩列表
	 * @param $Model
	 * @param $first
	 * @param $second
	 * @return mixed
	 */
	function getAllList($Model,$where,$first,$second){
		return $Model->table(C('DB_PREFIX').'hyd_investment hi')
						->field('hi.time_limit,hi.unit,sum(hi.borrow_money) as type_sum,us.user_realname,us.departmentid,us.idcard,us.hyd_name,us.level,us.temporary,us.entrydate,po.name as position,de.parentid,de.manager,de.name as department_name')
						->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hi.user_id')
						->join(' JOIN '.C('DB_PREFIX').'department de on de.id = us.departmentid')
                                                ->join(' JOIN '.C('DB_PREFIX').'position po on po.id = us.positionid')
						->where($where)
						->limit($first,$second)
                                                ->group('hi.user_id,hi.time_limit')
						->select();
	}

	function getAllCount($Model,$where){
            $subQuery = $Model->table(C('DB_PREFIX').'hyd_investment hi')
						->field('hi.time_limit,sum(hi.borrow_money) as type_sum,us.user_realname')
						->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hi.user_id')
						->join(' JOIN '.C('DB_PREFIX').'department de on de.id = us.departmentid')
						->where($where)
                                                ->group('hi.user_id,hi.time_limit')
                                                ->select(false);
            //子查询
            return $Model->table($subQuery.' a')->count();
	}
        
	function sales_export($sales = array()){
		$sales_list = $sales;
		$data = array();
		foreach ($sales_list as $k=>$v){
			$data[$k]['success_time']	= $v['begin_end'];
			$data[$k]['area_header']	= $v['area_header'];
			$data[$k]["city_header"]	= $v['city_header'];
			$data[$k]['manager']	= $v['manager'];  
			$data[$k]['area_department']	= $v['area_department'];
                        
			$data[$k]['city_department']	= $v['city_department'];
			$data[$k]['department_name']	= $v['department_name'];
                        $data[$k]['user_realname']	= $v['user_realname'];
			$data[$k]['level']	= $v['level']==1?'主管':'职员';
			$data[$k]['position']	= $v['position'];
                        
			$data[$k]['temporary']	= $v['temporary']==2?'全职':'兼职';
			$data[$k]['idcard']	= $v['idcard'];
			$data[$k]['entrydate']	= $v['entrydate'];
                        //$data[$k]['unit']	= borrowUnit($v['unit']);
			$data[$k]['time_limit']	= $v['time_limit'];
                        
			$data[$k]['type_sum']	= $v['type_sum'];

		}
		$headArr = array();
		$headArr[]='销售日期';
		$headArr[]='区域经理';
		$headArr[]='城市经理';
		$headArr[]='督导';
		$headArr[]='一级分部';
                        
		$headArr[]='二级分部';
		$headArr[]='部门名称';
                $headArr[]='销售员';
		$headArr[]='角色';
		$headArr[]='岗位名称';
                        
                $headArr[]='是否兼职';
		$headArr[]='身份证号';
		$headArr[]='入职日期';
                //$headArr[]='';
		$headArr[]='标的期限(月)';
                        
		$headArr[]='销售额';
			
		$filename="销售记录";
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
                $key += 1;
            }

            $column = 2;
            $objActSheet = $objPHPExcel->getActiveSheet();
            //合并单元格
            //$objPHPExcel->getActiveSheet()->mergeCells('A18:E22');

            //设置单元格的宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
            
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

