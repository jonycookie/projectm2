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
<script type="text/javascript">
$(function(){
	$("#clist tr").mouseover(function(){
	  $(this).find("td").css("background-color","#F2F9FD");
	}).mouseout(function(){
	  $(this).find("td").css("background-color","#FFFFFF");
	});
});
function expand(cid,level,obj){
	$.ajax({type: "POST",url: "<?=__SELF__?>?do=ajax",dataType:"json",
	   data: "action=getsubcatalog&cid="+cid+"&level="+level,
	   success: function(json){
			$("#catalog_"+cid).after(json.html);
			$(obj).attr("ids"+cid,json.ids); 
			obj.src="admin/images/desc.gif";
			obj.onclick=function(){fold(cid,level,obj)}
	   }
	}); 
}
function fold(cid,level,obj){
	var cidArray	= $(obj).attr("ids"+cid).split(',');
	for (i=0;i<cidArray.length;i++){
		$("#catalog_"+cidArray[i]).remove();
	}
	obj.src="admin/images/add.gif";
	obj.onclick=function(){expand(cid,level,obj)}
}
</script>

<div class="container" id="cpcontainer">
  <script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;栏目&nbsp;&raquo;&nbsp;栏目管理','<a href="<?=__SELF__?>?action=misc&operation=custommenu&do=add&title=cplog_forums&url=action%3Dforums" target="main"><img src="admin/images/btn_add2menu.gif" title="添加常用操作" width="19" height="18" /></a>');</script>
  <div class="itemtitle">
    <h3>栏目管理</h3>
    <ul class="tab1" id="submenu">
      <li<?php if($operation=="expand"){?> class="current"<?php }?>><a href="<?=__SELF__?>?do=catalog&operation=expand"><span>展开所有栏目</span></a></li>
      <li<?php if($operation=="fold"){?> class="current"<?php }?>><a href="<?=__SELF__?>?do=catalog&operation=fold"><span>收缩所有栏目</span></a></li>
    </ul>
  </div>
  <form name="cpform" method="post" action="<?=__SELF__?>?do=catalog&operation=post" id="cpform" >
    <input type="hidden" name="action" value="edit" />
    <table class="tbL tb tb2 ">
      <tr>
        <th>显示顺序</th>
        <th>栏目名称</th>
        <th>管理</th>
      </tr>
      <tbody id="clist">
      <?php if($operation=="fold"){
      	  echo $catalog->row("0",0);
      }elseif($operation=="expand"){
	      echo $catalog->all();
      }
      ?>
	  </tbody>
      <tr class="nobg">
        <td colspan="14"><div class="fixsel">
            <input type="submit" class="btn" name="editsubmit" value="提交"  />
          </div></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>