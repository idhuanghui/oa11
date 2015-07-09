$task_content_inner = null;
$mainiframe = null;
var tabwidth=98;
$loading = null;
$(function () {
    $mainiframe = $("#mainiframe", window.parent.document);
    $content=$("#content", window.parent.document);
    $loading=$("#loading", window.parent.document);
    
    var headerheight=86;
    $content.height($(window.parent.document).height()-headerheight);
    $(window.parent.document).resize(function(){
        $content.height($(window.parent.document).height()-headerheight);
        calcTaskitemsWidth();
    });
    
    $task_content_inner = $("#task-content-inner", window.parent.document);
});
function calcTaskitemsWidth() {
    var width = $("#task-content-inner li", window.parent.document).length * tabwidth;
    $("#task-content-inner", window.parent.document).width(width);
    if (($(window.parent.document).width()-268-tabwidth- 30 * 2) < width) {
        $("#task-content", window.parent.document).width($(window.parent.document).width() -268-tabwidth- 30 * 2);
        $("#task-next,#task-pre", window.parent.document).show();
    } else {
        $("#task-next,#task-pre", window.parent.document).hide();
        $("#task-content", window.parent.document).width(width);
    }
}
function close_current_app(){
	closeapp($("#task-content-inner .current", window.parent.document));
}
function closeapp($this){
    if(!$this.is(".noclose")){
            $this.prev().click();
    $this.remove();
    calcTaskitemsWidth();
    $("#task-next").click();
    } 
}

var task_item_tpl ='<li class="macro-component-tabitem">'+
'<span class="macro-tabs-item-text"></span>'+
'<a class="macro-component-tabclose" href="javascript:void(0)" title="点击关闭标签"><span></span><b class="macro-component-tabclose-icon">×</b></a>'+
'</li>';

var appiframe_tpl='<iframe style="width:100%;height: 100%;" frameborder="0" class="appiframe"></iframe>';

function openapp_inner(url, appid, appname, selectObj) {
    var $app = $("#task-content-inner li[app-id='"+appid+"']", window.parent.document);
    $("#task-content-inner .current", window.parent.document).removeClass("current");
    if ($app.length == 0) {
        var task = $(task_item_tpl).attr("app-id", appid).attr("app-url",url).attr("app-name",appname).addClass("current");
        task.find(".macro-tabs-item-text").html(appname);
        $task_content_inner.append(task);
        $(".appiframe", window.parent.document).hide(); //alert('111');
        $loading.show();
        $appiframe=$(appiframe_tpl).attr("src",url).attr("id","appiframe-"+appid);
        $("#content", window.parent.document).append($appiframe);
        $appiframe.load(function(){
            $loading.hide();
        });
        calcTaskitemsWidth();
    } else { 
    	$app.addClass("current");
    	$(".appiframe", window.parent.document).hide();
    	var $iframe=$("#appiframe-"+appid, window.parent.document);
    	var src=$iframe.get(0).contentWindow.location.href;
    	src=src.substr(src.indexOf("://")+3);
    	/*if(src!=GV.HOST+url){
    		$loading.show();
    		$iframe.attr("src",url);
    		$appiframe.load(function(){
            	$loading.hide();
            });
    	}*/
    	$iframe.show();
    	$mainiframe.attr("src",url);
    }
    
    //
    var itemoffset= $("#task-content-inner li[app-id='"+appid+"']", window.parent.document).index()* tabwidth;
    var width = $("#task-content-inner li", window.parent.document).length * tabwidth;
   
    var content_width = $("#task-content", window.parent.document).width();
    var offset=itemoffset+tabwidth-content_width;
    
    var lesswidth = content_width - width;
    
    var marginleft = $task_content_inner.css("margin-left");
   
    marginleft =parseInt( marginleft.replace("px", "") );
    var copymarginleft=marginleft;
    if(offset>0){
    	marginleft=marginleft>-offset?-offset:marginleft;
    }else{
    	marginleft=itemoffset+marginleft>=0?marginleft:-itemoffset;
    }
    
    if(-itemoffset==marginleft){
    	marginleft = marginleft + tabwidth > 0 ? 0 : marginleft + tabwidth;
    }
    
    //alert("cddd:"+(content_width-copymarginleft)+" dddd:"+(-itemoffset));
    if(content_width-copymarginleft-tabwidth==itemoffset){
    	marginleft = marginleft - tabwidth <= lesswidth ? lesswidth : marginleft - tabwidth;
    }
    
    $task_content_inner.animate({ "margin-left": marginleft + "px" }, 300, 'swing');

}

