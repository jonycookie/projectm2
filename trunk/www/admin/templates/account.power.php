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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;管理员管理','');</script>
<div class="container" id="cpcontainer">
  <h3>分配管理权限</h3>
  <!--table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>注:添加栏目、添加文章、评论管理、审核文章、友情链接、广告管理</li>
        </ul></td>
    </tr>
  </table-->
  <form action="<?=__SELF__?>?do=account&operation=post" method="post">
    <input type="hidden" name="action" value="power" />
    <input type="hidden" name="uid" value="<?=$rs->uid?>" />
    <table class="tb tb2 ">
      <tr>
        <td colspan="2">设置[<?=$rs->username?>]管理权限</td>
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
     <tr class="nobg">
        <td colspan="2"><div class="fixsel"> <input type="submit" class="btn" name="forumlinksubmit" value="提交"  /> </div></td>
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