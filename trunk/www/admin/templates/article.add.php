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
<script type="text/javaScript">admincpnav('首页&nbsp;&raquo;&nbsp;文章管理&nbsp;&raquo;&nbsp;<?=empty($id)?'添加':'编辑'?>文章','');</script>
<link rel="stylesheet" href="<?=$iCMS->dir?>admin/images/jquery.function.css" type="text/css" media="all" />
<script type="text/javascript" src="<?=$iCMS->dir?>javascript/jquery.function.js"></script>
<script type="text/javascript" src="<?=$iCMS->dir?>javascript/calendar.js"></script>
<script type="text/javascript" src="<?=$iCMS->dir?>javascript/editor.js"></script>
<script type="text/javascript">
$(function(){
	$("#title").focus();
	$("#savearticle").submit(function(){
		if($("#catalog option:selected").attr("value")=="0"){
			$("a[tid=base]").click();
			alert("请选择所属栏目");
			$("#catalog").focus();
			return false;
		}
		if($("#title").val()==''){
			$("a[tid=base]").click();
			alert("标题不能为空!");
			$("#title").focus();
			return false;
		}
		if($("#url").val()==''){
			var oEditor = FCKeditorAPI.GetInstance('content') ;
			if(oEditor.GetXHTML( true )==''){
				$("a[tid=base]").click();
				alert("内容不能为空!");
				oEditor.Focus();
				return false;
			}
		}
		var related="";
		var optobj=$("#data_related option");
		for (var i = 0; i < optobj.length; i++) {
			related+=$(optobj[i]).attr("value")+"#|$"+$(optobj[i]).text()+"~#~";
		}
		if(related!="")$("#related").val(related);
	}); 
	$("#keywordToTag").click( function(){
		$("#tag").toggle();
	}); 
<?php if($iCMS->config['keywordToTag']=="1" && !$id){?>
	$("#keywordToTag").click().attr("checked","checked");
	$("#tag").val("");
<?php }?>
	$("#navlist > li > a").click( function(){
		var that=this;
		$("#navlist > li > a").each(function(){
			this.id="";
			$("#"+$(this).attr("tid")).hide();
		});
		that.id="current";
		$("#"+$(that).attr("tid")).show();
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
		showDialog("<?=__SELF__?>?do=dialog&operation=cutpic&pic="+picurl,id);
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
  <h3><?=empty($id)?'添加':'编辑'?>文章</h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>点击ID可查看该文章</li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=article&operation=post" method="post" enctype="multipart/form-data" name="savearticle" id="savearticle">
    <input type="hidden" name="action" value="save" />
    <table class="tb tb2 ">
    <tr><td colspan="4">
	  <ul id="navlist">
	    <li><a id="current" href="javascript:void(0);" tid="base">基本信息</a></li>
	    <li><a href="javascript:void(0);" tid="publish">发布设置</a></li>
	  </ul>
    </td></tr>
    <tbody id="base">
      <tr>
        <td class="td27">栏目：</td>
        <td colspan="3" class="vtop rowform"><?php if($cata_option){?><select name="catalog" id="catalog" style="width:auto;">
          <option value="0"> == 请选择所属栏目 == </option>
          <?php echo $cata_option;}else{?><select name="catalog" id="catalog" onclick="redirect('<?=__SELF__?>?do=catalog&operation=add');">
          <option value="0"> == 暂无栏目请先添加 == </option>
          <?php }?>
        </select></td>
      </tr>
      <tr>
        <td class="td27" style="width:70px;">标题：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><input type="text" name="title" class="txt" id="title" value="<?=$rs['title']?>" style="width:520px"/></td>
    </td>
      </tr>
      <tr>
        <td class="td27">短标题：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><input name="stitle" class="txt" id="stitle" value="<?=$rs['stitle']?>"  style="width:520px" type="text"/></td>
      </tr>
      <tr>
        <td class="td27">出处：</td>
        <td class="vtop rowform"><input type="text" name="source" class="txt" id="source" value="<?=$rs['source']?>" style="width:200px" /> <button type="button" class="selectdefault" to="source"><span>预 设</span></button></td>
        <td class="td27">作者：</td>
        <td class="vtop rowform"><input type="text" name="author" class="txt" id="author" value="<?=$rs['author']?>" style="width:200px" /> <button type="button" class="selectdefault" to="author"><span>预 设</span></button></td>
      </tr>
      <tr>
        <td class="td27">编辑：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><input name="editor" class="txt" type="text" id="editor" value="<?=$rs['editor']?>" /> <button type="button" class="selectdefault" to="editor"><span>预 设</span></button></td>
      </tr>
      <tr>
        <td class="td27">缩略图：</td>
        <td colspan="3" class="vtop rowform" style="width:auto">
          <input id="pic" name="pic" type="text" value="<?=$rs['pic']?>" class="txt" style="width:450px"/> <button type="button" class="selectdefault" to="pic"><span>选 择</span></button><div id="picmenu" style="display:none;">
<ul><li onClick="showDialog('<?=__SELF__?>?do=dialog&operation=Aupload','pic',600,140);$('.close').click();">本地上传</li><li onClick="showDialog('<?=__SELF__?>?do=dialog&operation=file&hit=file&type=gif,jpg,png,bmp,jpeg','pic',600,500);$('.close').click();">从网站选择</li><li onClick="showPic('pic');$('.close').click();">查看缩略图</li><li onClick="cutPic('pic');$('.close').click();">剪裁</li></ul></div>
            </td>
      </tr>
      <tr>
        <td class="td27">副标题：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><input name="subtitle" class="txt" id="subtitle" value="<?=$rs['subtitle']?>"  style="width:520px" type="text"/></td>
      </tr>
      <tr>
        <td class="td27">内容：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><span style="float:right;"><img src="<?=$iCMS->dir?>admin/images/add.gif" onclick="setEditorSize('+','content',200)" title="增加编辑器高度"/> <img src="<?=$iCMS->dir?>admin/images/desc.gif" onclick="setEditorSize('-','content',200)" title="减少编辑器高度"/></span><input name="remote" type="checkbox" class="checkbox" id="remote" value="1" <?php if($iCMS->config['remote']=="1")echo 'checked="checked"'?>/>下载远程图片
    <!--<input name="dellink" type="checkbox" id="dellink" value="1" />删除非站内链接 -->
        <input name="autopic" type="checkbox" class="checkbox" id="autopic" value="1" <?php if($iCMS->config['autopic']=="1")echo 'checked="checked"'?>/>提取第一个图片为缩略图 
        <input name="draft" type="checkbox" class="checkbox" id="draft" value="1" <?php if($rs['visible']=="0")echo 'checked="checked"'?>/>存为草稿 </td>
      </tr>
      <tr class="nobg">
        <td colspan="4">
        	<textarea name="content" id="content" class="editor" rows="30" cols="80"><?=$rs['body']?></textarea>
        </td>
      </tr>
      <tr>
        <td class="td27">摘要：</td>
        <td colspan="3" class="vtop rowform"><textarea name="description" id="description" onKeyUp="textareasize(this)" class="tarea" style="width:460px"><?=$rs['description']?></textarea></td>
      </tr>
      <tr>
        <td class="td27">关键字：</td>
        <td colspan="3" class="vtop rowform"><input name="keywords" class="txt" type="text" id="keywords" value="<?=$rs['keywords']?>" style="width:460px"/> 多个关键字请用,格开</td>
      </tr>
      <tr>
        <td class="td27">标签：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><input name="tag" class="txt" type="text" id="tag" size="50" value="<?=$rs['tags']?>" /><input name="keywordToTag" type="checkbox" class="checkbox" id="keywordToTag" value="1" />
        将关键字转为标签<br />多个标签请用,格开</td>
      </tr>
      <tr>
        <td class="td27">相关文章：</td>
        <td><select id='data_related' size='10' style="width:100%"><?php 
        	$relatedArray=explode("~#~",$rs['related']);
        	if($relatedArray)foreach($relatedArray AS $idtitle){
        		list($reid,$retitle)=explode("#|$",$idtitle);
        		if($reid&&$retitle){
        			echo '<option value="'.$reid.'">'.$retitle.'</option>';
        		}
        	}
        	?></select>
         <input name="related" type="hidden" id="related" value="" />
</td>
<td colspan="2">
<input type="button" onclick="del(this.form.data_related)" value="×" tabindex="13" class="btn"/>
<br/>
<br/>
<input type="button" onclick="moveUp(this.form.data_related)" value="∧" tabindex="13" class="btn"/>
<br/>
<input type="button" onclick="moveDown(this.form.data_related)" value="∨" tabindex="13" class="btn"/>
<br/>
<br/>
<input type="button" onclick="editContentLink('related')" value="..." tabindex="13" class="btn"/>
</td>
      </tr>
      </tbody>
      <tbody id="publish" style="display:none;">
      <tr>
        <td style="width:70px;"></td>
        <td class="vtop rowform"></td>
        <td class="td27"></td>
        <td class="vtop rowform"></td>
    </td>
      <tr>
        <td class="td27">虚链接：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><select name="vlink[]" size="10" multiple="multiple" id="vlink">
          <?=$catalog->select(0,0,1,'channel=1&list&page','all')?>
        </select><script language="JavaScript" type="text/javascript"><?php if(strpos($rs['vlink'], ",")){?>var type='<?=$rs['vlink']?>';$('#vlink').val(type.split(','));<?php }else{?>$('#vlink').val(<?=(int)$rs['vlink']?>);<?php }?></script><br />按住Ctrl可多选    </td>
    </tr>
      <tr>
        <td class="td27">属性：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><select name="type[]" size="10" multiple="multiple" id="type">
          <option value="0">普通文章[type='0']</option>
          <?=contentype("article")?>
        </select><script language="JavaScript" type="text/javascript"><?php if(strpos($rs['type'], ",")){?>var type='<?=$rs['type']?>';$('#type').val(type.split(','));<?php }else{?>$('#type').val(<?=(int)$rs['type']?>);<?php }?></script><br />按住Ctrl可多选    </td>
    </tr>
      <tr>
        <td class="td27">置顶权重：</td>
        <td colspan="3" class="vtop rowform"><input id="top" class="txt" value="<?=$rs['top']?>" name="top" type="text"/></td>
      </tr>
      <tr>
        <td class="td27">发布时间：</td>
        <td colspan="3" class="vtop rowform"><input id="pubdate" class="txt" value="<?=$rs['pubdate']?>" name="pubdate" type="text" onclick="showcalendar(event, this)" style="width:208px"/></td>
      </tr>
      <tr>
        <td class="td27">模板：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><input id="template" class="txt" value="<?=$rs['tpl']?>" name="template" type="text"/> <img src="admin/images/selecttpl.gif" width="67" height="19" align="absmiddle" onclick="showDialog('<?=__SELF__?>?do=dialog&operation=template&hit=file&type=htm','template');"/></td>
      </tr>
      <tr>
        <td class="td27">自定链接：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><input name="customlink" class="txt" type="text" id="customlink" value="<?=$rs['customlink']?>" />
        <br />
        只能由英文字母、数字或_-组成(不支持中文),留空则自动以标题拼音填充</td>
      </tr>
      <tr>
        <td class="td27">外部链接：</td>
        <td colspan="3" class="vtop rowform" style="width:auto"><input name="url" class="txt" type="text" id="url" size="50" value="<?=$rs['url']?>" /> 
        不填写请留空.</td>
      </tr>
       </tbody>
      <!--tr class="nobg"><td colspan="4">
		<table width="100%" cellspacing="0" cellpadding="0">
			<tr><th width="50%"><b>上传附件</b></th><th><b>描述</b></th></tr>
			<tbody id="attachbodyhidden" style="display:none"><tr><th><input type="file" name="attach[]" class="uploadbtn"/><span id="localfile[]"></span><input type="hidden" name="localid[]" /></th><th class="vtop rowform"><input type="text" name="attachdesc[]" class="txt" /></th></tr></tbody>
			<tbody id="attachbody"></tbody>
		</table>
		<div id="img_hidden" alt="1" style="position:absolute;top:-100000px;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='image');width:400px;height:300px"></div>
			</td></tr-->
      <tr class="nobg">
        <td colspan="4" align="center"><input name="aid" type="hidden" id="aid" value="<?=$id?>" />
         <input name="userid" type="hidden" id="userid" value="<?=$rs['userid']?>" />
         <input name="postype" type="hidden" id="postype" value="1" />
        <input name="action" type="hidden" id="action" value="save" />
        <input type="submit" value="提交" class="btn" />
        &nbsp;&nbsp;<input type="reset" value="重置" class="btn" /></td>
      </tr>
    </table>
  </form>
</div>
</body></html>