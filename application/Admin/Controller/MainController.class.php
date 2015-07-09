<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class MainController extends AdminbaseController {

	protected function _model(){
		return new \Think\Model();
	}

	function _initialize() {
	}
	public function index(){
		
		$mysql = $this->_mysql_version();
		//服务器信息
		$info = array(
						'操作系统' => PHP_OS,
						'运行环境' => $_SERVER["SERVER_SOFTWARE"],
						'PHP运行方式' => php_sapi_name(),
						'MYSQL版本' =>$mysql,
						'程序版本' => SIMPLEWIND_CMF_VERSION . "&nbsp;&nbsp;&nbsp; [<a href='http://www.huiyingdai.com' target='_blank'>访问官网</a>]",
						'上传附件限制' => ini_get('upload_max_filesize'),
						'执行时间限制' => ini_get('max_execution_time') . "秒",
						'剩余空间' => round((@disk_free_space(".") / (1024 * 1024)), 2) . 'M',
		);
		$this->assign('server_info', $info);
		$this->display();
	}

	/**
	 * 获取Mysql版本信息
	 * @return mixed
	 */
	private function _mysql_version()
	{
		$Model = self::_model();
		$version = $Model->query("select version() as ver");
		return $version[0]['ver'];
	}
}