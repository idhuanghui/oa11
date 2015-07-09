<?php
/**
 * Department(部门管理)
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class DepartmentController extends AdminbaseController {

    protected $Department;
    protected $auth_rule_model;

    function _initialize() {
        parent::_initialize();
        $this->Department = D("Common/Department");
        $this->auth_rule_model = D("Common/AuthRule");
    }

    /**
     *  显示部门
     */
    public function index() {
    	$_SESSION['admin_department_index']="Department/index";
        $departments_model = M();
        $result = $departments_model->table(C('DB_PREFIX')."department d")
            ->field("d.*, u.user_realname as header_name, us.user_realname as manager_name")
            ->join("LEFT join ".C('DB_PREFIX')."users u on d.header = u.id")
            ->join("LEFT join ".C('DB_PREFIX')."users us on d.manager = us.id")
            ->select();
        
        //$result = $this->Department->order(array("listorder" => "ASC"))->select();
        
        import("Tree");
        $tree = new \Tree();
        //$tree->icon = array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        
        $newdepartments=array();
        foreach ($result as $m){
            $newdepartments[$m['id']]=$m; 
        }
        foreach ($result as $n=> $r) {

            $result[$n]['level'] = _get_level($r['id'], $newdepartments);
            $result[$n]['parentid_node'] = ($r['parentid']) ? ' class="child-of-node-' . $r['parentid'] . '"' : '';
            $result[$n]['spacer'] = '<span class="node_'.($result[$n]['level']+1).'"><i></i>';
            if($result[$n]['level'] == 2){ //三级部门不能添加子部门，且可添加岗位。<a href="javascript:openapp('{:u('Department/edit')}','edit_department','部门修改');">  
                
                $result[$n]['str_manage'] = '<a href="' . U("department/edit", array("id" => $r['id'], "menuid" => $_GET['menuid'])) . '">修改</a><em></em><a class="J_ajax_del" href="' . U("Department/delete", array("id" => $r['id'], "menuid" => I("get.menuid")) ). '">删除</a> ';
            }else {
                if($result[$n]['level'] == 1){
                    $result[$n]['header_city'] = $r['header_name'];
                }else if($result[$n]['level'] == 0){
                    $result[$n]['header_area'] = $r['header_name'];
                }else{
                    $result[$n]['manager_name'] = $r['manager_name'];
                }
                $result[$n]['str_manage'] = '<a href='. U("department/add", array("parentid" => $r['id'])) .'>添加子部门</a><em></em><a href="' . U("Department/edit", array("id" => $r['id'], "menuid" => $_GET['menuid'])) . '">修改</a><em></em><a class="J_ajax_del" href="' . U("Department/delete", array("id" => $r['id'], "menuid" => I("get.menuid")) ). '">删除</a> ';
            }
             
            $result[$n]['status'] = $r['status'] ? "显示" : "隐藏";
            $result[$n]['city'] = $r['provinceid'] .' '. $r['cityid'];
        }

        $tree->init($result);
        $str = "<tr id='node-\$id' \$parentid_node>
					<td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='w35 expand'></td>
					<td>\$id</td>
					<td>\$spacer\$name</span></td>
                                    <!-- <td>\$city</td> -->  
                                    <td>\$header_area</td> 
                                    <td>\$header_city</td> 
				    <td>\$manager_name</td>
					<td>\$str_manage</td>
				</tr>";
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        $this->display();
    }

    public function lists(){
    	$_SESSION['admin_department_index']="Department/lists";
    	$result = $this->Department->order(array("app" => "ASC","model" => "ASC","action" => "ASC"))->select();
    	$this->assign("menus",$result);
    	$this->display();
    }

    /**
     *  添加
     */
    public function add() {
    	import("Tree");
    	$tree = new \Tree();
    	$parentid = intval(I("get.parentid"));        
    	$result = $this->Department->order(array("listorder" => "ASC"))->select();
        
        $newdepartments=array();
        foreach ($result as $m){
            $newdepartments[$m['id']]=$m; 
        }
        
    	foreach ($result as $r) {
            if(_get_level($r['id'], $newdepartments) != 2){
                $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
                $array[] = $r;
            }
    	}
    	$str = "<option value='\$id' \$selected>\$spacer \$name</option>";
    	$tree->init($array);
    	$select_categorys = $tree->get_tree(0, $str);
        $this->assign("select_categorys", $select_categorys);
        
        $this->position_category_obj = D("Common/PositionCategory");
        $categorys = $this->position_category_obj->order("listorder asc")->select();
        $this->assign("position_categorys",$categorys);
        
    	$this->display();
    }
    
    /**
     *  添加提交
     */
    public function add_post() {
        if (IS_POST) {
            /*岗位组添加*/
            if($_POST['level']!=2 ){
                $_POST['position_category'] = 0;
            }
            if ($this->Department->create()) {
                    $new_id = $this->Department->add();
                    if ($new_id !== false) {
                        /*人事人员授权，财务人员授权*/
                        if($_POST['level']==2){
                                $this->_set_auth_users($_POST['parentid'],$new_id);
                        }
                        $to=empty($_SESSION['admin_department_index'])?"Department/index":$_SESSION['admin_department_index'];
                        $this->success("添加成功！", U($to));
                    } else {
                        $this->error("添加失败！");
                    }
            } else {
                    $this->error($this->Department->getError());
            }
        }
    }

    /**
     *  删除
     */
    public function delete() {
        $id = intval(I("get.id"));
        $count = $this->Department->where(array("parentid" => $id))->count();
        if ($count > 0) {
            $this->error("该部门下还有子部门，无法删除！");
        }
        //员工，岗位检查
        if ($this->Department->delete($id)!==false) {
            $this->success("删除部门成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     *  编辑
     */
    public function edit() {
        import("Tree");
        $tree = new \Tree();
        $id = intval(I("get.id"));
        
        $departments_model = M();
        $rs = $departments_model->table(C('DB_PREFIX')."department d")
            ->field("d.*, u.user_realname as header_name, us.user_realname as manager_name")
            ->join("LEFT join ".C('DB_PREFIX')."users u on d.header = u.id")
            ->join("LEFT join ".C('DB_PREFIX')."users us on d.manager = us.id")    
            ->where(array("d.id" => $id))
            ->find();
        
        $result = $this->Department->order(array("listorder" => "ASC"))->select();
        
        $newdepartments=array();
        foreach ($result as $m){
            $newdepartments[$m['id']]=$m; 
        }
        
    	foreach ($result as $r) {
            if(_get_level($r['id'], $newdepartments) != 2){
                $r['selected'] = $r['id'] == $rs['parentid'] ? 'selected' : '';
                $array[] = $r;
            }
    	}
        
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $select_categorys = $tree->get_tree(0, $str);
        $this->assign("data", $rs);
        $this->assign("select_categorys", $select_categorys);
        
        $this->position_category_obj = D("Common/PositionCategory");
        $categorys = $this->position_category_obj->order("listorder asc")->select();
        $this->assign("position_categorys",$categorys);
        $this->display();
    }
    
    /**
     *  编辑提交
     */
    public function edit_post() {
    	if (IS_POST) {
            /*负责人督导判断*/
            if($_POST['level'] == 2 ){
                $_POST['header'] = NULL;
            }else{
                $_POST['manager'] = NULL;
            }
            if ($this->Department->create()) {
                if ($this->Department->save() !== false) {
                        $to=empty($_SESSION['admin_department_index'])?"Department/index":$_SESSION['admin_department_index'];
                        $this->success("修改成功！", U($to));
                } else {
                        $this->error("更新失败！");
                }
            } else {
                $this->error($this->Department->getError());
            }
    	}
    }
  
    //排序
    public function listorders() {
        $status = parent::_listorders($this->Department);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }
    
    //导出
    public function export(){
        $departments_model = M();
        $users_model = M('users');
        $result = $departments_model->table(C('DB_PREFIX')."department d")
            ->field("d.*, u.user_realname as header_name")
            ->join("LEFT join ".C('DB_PREFIX')."users u on d.header = u.id")
            ->order("d.parentid ASC, d.listorder ASC")    
            ->select();
        
        $departments=array();$newdepartments=array();
        foreach ($result as $m){
            $departments[$m['id']]=$m; 
        }
        
    	foreach ($result as $r) {
            if(_get_level($r['id'], $departments) == 2){
                $city_data = $departments_model->table(C('DB_PREFIX')."department d")
                        ->field("d.*, u.user_realname as header_name")
                        ->join("LEFT join ".C('DB_PREFIX')."users u on d.header = u.id")
                        ->where("d.id= ".$r['parentid'])
                        ->find();
                $r['city_header'] = $city_data['header_name'];
                $r['city_department'] = $city_data['name'];
                
                $area_data = $departments_model->table(C('DB_PREFIX')."department d")
                        ->field("d.*, u.user_realname as header_name")
                        ->join("LEFT join ".C('DB_PREFIX')."users u on d.header = u.id")
                        ->where("d.id= ".$city_data['parentid'])
                        ->find();
                $r['area_header'] = $area_data['header_name'];
                $r['area_department'] = $area_data['name'];
                
                //部门负责人department_heaer
                //部门员工人数member_count
               
                $user_data = $users_model
                        ->field("user_realname")
                        ->where(array("level"=>1,"departmentid"=>$r['id']))
                        ->find();
                $r['department_heaer'] = $user_data['user_realname'];
                
                $r['member_count'] = $users_model
                        ->where("departmentid= ".$r['id'])
                        ->count();
                
                $newdepartments[] = $r;
            }
    	}  
        
        $this->Department->departments_export($newdepartments);
    }
    public function users_search(){
        if($_POST['name']){
            $user_realname = $_POST['name'];
            $users_model = M();
            $data = array();
            
            $data = $users_model->table(C('DB_PREFIX')."users u")
                        ->field("u.id, u.user_realname, u.user_login, d.name as department_name")
                        ->join("LEFT join ".C('DB_PREFIX')."department d on u.departmentid = d.id")
                        ->where("u.user_realname like '%$user_realname%' ")
                        ->select();
  
            if(count($data)){
                echo json_encode($data); 
            }else{
                
            }
        }
    }
    
    /*
     * 获取人事用户
     * $department_id 二级分部
     */
    private function _set_auth_users($department_id,$newid){
            if($department_id>0){
                    $departments_hr = $this->Department->where(array("parentid"=>$department_id,"ishr"=>'1'))->getField("id,name",true);   
                    $auth_user_model = D("Common/AuthUser");
                    if(is_array($departments_hr)){
                            $users = D("Common/Users");
                            foreach ($departments_hr as $key => $value) { 
                                    $user_arr = $users->where(array("departmentid"=>$key))->getField("id,user_realname",true);
                                    foreach ($user_arr as $k => $v) {
                                            //$hrusers[] = array("department_id"=>$newid,"user_id"=>$v['id']);
                                            $auth_user_model->add(array("user_id"=>$k,
                                                "department_id"=>$newid,
                                                "is_list" =>1,
                                                "is_add" =>1,
                                                "is_entry1"=>0,
                                                "is_entry2"=>0,
                                                "is_quit"=>1,
                                                "is_quit1"=>0,
                                                "is_quit2"=>0));
                                    }
                            }
                    }
                    //更新或插入一二级入职离职审核(5678)
                    $departments_count = $this->Department->where(array("parentid"=>$department_id))->count()-1;//同级部门个数
                    $role_users = M("RoleUser");
                    foreach(array('5'=>'is_entry1','6'=>'is_entry2','7'=>'is_quit1','8'=>'is_quit2') as $key=>$value){
                            $user_arr = array();
                            $user_arr = $role_users->where(array("role_id"=>$key))->getField("user_id,role_id",true);
                            foreach ($user_arr as $k => $v) {
                                    $count = 0;//已经符合权限的同级部门个数。
                                    $departments_m = M();
                                    $result_count = $departments_m->query("SELECT count(*) as dcount FROM  ".C('DB_PREFIX')."auth_user
                                        WHERE user_id =".$k."
                                        AND ".$value." =1
                                        AND department_id
                                        IN (
                                        SELECT id
                                        FROM ".C('DB_PREFIX'). "department 
                                        WHERE parentid =".$department_id."
                                        )");
                                    
                                    $count=$result_count[0]['dcount'];
                                    if($count!=0&&$departments_count == $count){
                                        $auth_user = array();
                                        $auth_user = $auth_user_model->where(array("department_id"=>$newid,"user_id"=>$k))->getField("department_id,user_id",true);
                                        if(count($auth_user)>0){
                                                $auth_user_model->where(array("department_id"=>$newid,"user_id"=>$k))->save(array($value=>1));
                                        }else{
                                                $auth_user_model->add(array("user_id"=>$k,
                                                    "department_id"=>$newid,
                                                    $value => 1));
                                        }
                                    }
                            }
                    }      
            }
    }
  
    
}

