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
<link href="/statics/simpleboot/css/zTreeStyle.css" rel="stylesheet">
<script type="text/javascript" src="/statics/js/zTree/js/jquery.ztree.core-3.5.js"></script>
<script type="text/javascript" src="/statics/js/zTree/js/jquery.ztree.excheck-3.5.js"></script>
<script type="text/javascript" src="/statics/js/My97DatePicker/WdatePicker.js"></script>
<body>
<form class="well form-search" method="post" action="<?php echo U('Achievements/index');?>">
<div class="Search">
    <div class="tit">
        <h3><i class="icon search_tit"></i>快速搜索</h3>
        <ul class="fr">
            <li><input class="chaxun" type="submit" value="查询"></li>
            <li><a id="exploder" href="javascript:void(0)"><i class="fa fa-reply"></i>导出<input id="explode" name="explode" type="hidden"  value="0" ></a></li>
            <li><a href="javascript:void(0)" class="search_show"><i class="fa fa-chevron-down"></i>收起</a></li>
        </ul>
    </div>
    <div class="s_con fl">
        <ul>
            <?php if($departments > 0 OR session('ADMIN_ID') == 1): ?><li><em>分部：</em>
                <div class="btn_sel w180">
                <input id="citySel"  type="text" name="departmentname" value="<?php echo ($formget["departmentname"]); ?>" class="no_border"  readonly onClick="showMenu();" ><i onClick="showMenu();"></i>  
                </div> 
                <input id="departments" name="departmentid"  type="hidden"  value="<?php echo ($formget["departmentid"]); ?>" >
                <div id="menuContent" class="menuContent">
                    <ul id="treeDemo" class="ztree"></ul>
                </div>
            </li>
            
            <li><em>员工：</em>
                    <input type="text" class="s_btn w100" name="user_realname" value="<?php echo ($formget["user_realname"]); ?>" />
                    <input type="hidden" class="s_btn w100" name="user_id" value="<?php echo ($formget["user_id"]); ?>" />
            </li><?php endif; ?>
            <li><em>任务年：</em>
                <select class="w80" name="taskyear">
                    <option value="2015" <?php echo $formget['taskyear']==2015?"selected":"" ?>>2015</option>
                    <option value="2016" <?php echo $formget['taskyear']==2016?"selected":"" ?>>2016</option>
                    <option value="2017" <?php echo $formget['taskyear']==2017?"selected":"" ?>>2017</option>
                    <!--<option value="2018"></option>-->
                    <!--<option value="2019"></option>-->
                    <!--<option value="2020"></option>-->
                </select>
            </li>
            <li><em>任务月：</em>
                <select class="w80" name="taskmonth">
                    <option value="01" <?php echo $formget['taskmonth']==01?"selected":"" ?> >01</option>
                    <option value="02" <?php echo $formget['taskmonth']==02?"selected":"" ?> >02</option>
                    <option value="03" <?php echo $formget['taskmonth']==03?"selected":"" ?> >03</option>
                    <option value="04" <?php echo $formget['taskmonth']==04?"selected":"" ?> >04</option>
                    <option value="05" <?php echo $formget['taskmonth']==05?"selected":"" ?> >05</option>
                    <option value="06" <?php echo $formget['taskmonth']==06?"selected":"" ?> >06</option>
                    <option value="07" <?php echo $formget['taskmonth']==07?"selected":"" ?> >07</option>
                    <option value="08" <?php echo $formget['taskmonth']==08?"selected":"" ?> >08</option>
                    <option value="09" <?php echo $formget['taskmonth']==09?"selected":"" ?> >09</option>
                    <option value="10" <?php echo $formget['taskmonth']==10?"selected":"" ?> >10</option>
                    <option value="11" <?php echo $formget['taskmonth']==11?"selected":"" ?> >11</option>
                    <option value="12" <?php echo $formget['taskmonth']==12?"selected":"" ?> >12</option>
                </select>
            </li>
        </ul>
    </div>
