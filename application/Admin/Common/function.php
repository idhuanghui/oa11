<?php

function sp_get_url_route(){
	$apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
	$host=(is_ssl() ? 'https' : 'http')."://".$_SERVER['HTTP_HOST'];
	$routes=array();
	foreach ($apps as $a){
	
		if(is_dir(SPAPP.$a)){
			if(!(strpos($a, ".") === 0)){
				$navfile=SPAPP.$a."/nav.php";
				$app=$a;
				if(file_exists($navfile)){
					$navgeturls=include $navfile;
					foreach ($navgeturls as $url){
						//echo U("$app/$url");
						$nav= file_get_contents($host.U("$app/$url"));
						$nav=json_decode($nav,true);
						if(!empty($nav) && isset($nav['urlrule'])){
							if(!is_array($nav['urlrule']['param'])){
								$params=$nav['urlrule']['param'];
								$params=explode(",", $params);
							}
							sort($params);
							$param="";
							foreach($params as $p){
								$param.=":$p/";
							}
							
							$routes[strtolower($nav['urlrule']['action'])."/".$param]=$nav['urlrule']['action'];
						}
					}
				}
					
			}
		}
	}
	
	return $routes;
}

/**
 * 员工编号
 * str_pad函数，不足5位从左边补0
 * @param $id
 * @return string
 */
function strPad($id){
    return str_pad($id,5,0,STR_PAD_LEFT);
}

/**
 * 判断用户状态
 * @param $satus 用户状态数组
 */
function userStatus($satus){
    switch($satus){
        case 'E':
			return '<span class="color_green">已入职</span>';
            break;
        case 'E1':
			return '<span class="color_yellow">待一级审批</span>';
            break;
        case 'E11':
			return '<span class="color_red">一审驳回</span>';
            break;
		case 'E2':
			return '<span class="color_yellow">待二级审批</span>';
			break;
        case 'E21':
			return '<span class="color_red">二审驳回</span>';
            break;
		case 'E3':
			return '<span class="color_red">取消入职</span>';
			break;			
        case 'Q':
			return '<span class="color_green">已离职</span>';
            break;
        case 'Q1':
			return '<span class="color_yellow">待一级审批</span>';
            break;
        case 'Q11':
			return '<span class="color_red">一审驳回</span>';
            break;
        case 'Q2':
			return '<span class="color_yellow">待二级审批</span>';
            break;
        case 'Q21':
			return '<span class="color_red">二审驳回</span>';
            break;
        case 'Q3':
            return '<span class="color_red">取消离职</span>';
            break;
        default :
			return '<span class="color_red">员工状态错误</span>';
            break;
    }
}

/**
 * 用户导出Excel表格时需要的用户状态.
 * @param $satus
 * @return string
 */
function userExportStatus($satus){
	switch($satus){
		case 'E':
			return '已入职';
			break;
		case 'E1':
			return '一级未审核';
			break;
		case 'E11':
			return '一级审核未通过';
			break;
		case 'E2':
			return '二级未审核';
			break;
		case 'E21':
			return '二级审核未通过';
			break;
        case 'E3':
            return '取消入职';
            break;
		case 'Q':
			return '已离职';
			break;
		case 'Q1':
			return '离职一级未审核';
			break;
		case 'Q11':
			return '离职一级审核未通过';
			break;
		case 'Q2':
			return '离职二级未审核';
			break;
		case 'Q21':
			return '离职二级审核未通过';
			break;
        case 'Q3':
            return '取消离职';
            break;
		default :
			return '员工状态错误';
			break;
	}
}

/**
 * 标的类型
 * @param $satus
 * @return string
 */
function borrowType($satus){
	switch($satus){
		case '0':
			return '汇保贷';
			break;
		case '1':
			return '汇典贷';
			break;
		case '2':
			return '汇小贷';
			break;
		case '3':
			return '汇车贷';
			break;
		case '4':
			return '新手标';
			break;
		case '5':
			return '汇租赁';
			break;
		default :
			return '标的类型错误';
			break;
	}
}

/**
 * 标的期限
 * @param $satus
 * @return string
 */
function borrowLimit($satus){
	switch($satus){
		case '0.5':
			return '15天';
			break;
		case '1':
			return '1个月';
			break;
		case '2':
			return '2个月';
			break;
		case '3':
			return '3个月';
			break;
		case '4':
			return '4个月';
			break;
		case '6':
			return '6个月';
			break;
                case '12':
			return '12个月';
			break;
		default :
			return '标的期限错误';
			break;
	}
}
/**
 * 标的单位
 * @param $satus
 * @return string
 */
function borrowUnit($satus){
	switch($satus){
		case '1':
			return '天';
			break;
		case '2':
			return '月';
			break;
		case '3':
			return '年';
			break;
		default :
			return '无效单位';
			break;
	}
}
/**
 * 获取部门深度
 * @param $id
 * @param $array
 * @param $i
 */
function _get_level($id, $array = array(), $i = 0) {

    if ($array[$id]['parentid']==0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid']==$id){
        return  $i;
    }else{
        $i++;
        return _get_level($array[$id]['parentid'],$array,$i);
    }

}

/**
 * in_arrar() 多维数组解决办法。
 * @param $x
 * @param $y
 * @return string
 */
