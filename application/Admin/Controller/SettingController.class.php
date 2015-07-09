<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SettingController extends AdminbaseController{
	
	
	protected $options_obj;
	
	function _initialize() {
		parent::_initialize();
		$this->options_obj = D("Common/Options");
	}
	
	//清除缓存
	function clearcache(){
			
		sp_clear_cache();
		$this->display();
	}
	
}