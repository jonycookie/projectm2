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
<link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/JavaScript">
admincpnav('首页&nbsp;&raquo;&nbsp;文章管理','');
$(function(){
<?php if(isset($_GET['at'])){?>
$("#at").val("<?=$_GET['at']?>");
<?php } if($_GET['cid']){?>
$("#cid").val("<?=$_GET['cid']?>");
<?php } if($_GET['st']){?>
$("#st").val("<?=$_GET['st']?>");
<?php } if($_GET['orderby']){?>
$("#orderby").val("<?=$_GET['orderby']?>");
<?php } if($_GET['sub']=="on"){?>
$("#sub").attr("checked",true);
<?php } if($_GET['nopic']=="on"){?>
$("#nopic").attr("checked",true);
<?php }?>
	$("#contentypedivclose").click(function(){
	    $("#contentypediv").slideUp("slow");
	    $("#contentype").removeAttr("checked");
	});
	$("#contentype").click(function(){
		var offset 		=$(this).offset();
		var snapTop 	= offset.top-$("#contentypediv").height()-10;
		var snapLeft 	= offset.left;
		$("#contentypediv").css({"top" : snapTop, "left" : snapLeft}).slideDown("slow");
	  });
//	  $("#alist tr").mouseover(function(){
//	  	  $(this).find("td").css("background-color","#F2F9FD");
//	  }).mouseout(function(){
//	  	  $(this).find("td").css("background-color","#FFFFFF");
//	  });
	$(".close").click(function(){
		var parentdiv=$(this).attr("parent");
	    $("#"+parentdiv).slideUp("slow");
	    $("#action").val("");
	});
});
function doaction(obj){
	var offset 		= $(obj).offset();
	var snapTop 	= offset.top-$("#"+obj.value+"div").height()-10;
	var snapLeft 	= offset.left;
//	$(".tipsdiv").slideUp("slow");
	$(".tipsdiv").hide();
	switch(obj.value){ 
		case "contentype":
//			$("#contentypediv").css({"top" : snapTop, "left" : snapLeft}).slideDown("slow");
			$("#contentypediv").css({"top" : snapTop, "left" : snapLeft}).show();
		break;
		case "move":
			$("#movediv").css({"top" : snapTop, "left" : snapLeft}).show();
		break;
		case "push":
			$("#pushdiv").css({"top" : snapTop, "left" : snapLeft}).show();
		break;
		case "top":
			$("#topdiv").css({"top" : snapTop, "left" : snapLeft}).show();
		break;
		case "keyword":
			$("#keyworddiv").css({"top" : snapTop, "left" : snapLeft}).show();
		break;
		case "tag":
			$("#tagdiv").css({"top" : snapTop, "left" : snapLeft}).show();
		break;
		case "vlink":
			$("#vlinkdiv").css({"top" : snapTop, "left" : snapLeft}).show();
		break;
		case "del":
			if(confirm("确定要删除！！！")){
				return true;
			}else{
				obj.value="";
				return false;
			}
		break;
	}
}
</script>
<style type="text/css">
.tipsdiv {position:absolute;z-index:10;top:-400px;left:-400px;display:none;background-color:#FFFFFF; border:#CCCCCC 1px solid;}
</style>
<div id="append_parent"></div>
<script type="text/javascript" src="javascript/calendar.js"></script>
<div class="container" id="cpcontainer">
  <h3>文章管理</h3>
  <form action="<?=__SELF__?>" method="get">
    <input type="hidden" name="do" value="article" />
    <input type="hidden" name="operation" value="manage" />
    <?php if($act=='user'){?>
    <input type="hidden" name="act" value="user" />
    <?php }?>
    <table class="tb tb2 ">
      <tr>
        <td class="tipsblock"><select name="at" id="at">
            <option value="0">普通文章[type='0']</option>
            <?=contentype("article")?>
          </select>
          　<select name="cid" id="cid">
          	<option value="0"> == 按栏目 == </option>
            <?=$catalog->select(0,0,1,"channel&list")?>
          </select>
        <input type="checkbox" name="sub" class="checkbox" id="sub"/>子栏目
        <select name="st" id="st">
            <option value="title">标题</option>
            <option value="tkd">标题/关键字/简介</option>
          </select>
        <select name="orderby" id="orderby">
        	<optgroup label="降序"></optgroup>
            <option value="id DESC">ID[降序]</option>
            <option value="hits DESC">点击[降序]</option>
            <option value="digg DESC">顶[降序]</option>
            <option value="pubdate DESC">时间[降序]</option>
            <option value="comments DESC">评论[降序]</option>
        	<optgroup label="升序"></optgroup>
            <option value="id ASC">ID[升序]</option>
            <option value="hits ASC">点击[升序]</option>
            <option value="digg ASC">顶[升序]</option>
            <option value="pubdate ASC">时间[升序]</option>
            <option value="comments ASC">评论[升序]</option>
          </select>
        <input type="checkbox" name="nopic" class="checkbox" id="nopic"/>无缩略图
        </td>
      </tr>
     <tr>
        <td class="tipsblock">开始时间：<input type="text" class="txt" name="starttime" value="<?=$_GET['starttime']?>" onClick="showcalendar(event, this)" style="width:80px">-结束时间：<input type="text" class="txt" name="endtime" value="<?=$_GET['endtime']?>" onClick="showcalendar(event, this)" style="width:80px">  关键字：
          <input type="text" name="keywords" class="txt" id="keywords" value="<?=$_GET['keywords']?>" size="30" />
          每页显示<input type="text" name="perpage" class="txt" id="perpage" value="<?=$_GET['perpage']?$_GET['perpage']:20?>" style="width:30px;" />
          <input type="submit" class="btn" value="搜索"/>
        </td>
      </tr>
    </table>
  </form>
  <form action="<?=__SELF__?>?do=article&operation=post" method="post">
    <table class="tbL tb tb2 ">
      <tr>
        <th width="4%" height="22">选择</th>
        <th width="4%">ID</th>
        <th width="4%">排序</th>
        <th>标 题</th>
        <th width="16%">发布时间</th>
        <th width="10%">栏目</th>
        <th width="7%">点/评</th>
        <th width="4%">状态</th>
        <th width="18%">操作</th>
      </tr>
      <tbody id="alist">
      <?php for($i=0;$i<$_count;$i++){
      	$ourl=$rs[$i]['url'];
      	$C=$catalog->catalog[$rs[$i]['cid']];
      	$iurlArray=array('id'=>$rs[$i]['id'],'link'=>$rs[$i]['customlink'],'url'=>$rs[$i]['url'],'dir'=>$iCMS->cdir($C),/*'domain'=>$C['domain'],*/'pubdate'=>$rs[$i]['pubdate']);
		empty($ourl) && $htmlurl=$iCMS->iurl('show',$iurlArray,'',iPATH);
		$rs[$i]['url']= $iCMS->iurl('show',$iurlArray);
	?>
      <tr>
        <td><input type="checkbox" class="checkbox" name="id[]" value="<?=$rs[$i]['id']?>" /></td>
        <td><a href="<?=$rs[$i]['url']?>" target="_blank"><?=$rs[$i]['id']?></a></td>
        <td><input type="text" name="order[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['order']?>" style="width:20px;border:1px #F6F6F6 solid;"/></td>
        <td><div style="height:22px;width:100%;overflow:hidden;"><a href="<?=__SELF__?>?do=article&operation=visible&id=<?=$rs[$i]['id']?>&v=<?=$rs[$i]['visible']?>" title="点击<?php if($rs[$i]['visible']=="1"){echo empty($act)?'转成草稿">':'取消审核">';}else{ echo empty($act)?'发布">':'通过审核">';}?><img src="admin/images/article.gif" align="absmiddle"></a> <?php if($rs[$i]['pic'])echo '<img src="admin/images/image.gif" align="absmiddle">'?> <?=$rs[$i]['title']?></div></td>
        <td><?=get_date($rs[$i]['pubdate'],'Y-m-d H:i');?></a></td>
        <td><a href="<?=__SELF__?>?do=article&operation=manage&cid=<?=$rs[$i]['cid']?><?=$uri?>"><?=$C['name']?></a></td>
        <td><?=$rs[$i]['hits']?>/<a href="<?=$iCMS->dir?>comment.php?aid=<?=$rs[$i]['id']?>" target="_blank"><?=$rs[$i]['comments']?></a></td>
        <td>
        	<?php if($ourl){?><img src="admin/images/olink.gif" align="absmiddle" alt="外部链接"><?php }?>
        	<?php if($cid && $cid!=$rs[$i]['cid']){?>虚<?php }?>
        </td>
        <td><?php if($cid&&$cid!=$rs[$i]['cid']){?>
        	<a href="<?=__SELF__?>?do=article&operation=delvlink&id=<?=$rs[$i]['id']?>&cid=<?=$cid?>" onclick="return confirm('删除<?=$rs[$i]['title']?>此栏目的虚链接?');">删除</a>
        	<?php }else{ ?>
        		<?php if ($iCMS->config['ishtm'] && $rs[$i]['visible']=="1" && empty($ourl)){?>
        			<a href="<?=__SELF__?>?do=article&operation=updateHTML&id=<?=$rs[$i]['id']?>"><?=file_exists($htmlurl)?"更新":"待发布"?></a> | 
        		<?php }?>
        			<a href="<?=__SELF__?>?do=article&operation=add&id=<?=$rs[$i]['id']?>">编辑</a> | <a href="<?=__SELF__?>?do=article&operation=del&id=<?=$rs[$i]['id']?>" onclick="return confirm('确定要删除<?=HTML2JS($rs[$i]['title'])?>');">删除</a>
        	<?php }?></td>
      </tr>
      <?php }?>
      </tbody>
      <tr>
        <td colspan="9" align="right"><?=$pagenav?></td>
      </tr>
      <tr class="nobg">
        <td class="td23"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'id')" />
          <label for="chkall">全选</label></td>
        <td colspan="8"><div class="fixsel"><select name="action" id="action" onChange="doaction(this);">
              <option value="">========批 量 操 作=======</option>
              <option value="cancel"><?=empty($act)?"转为草稿":"取消审核"?></option>
              <option value="passed"><?=empty($act)?"发布":"通过审核"?></option>
              <option value="passTime"><?=empty($act)?"发布&更新时间":"通过审核&更新时间"?></option>
              <optgroup label="===================="></optgroup>
              <option value="passTimeALL">全部<?=empty($act)?"发布&更新时间":"通过审核&更新时间"?></option>
              <option value="passALL">全部<?=empty($act)?"发布":"通过审核"?></option>
              <option value="TimeALL">全部更新时间</option>
              <optgroup label="===================="></optgroup>