function myfunction($x,$y){
    foreach($y As $z){
        if(is_array($z)){
            $var[]=myfunction($x,$z);
        }
        else{
            $var[]=$z;
        }
    }
    return implode($x,$var);
}

/**
 * @param $str    字符串
 * @param $digital		截取起始位置
 * @param $max		截取位数
 * @return string
 */
function _substr($str,$digital,$max){
	return substr($str,$digital,$max);
}

/**
 * 获取当前页面完整的URL (未使用)
 * @return string
 */
function curPageURL(){
	$pageURL = 'http';

	if ($_SERVER["HTTPS"] == "on"){
		$pageURL .= "s";
	}
	$pageURL .= "://";

	if($_SERVER["SERVER_PORT"] != "80"){
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	}else{
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}


//GET请求
if (!function_exists('GET_Api')) {
	function GET_Api($url, $PostData){
		foreach ($PostData as $k => $v) {
			$o .= "$k=" . urlencode($v) . "&";
		}
		$post_data = substr($o, 0, -1);
		return file_get_contents($url . $post_data);
	}
}

//POST请求
if (!function_exists('POST_Api')) {
	function POST_Api($url, $PostData)
	{
		foreach ($PostData as $k => $v) {
			$o .= "$k=" . urlencode($v) . "&";
		}
		$post_data = substr($o, 0, -1);

		//echo $url.$post_data;die();

		//请求参数为get方式
		$curl = curl_init();
		// 设置你需要抓取的URL
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);//在发起连接前等待的时间，如果设置为0，则无限等待。
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

		// 运行cURL，请求网页
		$data = curl_exec($curl);

		// 关闭URL请求
		curl_close($curl);
		return $data;
	}
}

if (!function_exists('decodeUrlArr')) {
	function decodeUrlArr($data)
	{
		return is_null(json_decode($data, true)) ? json_decode(substr($data, 3), true) :
			json_decode($data, true);
	}
}
/*
 * 二维数组去重
 */
if (!function_exists('assoc_unique')) {
    function assoc_unique(&$arr, $key) 
    { 
        $rAr=array(); 
        for($i=0;$i<count($arr);$i++) 
        { 
            if(!isset($rAr[$arr[$i][$key]])) 
            { 
            $rAr[$arr[$i][$key]]=$arr[$i]; 
            } 
        } 
        $arr=array_values($rAr);
        //return $arr;
    } 
    //assoc_unique($arr,'name'); 
}

/*
 * 接口3.0 加解密
 * var  $key='0ee6d504';         //key值
 */
function xcw_encode($text,$key){
    return base64_encode(mcrypt_encrypt(MCRYPT_DES, $key,pkcs5Pad($text, mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC)), MCRYPT_MODE_CBC, $key));
}

function xcw_decrypt($str,$key){
        return pkcs5Unpad(mcrypt_cbc(MCRYPT_DES, $key, base64_decode ($str), MCRYPT_DECRYPT, $key ));
}

function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
}

function pkcs5Unpad($text) {
        $pad = ord($text {strlen($text) - 1});
        if ($pad > strlen($text))
                return false;

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
                return false;

        return substr($text, 0, - 1 * $pad);
}

/**
 * 客户管理接口请求方法,
 * @param $url          接口地址,必填
 * @param $hyd_id       需要查询的汇盈贷ID,必填
 * @param $page_size    分页每页条数
 * @param $page_index   页数
 * @param $name         查询时客户姓名
 * @param $starttime    开始时间点
 * @param $endtime      结束时间点
 * @return mixed|null
 */
function customer_inquiry($url,$hyd_id,$page_size,$page_index,$name,$starttime,$endtime) {
    $hyd_id = trim($hyd_id,',');
    
//    $url = C('INTERFACE_URL_CZ');
    $data = array();
    $data['user_id'] = xcw_encode($hyd_id,'0ee6d504');
    $data['page_size'] = $page_size;
    $data['page_index'] = $page_index;
    if($name){
        $data['name'] = xcw_encode($name,'0ee6d504');
    }
    if($starttime){
        $data['starttime'] = strtotime($starttime);
    }
    if($endtime){
        $data['endtime'] = strtotime($endtime);
    }

    $res = xcw_decrypt(POST_Api($url, $data),'0ee6d504');

    $resArr = unserialize($res);

    if ($resArr['status'] == '0') {
        return $resArr;
    }
    return NULL;
}

function customer_inquirys($url,$hyd_id,$starttime,$endtime) {
    $hyd_id = trim($hyd_id,',');
	
	$data = array();
    //$data['user_id'] = xcw_encode($hyd_id,'0ee6d504');
    $data['user_id'] = $hyd_id;
    //if($starttime){
        $data['starttime'] = $starttime;
        //$data['starttime'] = strtotime($starttime);
    //}
    //if($endtime){
        $data['endtime'] = $endtime;
        //$data['endtime'] = strtotime($endtime);
    //}
		
    //$res = xcw_decrypt(POST_Api($url, $data),'0ee6d504');
    $res = POST_Api($url, $data);
	$resArr = unserialize($res);
	
	if ($resArr['status'] == '0') {
        return $resArr;
    }
    return NULL;
}

