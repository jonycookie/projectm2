<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
iCMS_admincp_head();
?><link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;组管理','');</script>
<div class="container" id="cpcontainer">
  <h3>分配组管理权限</h3>
  <form action="<?=__SELF__?>?do=group&operation=post" method="post">
    <input type="hidden" name="action" value="power" />
    <input type="hidden" name="gid" value="<?=$rs->gid?>" />
    <table class="tb tb2 ">
      <tr>
        <td colspan="2">设置[<?=$rs->name?>]组管理权限</td>
      </tr>
       <tr>
        <td colspan="2"><input name="power[]" type="checkbox" class="checkbox" value="ADMINCP" /> 允许进入后台</td>
      </tr>
      <tr>
        <td id="powerdiv" colspan="2" style="width:auto;">
	      <table style="width:100%"><tr>
	      <?php foreach($menu_array AS $H=>$value){?>
	      <td valign="top" style="width:10%"><h4><input name="power[]" type="checkbox" class="checkbox" value="header_<?=$H?>" /><?=lang('header_'.$H)?></h4><ul>
		      <?php foreach($value AS $key=>$url){?>
		      	  <li>├<input name="power[]" type="checkbox" class="checkbox" value="<?=$key?>" parent="header_<?=$H?>"/><?=lang($key)?></li>
		      <?php }?>
		      	</ul></td>
	      <?php }?>
	       </tr></table>
      </td>
      </tr>
       <tr>
        <td><input name="power[]" type="checkbox" class="checkbox" value="Allow_View_Article" /> 允许查看所有文章</td>
        <td>在有权限的栏目 是否查看该栏目所有文章 不选只能看到自己的发表的文章</td>
      </tr>
       <tr>
        <td><input name="power[]" type="checkbox" class="checkbox" value="Allow_Edit_Article" /> 允许编辑所有文章</td>
        <td>在有权限的栏目 是否编辑该栏目所有文章 不选只能编辑自己的发表的文章</td>
      </tr>
     <tr class="nobg">
        <td colspan="2"><div class="fixsel"> <input type="submit" class="btn" value="提交" /> </div></td>
      </tr>
    </table>
  </form>
</div>
<script type="text/javascript">
$(function(){ 
	var powerText	= '<?=$rs->power?>';
	var powerArray	= powerText.split(',');
	for (i=0;i<powerArray.length;i++){
		$("input[name^=power][value="+powerArray[i]+"]").attr('checked',true);
	}
	$("input[parent]").click(function(){
		var p=$(this).attr("parent");
		var all=$("input[parent="+p+"]");
		var s=false;
		for (i=0;i<all.length;i++){
			if($(all[i]).attr("checked")){
				s=true;
				break;
			}
		}
		s && $("input[value="+p+"]").attr('checked',true);
	}); 
	$("input[value^=header]").click(function(){
		var sub=$("input[parent="+$(this).val()+"]");
		if($(this).attr("checked")){
			sub.attr('checked',true);
		}else{
			sub.attr('checked',false);
		}
	});
	$("input[value=ADMINCP]").click(function(){
		if(!$(this).attr("checked")){
			$("input[name^=power]").attr('checked',false);
		}
	});
});
</script>
</body></html>