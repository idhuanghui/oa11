<?php
namespace Common\Model;
use Common\Model\CommonModel;
/**
 * 销售业绩查询模型
 * Class SalesModel
 * @package Common\Model
 */
class SalesModel extends CommonModel{

	/**
	 * 虚拟模型,没有对应的数据库
	 */
	Protected $autoCheckFields = false;

	/**
	 * 查询所有的业绩列表
	 * @param $Model
	 * @param $first
	 * @param $second
	 * @return mixed
	 */
	function getAllList($Model,$where,$first,$second){
		return $Model->table(C('DB_PREFIX').'hyd_investment hi')
						->field('hi.*,hi.id as hiid,us.id as usid,us.user_realname,us.departmentid,us.hyd_name,us.hyd_id as ushydid,de.id as deid,de.parentid,de.name as department_name')
						->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hi.user_id')
						->join(' JOIN '.C('DB_PREFIX').'department de on de.id = us.departmentid')
						->where($where)
						->limit($first,$second)
						->order('hi.success_time DESC')
						->select();
	}

	function getAllCount($Model,$where){
		return $Model->table(C('DB_PREFIX').'hyd_investment hi')
						->field('hi.*,hi.id as hiid,us.id as usid,us.user_realname,us.departmentid,us.hyd_name,us.hyd_id as ushydid,de.id as deid,de.parentid,de.name')
						->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hi.user_id')
						->join(' JOIN '.C('DB_PREFIX').'department de on de.id = us.departmentid')
						->where($where)
						->count();
	}
        
        
	/**
	 * 其他员工根据条件获取自己的业绩列表
	 * @param $Model
	 * @param $first
	 * @param $second
	 * @return mixed
	 */
	function getAppointList($Model,$where,$first,$second){
		return $Model->table(C('DB_PREFIX').'hyd_investment hi')
						->field('hi.success_time,hi.customer,hi.username,hi.borrow_nid,hi.borrow_name,
                                                        hi.frozen_time,hi.recover_time,hi.unit,hi.time_limit,hi.borrow_money,
                                                        hi.borrow_type,hi.borrow_apr,hi.nid,us.user_realname,us.hyd_name,
                                                        de.parentid,de.name as department_name')
						->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hi.user_id')
						->join('join '.C('DB_PREFIX').'department de on de.id = us.departmentid')
//						->where('hi.user_id='.session('ADMIN_ID'))
						->where($where)
						->limit($first,$second)
						->order('hi.success_time DESC')
						->select();
	}
        
	function getAppointCount($Model,$where){
		return $Model->table(C('DB_PREFIX').'hyd_investment hi')
						->field('hi.*,hi.id as hiid,us.id as usid,us.user_realname,us.departmentid,us.hyd_name,us.hyd_id as ushydid,de.id as deid,de.parentid,de.name')
						->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hi.user_id')
						->join('join '.C('DB_PREFIX').'department de on de.id = us.departmentid')
						->where($where)
						->count();
	}

	function sales_export($sales = array()){
		$sales_list = $sales;
		$data = array();
		foreach ($sales_list as $k=>$v){
			$data[$k]['success_time']	= date('Y-m-d H:i:s', $v['success_time']);
			$data[$k]['area_header']	= $v['area_header'];
			$data[$k]["city_header"]	= $v['city_header'];
			$data[$k][]	= $v[''];  
			$data[$k]['area_department']	= $v['area_department'];
                        
			$data[$k]['city_department']	= $v['city_department'];
			$data[$k]['department_name']	= $v['department_name'];
			$data[$k]['customer']	= $v['customer'];
			$data[$k]['username']	= $v['username'];
			$data[$k]['borrow_nid']	= $v['borrow_nid'];
                        
			$data[$k]['borrow_name']	= $v['borrow_name'];
			$data[$k]['frozen_time']	= date('Y-m-d H:i:s', $v['frozen_time']);
			$data[$k]['recover_time']	= date('Y-m-d H:i:s', $v['recover_time']);
			$data[$k]['time_limit']	= borrowLimit($v['time_limit']);
			//$data[$k]['unit']	= borrowUnit($v['unit']);
                        
			$data[$k]['borrow_apr']	= $v['borrow_apr'];
			$data[$k]['borrow_money']	= $v['borrow_money'];
			$data[$k]['borrow_type']	= borrowType($v['borrow_type']);
			$data[$k]['user_realname']	= $v['user_realname'];

		}
                $headArr = array();
		$headArr[]= '销售日期';
		$headArr[]='区域经理';
                $headArr[]='城市经理';
		$headArr[]='督导';
                $headArr[]='一级分部';
                        
		$headArr[]='二级分部';
		$headArr[]='部门名称';
		$headArr[]='客户姓名';
		$headArr[]='客户账号';
		$headArr[]='标的编号';
                        
		$headArr[]='标的名称';
		$headArr[]='投标冻结时间';
		$headArr[]='投标到期时间';
		$headArr[]='期限';
		//$headArr[]='标期单位';
                        
		$headArr[]='利率';
		$headArr[]='投标金额';
		$headArr[]='标的类型';
		$headArr[]='销售员';

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
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(45);  
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);

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

