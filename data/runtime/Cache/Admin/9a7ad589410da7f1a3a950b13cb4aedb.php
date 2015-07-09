<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>系统后台</title>
        <meta http-equiv="X-UA-Compatible" content="chrome=1,IE=edge" />
        <meta name="renderer" content="webkit|ie-comp|ie-stand">
        <meta name="robots" content="noindex,nofollow">
        <link href="/tpl_admin/simpleboot/assets/css/admin_login.css" rel="stylesheet" />
        <script>
                if (window.parent !== window.self) {
                                document.write = '';
                                window.parent.location.href = window.self.location.href;
                                setTimeout(function () {
                                                document.body.innerHTML = '';
                                }, 0);
                }
        </script>
    </head>
<body>
<div class="wrapper">
    <h1><img src="/tpl_admin/simpleboot/assets/images/login_logo.svg"></h1>
    <h2><em></em>欢迎使用汇盈贷后台管理系统<span></span></h2>
    
    <form method="post" name="login" action="<?php echo U('public/dologin');?>" autoComplete="off" class="J_ajaxForm">
        <ul class="signin">
        <li><span class="icon i_mobile"></span><input id="J_admin_name" required name="username" type="text" class="i_input" placeholder="请输入手机号"></li>
        <li><span class="icon i_pass"></span><input id="admin_pwd" type="password" required name="password" class="i_input" placeholder="密码"></li>
        <li><span class="icon i_code"></span>
            <input type="text" name="verify" class="i_input code" placeholder="校验码">
            <?php echo sp_verifycode_img('length=4&font_size=18&width=110&height=50','style="cursor: pointer;" title="点击获取"');?>
        </li>
        <!-- <li class="check"><input type="checkbox" class="checkbox" ><label>记住密码</label></li> -->

        <li><div id="login_btn_wraper">
                    <button type="submit" name="submit" class="submit J_ajax_submit_btn">登录</button>
            </div></li>
        </ul>
    </form>
    
</div>

<script>
var GV = {
	DIMAUB: "/",
	JS_ROOT: "statics/js/",//js版本号
	TOKEN : ''	//token ajax全局
};

</script>
<script src="/statics/js/wind.js"></script>
<script src="/statics/js/jquery.js"></script>
<script type="text/javascript" src="/statics/js/common.js"></script>
<script>
;(function(){
	document.getElementById('J_admin_name').focus();
	
})();
</script>
</body>
</html>