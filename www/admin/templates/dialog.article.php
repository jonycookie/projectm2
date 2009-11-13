<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=iCMS_CHARSET?>">
<link rel="stylesheet" href="<?=$iCMS->dir?>admin/images/style.css" type="text/css" media="all" />
<script src="javascript/jquery.js" type="text/javascript"></script>
<script src="javascript/admin.fun.js" type="text/javascript"></script>
</head>
<body>
<script type="text/JavaScript">
$(function(){
	window.focus();
	  $("#alist tr").mouseover(function(){
	  	  $(this).find("td").css("background-color","#F2F9FD");
	  }).mouseout(function(){
	  	  $(this).find("td").css("background-color","#FFFFFF");
	  });
	  $("#forumlinksubmit").click(function(){ 
		  var obj=$(window.opener.document.getElementById("data_related"));
		  var IDobj=$("[name='id[]']:checked");
		  var option="";
		  for (var i = 0; i < IDobj.length; i++) {
			   var id=$(IDobj[i]).val();
			   var title=$("#title_"+id).text();
			   option+='<option value="'+id+'">'+title+'</option>';
		   }
		   if(option!="")obj.append(option);
		   window.close();
   	  }); 
});

</script>
<div class="container" id="cpcontainer">
  <h3>文章管理</h3>
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
  <form action="<?=__SELF__?>" method="get">
    <input type="hidden" name="do" value="dialog" />
    <input type="hidden" name="operation" value="article" />
    <input type="hidden" name="in" value="<?=$_GET['in']?>" />
    <table class="tb tb2 ">
     <tr>
        <td class="tipsblock">关键字：
          <input type="text" name="keywords" class="txt" id="keywords" value="<?=$_GET['keywords']?>" size="30" />
          <input type="submit" class="btn" value="搜索"/>
        </td>
      </tr>
    </table>
  </form>
  <form method="post" onsubmit="return false;">
    <table class="tbL tb tb2 ">
      <tr>
        <th width="4%" height="22">选择</th>
        <th width="4%">ID</th>
        <th>标 题</th>
        <th width="16%">发布时间</th>
        <th width="10%">栏目</th>
        <th width="7%">点/评</th>
      </tr>
      <tbody id="alist">
      <?php for($i=0;$i<$_count;$i++){
      	$ourl=$rs[$i]['url'];
		$rs[$i]['url']= $iCMS->iurl('show',array('id'=>$rs[$i]['id'],'link'=>$rs[$i]['customlink'],'url'=>$rs[$i]['url'],'dir'=>$iCMS->cdir($catalog->catalog[$rs[$i]['cid']]),'pubdate'=>$rs[$i]['pubdate']));
	?>
      <tr>
        <td><input type="checkbox" class="checkbox" name="id[]" value="<?=$rs[$i]['id']?>" /></td>
        <td><a href="<?=$rs[$i]['url']?>" target="_blank"><?=$rs[$i]['id']?></a></td>
        <td><div style="height:22px;width:100%;overflow:hidden;"><?php if($rs[$i]['pic'])echo '<img src="admin/images/file/image.gif" align="absmiddle">'?> <span id="title_<?=$rs[$i]['id']?>"><?=$rs[$i]['title']?></span></div></td>
        <td><?=get_date($rs[$i]['pubdate'],'Y-m-d H:i');?></a></td>
        <td><a href="<?=__SELF__?>?do=dialog&operation=article&cid=<?=$rs[$i]['cid']?><?=$uri?>&in=<?=$in?>"><?=$catalog->catalog[$rs[$i]['cid']]['name']?></a></td>
        <td><?=$rs[$i]['hits']?>/<?=$rs[$i]['comments']?></td>
      </tr>
      <?php }?>
      </tbody>
      <tr>
        <td colspan="9" align="right"><?=$pagenav?></td>
      </tr>
      <tr class="nobg">
        <td class="td23"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'id')" />
          <label for="chkall">全选</label></td>
        <td colspan="8"><div class="fixsel"><input type="button" class="btn" id="forumlinksubmit" value="选定提交"  /></div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>