</div>
<div class="Search">
    <div class="tit">
        <h3><i class="icon tab_tit"></i>绩效统计</h3>
        <ul class="fr">
            <li>
                <select name="page_size" id="page_size" class="w50 pag_bar">
                    <option value="10" <?php echo $formget['page_size']==10?"selected":"" ?> >10</option>
                    <option value="20" <?php echo $formget['page_size']==20?"selected":"" ?> >20</option>
                    <option value="50" <?php echo $formget['page_size']==50?"selected":"" ?> >50</option>
                    <option value="100" <?php echo $formget['page_size']==100?"selected":"" ?> >100</option>
                </select>
            </li>
        </ul>
    </div>
    <div class="con_tab fl">
            <table class="tab_list" width="100%" cellspacing="0" cellpadding="0" border="0">
                <thead>
                <tr>
                    <th class="t_center" >任务时间</th>
                    <th class="t_center">区域经理</th>
                    <th class="t_center">城市经理</th>
                    <th class="t_center">督导</th>
                    <th class="t_center">一级分部</th>
                    <th class="t_center">二级分部</th>
                    <th class="t_center">部门名称</th>
                    <th class="t_center" >员工</th>
                    <th class="t_center" >是否兼职</th>
                    <th class="t_center" >销售业绩</th>
                    <th class="t_center" >折比业绩</th>
                    <th class="t_center" >注册并开通汇付天下<br />(开户)人数</th>
                    <th class="t_center" >当月开户并投资<br />(0&lt;投资&lt;100)人数</th>
                    <th class="t_center" >当月开户并投资<br />(100&le;投资&lt;3000)人数</th>
                    <th class="t_center" >当月开户并投资<br />(3000&le;投资)人数</th>
                </tr>
                </thead>
                <tbody>
                <?php if(is_array($achieve)): foreach($achieve as $key=>$vo): ?><tr>
                        <td class="t_center"><?php echo ($vo["month"]); ?></td>
                        <!--<td class="t_center"><?php echo ($vo["months"]); ?></td>-->
                        <td class="t_center"><?php echo ($vo["area_header"]); ?></td>
                        <td class="t_center"><?php echo ($vo["city_header"]); ?></td>
                        <td class="t_center"><?php echo ($vo["manager"]); ?></td>
                        <td class="t_center"><?php echo ($vo["area_department"]); ?></td>
                        <td class="t_center"><?php echo ($vo["city_department"]); ?></td>
                        <td class="t_center"><?php echo ($vo["department_name"]); ?></td>
                        <td class="t_center"><?php echo ($vo["user_realname"]); ?></td>
						<td class="t_center">
							<?php if($vo['temporary'] == 2): ?>全职
								<?php else: ?>
								兼职<?php endif; ?>	
						</td>
						<td class="t_center"><?php echo ($vo["allmoney"]); ?></td>
						<td class="t_center"><?php echo ($vo["ratio"]); ?></td>
                        <td class="t_center"><?php echo ($vo["open_num"]); ?></td>
                        <td class="t_center"><?php echo ($vo["this_num1"]); ?></td>
                        <td class="t_center"><?php echo ($vo["this_num2"]); ?></td>
                        <td class="t_center"><?php echo ($vo["this_num3"]); ?></td>
                    </tr><?php endforeach; endif; ?>
                </tbody>
            </table>
    </div>
    <div class="pagination fl">
        <?php echo ($page); ?>
    </div>
</div>
</form>
</body>
<script src="/statics/js/common.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        //导出
        $('#exploder').click(function(){
            $("#explode").val(1);
            $(".form-search").submit();
            $("#explode").val(0);
        });
        //自动分页
        $('#page_size').change(function(){
            //alert($(this).children('option:selected').val());
            $(".form-search").submit();
        });
    });

    $(".search_show").click(function(){
        $(this).html($(".s_con").is(":hidden") ? "<i class='fa fa-chevron-down'></i>"+"收起" : "<i class='fa fa-chevron-up'></i>"+"展开");
        $(".s_con").slideToggle();
    });
</script>
<SCRIPT type="text/javascript">

    var setting = {
        check: {
            enable: true
        },
        view: {
            dblClickExpand: false
        },
        data: {
            simpleData: {
                enable: true
            }
        },
        callback: {
            beforeClick: beforeClick,
            onCheck: onCheck
        }
    };

    var zNodes = <?php echo ($select_categorys); ?>;

    function beforeClick(treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeDemo");
        zTree.checkNode(treeNode, !treeNode.checked, null, true);
        return false;
    }

    function onCheck(e, treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
                nodes = zTree.getCheckedNodes(true),
                v = "";
        k = "";
        for (var i=0, l=nodes.length; i<l; i++)
        {
            v += nodes[i].name + ",";
        }
        if (v.length > 0 ) v = v.substring(0, v.length-1);
        var cityObj = $("#citySel");
        cityObj.attr("value", v);

        for (var i=0, l=nodes.length; i<l; i++)
        {
            k += nodes[i].id + ",";
        }
        if (k.length > 0 ) k = k.substring(0, k.length-1);
        var cityid = $("#departments");
        cityid.attr("value", k);
    }

    function showMenu() {
        var cityObj = $("#citySel");
        var cityOffset = $("#citySel").offset();
        $("#menuContent").css({left:cityOffset.left + "px", top:cityOffset.top + cityObj.outerHeight() + "px"}).slideDown("fast");

        $("body").bind("mousedown", onBodyDown);
    }
    function hideMenu() {
        $("#menuContent").fadeOut("fast");
        $("body").unbind("mousedown", onBodyDown);
    }
    function onBodyDown(event) {
        if (!(event.target.id == "menuBtn" || event.target.id == "citySel" || event.target.id == "menuContent" || $(event.target).parents("#menuContent").length>0)) {
            hideMenu();
        }
    }


    $(document).ready(function(){
        $.fn.zTree.init($("#treeDemo"), setting, zNodes);
    });
</SCRIPT>
</html>