<?php if($iCMS->config['ishtm']){?><option value="updateHTML">更新HTML</option><?php }?>
              <option value="top">设置置顶权重</option>
              <option value="contentype">设置文章属性</option>
              <option value="thumb">提取缩略图</option>
              <option value="move">移动栏目</option>
              <option value="keyword">设置关键字</option>
              <option value="tag">设置标签</option>
              <option value="vlink">设置虚拟链接</option>
              <!--option value="push">推送</option-->
              <optgroup label="===================="></optgroup>
              <option value="del">删除</option>
            </select><input type="submit" class="btn" name="forumlinksubmit" value="提交"  /></div></td>
      </tr>
    </table>
	<div id="contentypediv"  class="tipsdiv" style="width:210px;">
	  <table class="tb tb2 nobdb" id="tips">
	    <tr>
	      <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" class="close" parent="contentypediv"><img src="admin/images/close.gif" /></span>选择文章属性</th>
	    </tr>
	    <tr>
	      <td class="tipsblock" style="padding-left:5px;">
			<select name="type[]" size="10" multiple="multiple" id="type" style="width:98%;">
			<option value="0">普通文章[type='0']</option>
			<?=contentype("article")?>
			</select><br />按住Ctrl可多选
		 </td>
	    </tr>
	  </table>
	</div>
	<div id="movediv" class="tipsdiv"  style="width:280px;">
	  <table class="tb tb2 nobdb" id="tips">
	    <tr>
	      <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" class="close" parent="movediv"><img src="admin/images/close.gif" /></span>请选择栏目</th>
	    </tr>
	    <tr>
	      <td class="tipsblock" style="padding-left:5px;">
	       <select name="cataid" id="cataid">
	       	<option value="">请选择目标栏目</option>
	       	<?=$catalog->select(0,0,1,'channel=1&list')?>
	       	</select>
		 </td>
	    </tr>
	  </table>
	</div>
	<div id="keyworddiv" class="tipsdiv"  style="width:280px;">
	  <table class="tb tb2 nobdb" id="tips">
	    <tr>
	      <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" class="close" parent="keyworddiv"><img src="admin/images/close.gif" /></span>请输入关键字</th>
	    </tr>
	    <tr>
	      <td class="tipsblock" style="padding-left:5px;">
	      <textarea name="keyword" id="keyword" onKeyUp="textareasize(this)" class="tarea"></textarea><br />
	      追加<input name="pattern" type="radio" class="radio" value="addto" />
	      替换<input name="pattern" type="radio" class="radio" value="replace" />
		 </td>
	    </tr>
	  </table>
	</div>
	<div id="topdiv" class="tipsdiv"  style="width:280px;">
	  <table class="tb tb2 nobdb" id="tips">
	    <tr>
	      <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" class="close" parent="topdiv"><img src="admin/images/close.gif" /></span>请输入权重</th>
	    </tr>
	    <tr>
	      <td class="tipsblock" style="padding-left:5px;">
	      <input type="text" name="top" class="txt" id="top" value=""/>
		 </td>
	    </tr>
	  </table>
	</div>
	<div id="tagdiv" class="tipsdiv"  style="width:280px;">
	  <table class="tb tb2 nobdb" id="tips">
	    <tr>
	      <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" class="close" parent="tagdiv"><img src="admin/images/close.gif" /></span>请输入标签</th>
	    </tr>
	    <tr>
	      <td class="tipsblock" style="padding-left:5px;">
	      <textarea name="tag" id="tag" onKeyUp="textareasize(this)" class="tarea"></textarea><br />
	      追加<input name="pattern" type="radio" class="radio" value="addto" />
	      替换<input name="pattern" type="radio" class="radio" value="replace" />
		 </td>
	    </tr>
	  </table>
	</div>
	<div id="vlinkdiv" class="tipsdiv"  style="width:280px;">
	  <table class="tb tb2 nobdb" id="tips">
	    <tr>
	      <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" class="close" parent="vlinkdiv"><img src="admin/images/close.gif" /></span>请选择关联栏目</th>
	    </tr>
	    <tr>
	      <td class="tipsblock" style="padding-left:5px;">
	      <select name="vlink[]" size="10" multiple="multiple" id="vlink" style="width:98%;">
          <?=$catalog->select(0,0,1,'channel=1&list&page','all')?>
        </select><br />
	      追加<input name="pattern" type="radio" class="radio" value="addto" />
	      替换<input name="pattern" type="radio" class="radio" value="replace" />
		 </td>
	    </tr>
	  </table>
	</div>
  </form>
</div>
</body></html>