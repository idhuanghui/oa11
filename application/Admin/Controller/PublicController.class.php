<?php

/**
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PublicController extends AdminbaseController {

    function _initialize() {}
    
    //后台登陆界面
    public function login() {
    	if(isset($_SESSION['ADMIN_ID'])){//已经登录
    		$this->success(L('LOGIN_SUCCESS'),U("Index/index"));
    	}else{
    		if(empty($_SESSION['adminlogin'])){
    			redirect(__ROOT__."/");
    		}else{
    			$this->display(":login");
    		}
    		
    	}
    }
    
    public function logout(){
    	session('ADMIN_ID',null); 
    	$this->redirect("public/login");
    }
    
    public function dologin(){
    	$name = I("post.username");
    	if(empty($name)){
    		$this->error(L('USERNAME_OR_EMAIL_EMPTY'));
    	}
    	$pass = I("post.password");
    	if(empty($pass)){
    		$this->error(L('PASSWORD_REQUIRED'));
    	}
    	$verify = I("post.verify");
//    	if(empty($verify)){
//    		$this->error(L('CAPTCHA_REQUIRED'));
//    	}
    	//验证码
    	if(0&&$_SESSION['_verify_']['verify']!=strtolower($verify))
    	{
    		$this->error(L('CAPTCHA_NOT_RIGHT'));
    	}else{
    		$user = D("Common/Users");
    		if(strpos($name,"@")>0){//邮箱登陆
    			$where['user_email']=$name;
    		}else{
    			$where['user_login']=$name;
				$where['user_status'] = array(array('NEQ','E11'),array('NEQ','E21'),array('NEQ','E3'));
    		}
    		
    		$result = $user->where($where)->find();
            switch($result['user_status']){
                case 'E1':
                case 'E11':
                case 'E2':
                case 'E21':
                case 'E3':
                    $this->error(L('EXAMINE_NOT'));
                    break;
                case 'Q':
                case 'Q1':
//                case 'Q11':
                case 'Q2':
//                case 'Q21':
                    $this->error(L('TO_QUIT'));
                    break;
            }
    		if($result != null && $result['user_type']==1 || $result['user_type'] == 2){
    			if($result['user_pass'] == sp_password($pass)){
                    $role = D("Common/Role_user");
                    //$ro_resule = $role->where(array('user_id'=>$result["id"]))->find();
//                    $ro_resule = $role->where(array('user_id'=>$result["id"]))->order("role_id ASC")->select();
    				//登入成功页面跳转
    				$_SESSION["ADMIN_ID"]		=$result["id"];
    				$_SESSION['name']			=$result["user_login"];
                    $_SESSION['level'] 			= $result['level'];
                    $_SESSION['departmentid']   = $result['departmentid'];
                    $_SESSION['hyd_id']   		= $result['hyd_id'];
//                    $_SESSION['roid']    		= $ro_resule['role_id'];    //写role_id到session判断是否是超级管理员
    				//session("roleid",$result['role_id']);
    				$result['last_login_ip']=get_client_ip();
    				$result['last_login_time']=time();
    				$user->save($result);
    				setcookie("admin_username",$name,time()+30*24*3600,"/");
    				$this->success(L('LOGIN_SUCCESS'),U("Index/index"));
    			}else{
    				$this->error(L('PASSWORD_NOT_RIGHT'));
    			}
    		}else{
    			$this->error(L('USERNAME_NOT_EXIST'));
    		}
    	}
    }

}

