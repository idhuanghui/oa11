<?php

/* * 
 * 系统权限配置，用户组管理
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RbacController extends AdminbaseController {

    protected $User, $Role, $Role_user,$auth_access_model,$auth_user_model,$department;

    function _initialize() {
        parent::_initialize();
        $this->Role = D("Common/Role");
        $this->role_obj = D("Common/Role");
    }
    
    /**
     * 用户组管理，有add添加，edit编辑，delete删除
     */
    public function index() {
        $data = $this->Role->order(array("listorder" => "asc", "id" => "desc"))->select();
        $this->assign("roles", $data);
        $this->display();
    }

    /**
     * 添加用户组
     */
    public function roleadd() {
        
        $this->display();
    }
    
    /**
     * 添加用户组
     */
    public function roleadd_post() {
    	if (IS_POST) {
    		if ($this->Role->create()) {
    			if ($this->Role->add()!==false) {
    				$this->success("添加用户组成功",U("rbac/index"));
    			} else {
    				$this->error("添加失败！");
    			}
    		} else {
    			$this->error($this->Role->getError());
    		}
    	}
    }

    /**
     * 删除用户组
     */
    public function roledelete() {
    	$users_obj = D("Common/Users");
        $id = intval(I("get.id"));
        if ($id == 1) {
            $this->error("超级管理员用户组不能被删除！");
        }
        $count=$users_obj->where("id=$id")->count();
        if($count){
        	$this->error("该用户组已经有用户！");
        }else{
        	$status = $this->Role->delete($id);
        	if ($status!==false) {
        		$this->success("删除成功！", U('Rbac/index'));
        	} else {
        		$this->error("删除失败！");
        	}
        }
        
    }

    /**
     * 编辑用户组
     */
    public function roleedit() {        
        $id = intval(I("get.id"));
        if ($id == 0) {
            $id = intval(I("post.id"));
        }
        if ($id == 1) {
            $this->error("超级管理员用户组不能被修改！");
        }
        $data = $this->Role->where(array("id" => $id))->find();
        if (!$data) {
        	$this->error("该用户组不存在！");
        }
        $this->assign("data", $data);
        $this->display();
    }
    
    /**
     * 编辑用户组
     */
    public function roleedit_post() {
    	$id = intval(I("get.id"));
    	if ($id == 0) {
    		$id = intval(I("post.id"));
    	}
    	if ($id == 1) {
    		$this->error("超级管理员用户组不能被修改！");
    	}
    	if (IS_POST) {
    		$data = $this->Role->create();
    		if ($data) {
    			if ($this->Role->save($data)!==false) {
    				$this->success("修改成功！");
    			} else {
    				$this->error("修改失败！");
    			}
    		} else {
    			$this->error($this->Role->getError());
    		}
    	}
    }
    //重置状态
    function status_reset(){
        if($_POST){
            $data = array();
            if($_POST['status']){
                $data['status'] = '0';
            }else{
                $data['status'] = '1';
            }
            $res = $this->Role->where(array('id'=>trim($_POST['id'])))->data($data)->save();
            if($res){
                echo '状态重置成功!';
            }else{
                echo '状态重置失败!';
            }
        }
    }
    /**
     * 用户组授权
     */
    public function authorize() {
        $this->auth_access_model = D("Common/AuthAccess");
       //用户组ID
        $roleid = intval(I("get.id"));
        if (!$roleid) {
        	$this->error("参数错误！");
        }
        import("Tree");
        $menu = new \Tree();
        //$menu->icon = array('│ ', '├─ ', '└─ ');
        $menu->nbsp = '&nbsp;&nbsp;&nbsp;';
        $result = $this->initMenu();
        $newmenus=array();
        $priv_data=$this->auth_access_model->where(array("role_id"=>$roleid))->getField("rule_name",true);//获取权限表数据
        foreach ($result as $m){
        	$newmenus[$m['id']]=$m;
        }
        
        foreach ($result as $n => $t) {
        	$result[$n]['checked'] = ($this->_is_checked($t, $roleid, $priv_data)) ? ' checked' : '';
        	$result[$n]['level'] = $this->_get_level($t['id'], $newmenus);
                $result[$n]['spacer'] = '<span class="node_'.($result[$n]['level']+1).'"><i></i>';
        	$result[$n]['parentid_node'] = ($t['parentid']) ? ' class="child-of-node-' . $t['parentid'] . '"' : '';
        }
        $str = "<tr id='node-\$id' \$parentid_node>
                       <td style='padding-left:30px;'>\$spacer<input type='checkbox' name='menuid[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$name</td>
	    			</tr>";
        $menu->init($result);
        $categorys = $menu->get_tree(0, $str);
        
        $this->assign("categorys", $categorys);
        $this->assign("roleid", $roleid);
        $this->display();
    }
    
    /**
     * 用户组授权
     */
    public function authorize_post() {
    	
    	if (IS_POST) {
    		$roleid = intval(I("post.roleid"));
    		if(!$roleid){
    			$this->error("需要授权的用户组不存在！");
    		}
                $type = intval(I("post.type"));
                //$this->error($type);
    		if (is_array($_POST['menuid']) && count($_POST['menuid'])>0) {
    			$this->auth_access_model = D("Common/AuthAccess");
    			$menu_model=M("Menu");
    			$auth_rule_model=M("AuthRule");
    			$this->auth_access_model->where(array("role_id"=>$roleid,'type'=>'admin_url'))->delete();
    			foreach ($_POST['menuid'] as $menuid) {
    				$menu=$menu_model->where(array("id"=>$menuid))->field("app,model,action")->find();
    				if($menu){
    					$app=$menu['app'];
    					$model=$menu['model'];
    					$action=$menu['action'];
    					$name=strtolower("$app/$model/$action");
    					$this->auth_access_model->add(array("role_id"=>$roleid,"rule_name"=>$name,'type'=>'admin_url'));
    				}
    			}
                        
    			$this->success("授权成功！");
    		}else{
    			//当没有数据时，清除当前用户组授权
    			$this->auth_access_model->where(array("role_id" => $roleid))->delete();
    			$this->error("没有接收到数据，执行清除授权成功！");
    		}
    	}
    }
    
    /**
     * 用户授权
     */
    public function authuser() {
        $this->department = D("Common/Department");
        $this->auth_user_model = D("Common/AuthUser");

       //用户组ID
        $userid = intval(I("get.id"));
        if (!$userid) {
        	$this->error("参数错误！");
        }
        import("Tree");
        $menu = new \Tree();
        $menu->icon = array('│ ', '├─ ', '└─ ');
        $menu->nbsp = '&nbsp;&nbsp;&nbsp;';
        $result = $this->department->order(array("listorder" => "ASC"))->select();
        $newmenus=array();

        $priv_data = $this->auth_user_model->where(array("user_id"=>$userid))->getField("department_id,is_list,is_add,is_entry1,is_entry2,is_quit,is_quit1,is_quit2",true);//获取用户部门权限表数据
        foreach ($result as $m){
            $newmenus[$m['id']]=$m;	
        }
        $role_data = array('is_list','is_add','is_entry1','is_entry2','is_quit','is_quit1','is_quit2');
        foreach ($result as $n => $t) {
            $result[$n]['level'] = $this->_get_level($t['id'], $newmenus);
            $result[$n]['parentid_node'] = ($t['parentid']) ? ' class="child-of-node-' . $t['parentid'] . '"' : '';
            if($result[$n]['level'] == 2){
                if(array_key_exists($t['id'], $priv_data)){ 
                    foreach ($role_data as $r){ //dump($n);
                        if($priv_data[$t['id']][$r] == 1){
                            $result[$n][$r][$t['id']] = ' checked';
                        }else{
                            $result[$n][$r][$t['id']] = ' ';
                        }
                    }
                }
            }
        }
        $str = "<tr id='node-\$id' \$parentid_node>

                <td class='p_20'>\$spacer\$name</td>
                <td class='t_center'><input name='role_content[\$id][]' type='checkbox' \$is_list[\$id] value='list' class='role_content' level='\$level' onclick='javascript:checknode(this);'></td>
                <td class='t_center'><input name='role_content[\$id][]' type='checkbox' \$is_add[\$id] value='add' class='role_content' level='\$level' onclick='javascript:checknode(this);'></td>
                <td class='t_center'><input name='role_content[\$id][]' type='checkbox' \$is_entry1[\$id] value='entry1' class='role_content' level='\$level' onclick='javascript:checknode(this);'></td>
                <td class='t_center'><input name='role_content[\$id][]' type='checkbox' \$is_entry2[\$id] value='entry2' class='role_content' level='\$level' onclick='javascript:checknode(this);'></td>
                <td class='t_center'><input name='role_content[\$id][]' type='checkbox' \$is_quit[\$id] value='quit' class='role_content' level='\$level' onclick='javascript:checknode(this);'></td>
                <td class='t_center'><input name='role_content[\$id][]' type='checkbox' \$is_quit1[\$id] value='quit1' class='role_content' level='\$level' onclick='javascript:checknode(this);'></td>
                <td class='t_center'><input name='role_content[\$id][]' type='checkbox' \$is_quit2[\$id] value='quit2' class='role_content' level='\$level' onclick='javascript:checknode(this);'></td>

            </td>

        </tr>";
        
        $menu->init($result);
        $categorys = $menu->get_tree(0, $str);

        //角色选择
        $roles=$this->role_obj->where("status=1 AND id >1")->order("id ASC")->select();
        $this->assign("roles",$roles);
        $role_user_model=M("RoleUser");
        $role_ids=$role_user_model->where(array("user_id"=>$userid))->getField("role_id",true);
        $this->assign("role_ids",$role_ids);

        $this->assign("categorys", $categorys);
        $this->assign("userid", $userid);
        $this->display();
    }
    
    /**
     * 用户授权提交
     */
    public function authuser_post() {

    	if (IS_POST) {
    		$userid = intval(I("post.userid"));
            $role_ids=$_POST['role_id'];
            unset($_POST['role_id']);
    		if(!$userid){
                    $this->error("需要授权的用户不存在！");
    		}
            $role_user_model=M("RoleUser");
            $role_user_model->where(array("user_id"=>$userid))->delete();       //数据部存在时清除
            foreach ($role_ids as $role_id){
                $role_user_model->add(array("role_id"=>$role_id,"user_id"=>$userid));
            }
            $this->auth_user_model = D("Common/AuthUser");
    		if (is_array($_POST['role_content']) && count($_POST['role_content'])>0) {
                            
                        $this->auth_user_model->where(array("user_id"=>$userid))->delete();

                        $this->Department = D("Common/Department");
                        $result = $this->Department->order(array("listorder" => "ASC"))->select();

                        $newdepartments=array();
                        foreach ($result as $m){
                            $newdepartments[$m['id']]=$m;
                        }
                        foreach ($result as $n=> $r) {
                            $result[$n]['level'] = $this->_get_level($r['id'], $newdepartments);
                            if($result[$n]['level']==2){ //仅存储团队
                                if(count($_POST['role_content'][$r['id']])>0){ 
                                    foreach ($_POST['role_content'][$r['id']] as $c){ //dump($c);
                                        $$c = 1;
                                    }
                                    $this->auth_user_model->add(array("user_id"=>$userid,
                                        'department_id'=>$r['id'],
                                        "is_list" =>$list,
                                        "is_add" =>$add,
                                        "is_entry1"=>$entry1,
                                        "is_entry2"=>$entry2,
                                        "is_quit"=>$quit,
                                        "is_quit1"=>$quit1,
                                        "is_quit2"=>$quit2));
                                    
                                    foreach ($_POST['role_content'][$r['id']] as $c){//初始化
                                        $$c = 0;
                                    }

                                }
                            }
                        }
                        
    			$this->success("授权成功！");
    		}else{
    			//当没有数据时，清除当前用户组授权
    			$this->auth_user_model->where(array("user_id" => $userid))->delete();
    			$this->success("授权成功！");
    		}
    	}
    }
    /**
     *  检查指定菜单是否有权限
     * @param array $menu menu表中数组
     * @param int $roleid 需要检查的用户组ID
     */
    private function _is_checked($menu, $roleid, $priv_data) {
    	
    	$app=$menu['app'];
    	$model=$menu['model'];
    	$action=$menu['action'];
    	$name=strtolower("$app/$model/$action");
    	if($priv_data){
	    	if (in_array($name, $priv_data)) {
	    		return true;
	    	} else {
	    		return false;
	    	}
    	}else{
    		return false;
    	}
    	
    }

    /**
     * 获取菜单深度
     * @param $id
     * @param $array
     * @param $i
     */
    protected function _get_level($id, $array = array(), $i = 0) {
        
        	if ($array[$id]['parentid']==0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid']==$id){
        		return  $i;
        	}else{
        		$i++;
        		return $this->_get_level($array[$id]['parentid'],$array,$i);
        	}
        		
    }

}

