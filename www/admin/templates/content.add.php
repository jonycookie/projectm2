<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
iCMS_admincp_head();
?>
<div id="append_parent"></div>
<div id="default">
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" class="close"><img src="admin/images/close.gif" /></span><span id="dtitle">预设</span></th>
    </tr>
    <tr>
      <td class="tipsblock" style="padding-left:5px;" id="defaultbody">
	 </td>
    </tr>
  </table>
</div>
<link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;<?=$model->name?>管理&nbsp;&raquo;&nbsp;<?=empty($id)?'添加':'编辑'?><?=$model->name?>','');</script>
<link rel="stylesheet" href="<?=$iCMS->dir?>admin/images/jquery.function.css" type="text/css" media="all" />
<script type="text/javascript" src="<?=$iCMS->dir?>javascript/jquery.function.js"></script>
<script type="text/javascript" src="<?=$iCMS->dir?>javascript/calendar.js"></script>
<script language="JavaScript" type="text/javascript">
$(function(){
	$("#title").focus();
	$("#savecontent").submit(function(){
		if($("#cid option:selected").attr("value")=="0"){
			alert("请选择所属栏目");
			$("#cid").focus();
			return false;
		}
		if($("#title").val()==''){
			alert("标题不能为空!");
			$("#title").focus();
			return false;
		}
		<?php for($i=0;$i<$fcount;$i++){if($form[$i]['js']){?><?=$form[$i]['js']?><?php }}?>
	}); 

	$(".selectdefault").click(function(){
		var offset 		= $(this).offset();
		var snapTop 	= offset.top+25;
		var snapLeft 	= offset.left;
//		alert(snapTop+"-"+snapLeft);
		var def			= $("#default");
		var inid		=$(this).attr("to");
		if(inid=="pic"){
			$("#dtitle").html("选择");
			$("#defaultbody").empty().html($("#picmenu").html());
		}else{
			$.post("<?=__SELF__?>?do=ajax",{'action':inid},
			  function(data){
				$("#defaultbody").empty().html(data);
			  }
			); 
		}
		def.hide().addClass("selectdefaultdiv")
		.css({"top" : snapTop, "left" : snapLeft,"width":"120"})
		.slideDown("slow");
	});
	$(".close").click(function(){
	    $("#default").slideUp("slow");
	});
//	$("#pic").snap('value');
	$("#pic").dblclick( function () { showPic('pic'); }); 
});
function indefault(v,id){
	var val	=$("#"+id).val();
	if(val==""){
		val=v;
	}else{
		val+=" "+v;
	}
	$("#"+id).val(val);
}
function showPic(id){
	var picurl	=$("#"+id).val();
	if(picurl){
		var img=new Image();
		img.src=picurl;
		showDialog("<?=__SELF__?>?do=dialog&operation=showpic",picurl,img.width+20,img.height+20);
	}else{
		alert("没有图片!");
	}
//	$("#"+id).focus();
}
function cutPic(id){
	var picurl	=$("#"+id).val();
	if(picurl){
		showDialog("<?=__SELF__?>?do=dialog&operation=cutpic",picurl);
	}else{
		alert("没有图片!");
	}
//	$("#"+id).focus();
}
function editContentLink(fieldName){
	showDialog("<?=__SELF__?>?do=dialog&operation=article",fieldName);
}
</script>
<div class="container" id="cpcontainer">
  <h3><?=empty($id)?'添加':'编辑'?><?=$model->name?></h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>点击ID可查看该<?=$model->name?></li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=content&operation=post" method="post" enctype="multipart/form-data" name="savecontent" id="savecontent">
    <input type="hidden" name="action" value="save" />
    <input name="mid" type="hidden" id="mid" value="<?=$mid?>" />
    <input name="table" type="hidden" id="table" value="<?=$model->table?>" />
    <table class="tb tb2 ">
    <?php for($i=0;$i<$fcount;$i++){if($form[$i]['general']){?>
      <tr>
        <td style="width:100px"><?=$form[$i]['general']['label']?>：</td>
        <td style="width:auto"><?=$form[$i]['general']['html']?><?=$form[$i]['general']['description']?></td>
      </tr>
      <?php }}?>	  
      <tr class="nobg">
        <td colspan="2" align="center">
    	 <?php for($i=0;$i<$fcount;$i++){if($form[$i]['hidden']){?><?=$form[$i]['hidden']?><?php }}?>	  
         <input name="id" type="hidden" id="id" value="<?=$id?>" />
         <input name="mVal[userid]" type="hidden" id="userid" value="<?=$rs['userid']?>" />
         <input name="mVal[postype]" type="hidden" id="postype" value="1" />
         <input type="submit" value="提交" class="btn" />
        &nbsp;&nbsp;<input type="reset" value="重置" class="btn" /></td>
      </tr>
    </table>
  </form>
</div>
</body></html>