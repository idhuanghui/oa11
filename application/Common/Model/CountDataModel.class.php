<?php
namespace Common\Model;
use Common\Model\CommonModel;
/**
 * 数据统计查询模型
 * Class CountDataModel
 * @package Common\Model
 * zch
 */
class CountDataModel extends CommonModel{

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
		return $Model->table(C('DB_PREFIX').'hyd_report hr')
						->field('hr.*,us.user_realname,us.departmentid,us.idcard,us.hyd_name,us.level,us.temporary,us.entrydate,de.parentid,de.manager,de.name as department_name')
						->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hr.user_id')
						->join(' JOIN '.C('DB_PREFIX').'department de on de.id = us.departmentid')
						->where($where)
                                                ->order("hr.query_date Desc")
						->limit($first,$second)
						->select();
	}

	function getAllCount($Model,$where){
            $subQuery = $Model->table(C('DB_PREFIX').'hyd_report hr')
						->field('us.user_realname')
						->join('LEFT JOIN '.C('DB_PREFIX').'users us on us.id = hr.user_id')
						->join(' JOIN '.C('DB_PREFIX').'department de on de.id = us.departmentid')
						->where($where)
                                                ->select(false);
            //子查询
            return $Model->table($subQuery.' a')->count();
	}
        
	function sales_export($sales = array()){
		$sales_list = $sales;
		$data = array();
		foreach ($sales_list as $k=>$v){
			$data[$k][]         = $v['query_date'];
			$data[$k][]	= $v['area_header'];
			$data[$k][]	= $v['city_header'];
			$data[$k][]            = $v['manager'];  
			$data[$k][]	= $v['area_department'];
                        
			$data[$k][]	= $v['city_department'];
			$data[$k][]	= $v['department_name'];
                        $data[$k][]            = $v['reg_num'];
			$data[$k][]	= $v['reg_recharge_num'];
			$data[$k][]     = $v['reg_recharge_amount'];
                        
			$data[$k][]	= $v['reg_recharge_times'];
			$data[$k][]	= $v['reg_tender_num'];
			$data[$k][]	= $v['reg_tender_amount'];
                        $data[$k][]	= $v['reg_tender_times'];
			$data[$k][]	= $v['first_recharge_num'];
                        
			$data[$k][]       = $v['first_tender_num'];
                        $data[$k][]     = $v['total_recharge_num'];
                        $data[$k][]   = $v['total_recharge_times'];
                        $data[$k][]  = $v['total_recharge_amount'];
                        $data[$k][]       = $v['total_tender_num'];
                        
                        $data[$k][]         = $v['total_tender_times'];
                        $data[$k][]        = $v['total_tender_amount'];
                        $data[$k][]          = $v['total_recover_num'];
                        $data[$k][]       = $v['total_recover_amount'];
                        $data[$k][]         = $v['total_withdraw_num'];
                        
                        $data[$k][]         = $v['total_withdraw_times'];
                        $data[$k][]         = $v['total_withdraw_amount'];
                        $data[$k][]          = $v['total_balance'];
                        $data[$k][]          = $v['user_realname'];
                        $data[$k][]                 = $v['userid'];
                        
//                        $data[$k]['total_withdraw_num']         = $v['total_withdraw_num'];
//                        $data[$k]['total_withdraw_num']         = $v['total_withdraw_num'];
		}
		$headArr = array();
		$headArr[]='统计日期';
		$headArr[]='区域经理';
		$headArr[]='城市经理';
		$headArr[]='督导';
		$headArr[]='一级分部';
                        
		$headArr[]='二级分部';
		$headArr[]='部门名称';
                $headArr[]='新注册人数';
		$headArr[]='新注册人充值人数';
		$headArr[]='新注册人充值金额';
                        
                $headArr[]='新注册人充值次数';
		$headArr[]='新注册人的投资人数';
		$headArr[]='新注册人的投资金额';
                $headArr[]='新注册人的投资次数';
		$headArr[]='首次充值人数';
                        
		$headArr[]='首次投资人数';
		$headArr[]='总充值人数';
                $headArr[]='总充值次数';
                $headArr[]='总充值金额';
                $headArr[]='总投资人数';
                
                $headArr[]='总投资次数';
                $headArr[]='总投资金额';
                $headArr[]='还款人数';
                $headArr[]='还款金额';
                $headArr[]='总提现人数';
                
                $headArr[]='总提现次数';
                $headArr[]='总提现金额';
                $headArr[]='用户余额';
                $headArr[]='员工';
                $headArr[]='员工编号';
                
//                $headArr[]='入职时间';
//                $headArr[]='离职时间';
                
		$filename="数据统计";
		$this->_getExcel($filename,$headArr,$data);
	}
	private function _getExcel($fileName,$headArr,$data){
		//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.Writer.Excel5");
		import("Org.Util.PHPExcel.IOFactory.php");

		$date = date("Y_m_d_H_i_s",time());
		$fileName .= "_{$date}.xls";

		//创建PHPExcel对象，注意，不能少了\
		$objPHPExcel = new \PHPExcel();
		$objProps = $objPHPExcel->getProperties();

		/**
		 * 导出Excel表头,从第一行开始写入.
		 */
		// 以下实现生成Excel单元格坐标.
		$abc          = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$firstChar      = 0;
		$flag         = false;        // 是否已经变成AA模式。
		$d              = 65;
		$headLength = count($headArr);

		for($i= 0 ;$i<$headLength;$i++){
			if ($d == 91){
				$d = 65;                    // 又将$d设置为初始化值65。
				if ($flag){
					$firstChar++;
					$offset = $abc{$firstChar} . chr($d);
				} else {
					$flag     = true;
					$offset = $abc{$firstChar} . chr($d);
				}
			}else{
				if ($flag){
					$offset = $abc{$firstChar} . chr($d);
				} else {
					$offset = chr($d);
				}
			}
			//设置单元格的值.
                        $offsets = $offset . '1';
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($offsets, $headArr[$i]);
                        $d++;
		}
                
		/**
		 * Excel内容,从第二行开始写入.
		 */
		$k = 2;
		foreach ($data as $rows){
			$abc_              = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$firstChar_     = 0;
			$flag_         	= false;
			$d_				= 65;
			$rowsLength = count($rows);
			for($a = 0;$a<$rowsLength;$a++){
				if ($d_ == 91){
					$d_ = 65;
					if($flag_){
						$firstChar_++;
						$offset_ = $abc_{$firstChar_} . chr($d_);
					}else{
						$flag_     = true;
						$offset_ = $abc_{$firstChar_} . chr($d_);
					}
				}else{
					if($flag_){
						$offset_ = $abc_{$firstChar_} . chr($d_);
					}else{
						$offset_ = chr($d_);
					}
				}
				$offsets_ = $offset_ . $k;
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($offsets_, $rows[$a]);
				$d_ ++;
			}
			$k++;
		}
//		$objActSheet = $objPHPExcel->getActiveSheet();
		//合并单元格
		//$objPHPExcel->getActiveSheet()->mergeCells('A18:E22');
		//设置单元格的宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(23);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(23);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(15);
//		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);

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