<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- HTML5 shim for IE8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->
    
    <link href="/statics/simpleboot/css/global.css" rel="stylesheet">
    
    <!-- <link href="/statics/simpleboot/themes/<?php echo C('SP_ADMIN_STYLE');?>/theme.min.css" rel="stylesheet"> -->

    
    <link href="/statics/simpleboot/css/simplebootadmin.css" rel="stylesheet">
    <link href="/statics/js/artDialog/skins/default.css" rel="stylesheet" />
    <link href="/statics/simpleboot/font-awesome/4.2.0/css/font-awesome.min.css"  rel="stylesheet" type="text/css">
    <style>
            .length_3{width: 180px;}
    </style>
    
    <!--[if IE 7]>
    <link rel="stylesheet" href="/statics/simpleboot/font-awesome/4.2.0/css/font-awesome-ie7.min.css">
    <![endif]-->
    
<script type="text/javascript">
//全局变量
var GV = {
    DIMAUB: "/",
    JS_ROOT: "statics/js/",
    TOKEN: ""
};
</script>

<!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/statics/js/jquery.js"></script>
    <script src="/statics/js/wind.js"></script>
    <script src="/statics/simpleboot/bootstrap/js/bootstrap.min.js"></script>
    
<?php if(APP_DEBUG): ?><style>
        #think_page_trace_open{
                z-index:9999;
        }
    </style><?php endif; ?>
<style>
h4.well{ margin:12px 0 8px 0; font-size:16px; font-weight:100; color: #6099c4;}
.home_info li{ line-height:29px; font-size:13px;}
.home_info li label { float: left; width: 100px;}
</style>
</head>
<body>
<div class="wrap">
<h4 class="well">系统信息</h4> 
<div class="home_info">
<ul>
<?php if(is_array($server_info)): $i = 0; $__LIST__ = $server_info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><label><?php echo ($key); ?></label><?php echo ($vo); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
</ul>
</div>
<h4 class="well">发起团队</h4>
<div class="home_info" id="home_devteam">
<ul>
<li><label>OA团队</label>www.huiyingdai.com</li>
<li><label>团队成员</label>李磊，李杨，张成浩，黄辉</li>
<li><label>联系邮箱</label>oa@huiyingdai.com</li>
</ul>
</div>
</div>

<script src="/statics/js/common.js"></script> 
<script>
</script>
</body>
</html>