<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh_CN" style="overflow: hidden;">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<!-- Set render engine for 360 browser -->
<meta name="renderer" content="webkit">
<meta charset="utf-8">
<title>系统中心</title>
<meta name="description" content="This is page-header (.page-header &gt; h1)">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="/statics/simpleboot/css/simplebootadminindex.css" rel="stylesheet">
<link href="/statics/simpleboot/css/global.css" rel="stylesheet">
<link href="/statics/simpleboot/font-awesome/4.2.0/css/font-awesome.min.css"  rel="stylesheet" type="text/css">

<!--[if IE 7]>
<link rel="stylesheet" href="/statics/simpleboot/font-awesome/4.2.0/css/font-awesome-ie7.min.css">
<![endif]-->
<!--[if lte IE 8]>
<link rel="stylesheet" href="/statics/simpleboot/css/simplebootadminindex-ie.css?" />
<![endif]-->
<script>
//全局变量
var GV = {
	HOST:"<?php echo ($_SERVER['HTTP_HOST']); ?>",
    DIMAUB: "/",
    JS_ROOT: "statics/js/",
    TOKEN: ""
};
</script>
<?php $submenus=(array)json_decode($SUBMENU_CONFIG); ?>
<?php function getsubmenu($submenus){ ?>
<?php foreach($submenus as $menu){ ?>
<li>
  <?php if(empty($menu->items)){ ?>
  <a href="javascript:openapp('<?php echo ($menu->url); ?>','<?php echo ($menu->id); ?>','<?php echo ($menu->name); ?>');"><i class="fa fa-<?php echo ((isset($menu->icon) && ($menu->icon !== ""))?($menu->icon):'desktop'); ?>"></i><span class="menu-text"><?php echo ($menu->name); ?></span></a>
  <?php }else{ ?>
  <a href="#" class="dropdown-toggle"><i class="fa color_grey fa-<?php echo ((isset($menu->icon) && ($menu->icon !== ""))?($menu->icon):'desktop'); ?>"></i><span class="menu-text"><?php echo ($menu->name); ?></span><b class="arrow phicons i_adds"></b></a>
  <ul  class="submenu">
    <?php getsubmenu1((array)$menu->items) ?>
  </ul>
  <?php } ?>
</li>
<?php } ?>
<?php } ?>
<?php function getsubmenu1($submenus){ ?>
<?php foreach($submenus as $menu){ ?>
<li>
  <?php if(empty($menu->items)){ ?>
  <a href="javascript:openapp('<?php echo ($menu->url); ?>','<?php echo ($menu->id); ?>','<?php echo ($menu->name); ?>');">
  <!--<i class="fa fa-caret-right"></i>-->
  <span class="menu-text"><?php echo ($menu->name); ?></span></a>
  <?php }else{ ?>
  <a href="#" class="dropdown-toggle">
  <!--<i class="fa fa-caret-right"></i>-->
  <span class="menu-text"><?php echo ($menu->name); ?></span><b class="arrow fa fa-angle-right"></b></a>
  <ul  class="submenu">
    <?php getsubmenu2((array)$menu->items) ?>
  </ul>
  <?php } ?>
</li>
<?php } ?>
<?php } ?>
<?php function getsubmenu2($submenus){ ?>
<?php foreach($submenus as $menu){ ?>
<li><a href="javascript:openapp('<?php echo ($menu->url); ?>','<?php echo ($menu->id); ?>','<?php echo ($menu->name); ?>');">
  <!--&nbsp;<i class="fa fa-angle-double-right"></i>-->
  <span class="menu-text"><?php echo ($menu->name); ?></span></a></li>
<?php } ?>
<?php } ?>
<?php if(APP_DEBUG): ?><style>
#think_page_trace_open {
	left: 0 !important;
	right: initial !important;
}
</style><?php endif; ?>
</head>
<body screen_capture_injected="true">
<div id="loading"><span><img src="/statics/simpleboot/images/loading.gif"></span></div>
<div id="right_tools_wrapper"> 
<!--
<span id="right_tools_clearcache" title="清除缓存" onclick="javascript:openapp('<?php echo u('admin/setting/clearcache');?>','right_tool_clearcache','清除缓存');"><i class="fa fa-trash-o right_tool_icon"></i></span>
-->
<span id="refresh_wrapper" title="刷新当前页" ><i class="phicons i_tool"></i></span>
</div>
<div class="navbar">
  <div class="navbar-inner"> <span class="brand"><img src="/statics/simpleboot/images/logo.svg"><small>后台管理系统</small></span>
    <ul class="pull-right">
      <li> <i class="fa fa-check-circle color_white"></i>
        <?php if(session('ADMIN_ID') == 1): echo ((isset($admin["user_realname"]) && ($admin["user_realname"] !== ""))?($admin["user_realname"]):$admin[user_login]); ?>
          <?php else: ?>
          <a href="javascript:openapp('<?php echo u('user/info',array('id' => session('ADMIN_ID')));?>','index_userinfo','个人资料');"><?php echo ((isset($admin["user_realname"]) && ($admin["user_realname"] !== ""))?($admin["user_realname"]):$admin[user_login]); ?></a><?php endif; ?>
      </li>
      <li><a href="http://www.huiyingdai.com" target="_blank">汇盈贷官网</a></li>
      <li><a href="javascript:void(0)" class="js_password">修改密码</a></li>
      <li><i class="fa fa-sign-out color_white"></i><a href="<?php echo U('Public/logout');?>">退出账号</a></li>
    </ul>
  </div>
