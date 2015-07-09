<?php
namespace Common\Model;
use Think\Model\ViewModel;

/**
 * Excel导出模型
 * Class ExamineModel
 * @package Common\Model
 */
class ExcelViewModel extends ViewModel
{
    /**
     * 虚拟模型,没有对应的数据库
     */
    Protected $autoCheckFields = false;
//
//    //管理员获取员工列表
//    function getList($Model,$where){
//        return $Model->table(C('DB_PREFIX').'users user')
//            ->field('user.*,de.id as did,de.parentid,de.name as dname,de.ishead,de.ishr,de.isfinance,de.header,de.manager')
//            ->join('LEFT join '.C('DB_PREFIX').'department de on de.id=user.departmentid ')
//            //->join('join '.C('DB_PREFIX').'position po on po.id=user.positionid')
//            ->join('join '.C('DB_PREFIX').'user_leave ul on ul.userid=user.id')
//            ->where($where)
//            ->order("create_time DESC")
//            ->select();
//    }

    /**
     * 员工信息导出
     * @param $fileName
     * @param $headArr
     * @param $data
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
	function getExcel($fileName,$headArr,$data){
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
				if(ord($offset_) == 79){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($offsets_, ' '.$rows[$a]);		//防止导出到excel时身份证号码科学计数法(前面加空格,不科学....)
				}else{
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($offsets_, $rows[$a]);
				}
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

    /**
     * 离职员工信息导出
     * @param $fileName
     * @param $headArr
     * @param $data
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
	function getLeavesExcel($fileName,$headArr,$data){
		//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.Writer.Excel5");
		import("Org.Util.PHPExcel.IOFactory.php");

		$date = date("Y_m_d_H_i_s",time());
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
//		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);

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

    /**
     * 客户查询导出
     * @param $fileName
     * @param $headArr
     * @param $data
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
	function getCustomerExcel($fileName,$headArr,$data){
		//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.Writer.Excel5");
		import("Org.Util.PHPExcel.IOFactory.php");

		$date = date("Y_m_d_H_i_s",time());
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

		//设置单元格的宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(15);

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
		//设置活动单指数到第一个表,所以Excel打开这是第一个表
		$objPHPExcel->setActiveSheetIndex(0);
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); //文件通过浏览器下载
		exit;
	}

    /**
     * 客户充值查询导出
     * @param $fileName
     * @param $headArr
     * @param $data
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    function getCustomerRecharge($fileName,$headArr,$data){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Y_m_d_H_i_s",time());
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

        //设置单元格的宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(15);

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
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }

    /**
     * 投资统计导出
     * @param $fileName
     * @param $headArr
     * @param $data
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    function getCountInvestment($fileName,$headArr,$data){
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Y_m_d_H_i_s",time());
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

        //设置单元格的宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);

        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
//                $objActSheet->setCellValue($j.$column, $value);
                if(ord($j) == 76){
                    $objActSheet->setCellValue($j.$column, ' '.$value);
                }else{
                    $objActSheet->setCellValue($j.$column, $value);
                }
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
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