</div>
<!--修改密码 start-->
<div class="layer_mask" ></div>
<form class="form-horizontal J_ajaxForm" method="post" action="<?php echo U('User/password_post');?>">  
<div id="layer" class="js_layer_pwd wplf w460">
<h3><a href="javascript:void(0)"><i class="icon i_close"></i></a>修改密码</h3>
<div class="iloan_box">
<table class="iloan">
<tr><th width="100"><i>*</i>旧密码:</th><td><input type="password" class="w260" id="" name="old_password"></td> </tr>
<tr><th><i>*</i>新密码:</th><td><input type="password" class="w260" id="" name="password"></td> </tr>
<tr><th><i>*</i>确认密码:</th><td><input type="password" class="w260" id="" name="repassword"></td> </tr>         
</table>
</div>
<div class="la_foot">
<button type="submit" class="bg_blue J_ajax_submit_btn">提交</button>
<button type="button" id='close_button'  class="bg_red i_close">关闭</button>
</div>
</div>
</form> 
<!--修改密码 end--> 
<div class="main-container">
<div class="sidebar" id="sidebar"> 
<!-- <div class="sidebar-shortcuts" id="sidebar-shortcuts"></div> -->
<div id="nav_wraper">
<ul class="nav nav-list">
<li class="home"><a href="javascript:void(0)"><i class="fa color_black fa-home"></i>管理菜单<b class="arrow phicons i_sho"></b></a></li>
<?php echo getsubmenu($submenus);?>
</ul>
</div>
</div>
<div class="main-content">
<div class="breadcrumbs" id="breadcrumbs"> 
<a class="task-changebt" id="task-pre"><i></i></a>
<div id="task-content">
<ul class="macro-component-tab" id="task-content-inner">
<li class="macro-component-tabitem noclose" app-id="0" app-url="<?php echo u('main/index');?>" app-name="首页"> <span class="macro-tabs-item-text">首页</span> </li>
</ul>
</div>
<div style="clear:both;"></div>
<a id="task-next" class="task-changebt"><i></i></a>
 </div>
<div class="page-content" id="content">
<iframe src="<?php echo U('Main/index');?>" style="width:100%; height: 100%;" frameborder="0" id="appiframe-0" class="appiframe"></iframe>
</div>
</div>
</div>

<script src="/statics/js/jquery-1.8.3.min.js"></script> 
<script>
	var b = $("#sidebar").hasClass("menu-min");
	var a = "ontouchend" in document;
	$(".nav-list").on(
			"click",
			function(g) {
				var f = $(g.target).closest("a");
				if (!f || f.length == 0) {
					return
				}
				if (!f.hasClass("dropdown-toggle")) {
					if (b && "click" == "tap"
							&& f.get(0).parentNode.parentNode == this) {
						var h = f.find(".menu-text").get(0);
						if (g.target != h && !$.contains(h, g.target)) {
							return false
						}
					}
					return
				}
				var d = f.next().get(0);
				if (!$(d).is(":visible")) {
					var c = $(d.parentNode).closest("ul");
					if (b && c.hasClass("nav-list")) {
						return
					}
					c.find("> .open > .submenu").each(
							function() {
								if (this != d
										&& !$(this.parentNode).hasClass(
												"active")) {
									$(this).slideUp(150).parent().removeClass(
											"open")
								}
							})
				} else {
				}
				if (b && $(d.parentNode.parentNode).hasClass("nav-list")) {
					return false;
				}
				$(d).slideToggle(150).parent().toggleClass("open");
				return false;
			});
</script> 
<script src="/tpl_admin/simpleboot/assets/js/index.js"></script>
<script type="text/javascript">
	$('.js_password').click(function(){
		$('.layer_mask').fadeIn();
		$('.js_layer_pwd').fadeIn();
	});
	$('.js_layer_pwd .i_close').click(function(){
		$('.layer_mask').fadeOut();
		$('.js_layer_pwd').fadeOut();
	});
</script>
</body>
</html>