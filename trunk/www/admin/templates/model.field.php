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
<script type="text/javascript">admincpnav('首页&nbsp;&raquo;&nbsp;字段管理','');</script>
<script type="text/javascript" src="javascript/jquery.ui.core.js"></script>
<script type="text/javascript" src="javascript/jquery.sortable.js"></script>
<style type="text/css">
.tbody-selected {background:#F2F9FD;border:1px solid #A6C9D7;height:22px;}
.hand{cursor: hand; cursor: pointer;}
</style>
<div class="container" id="cpcontainer">
  <div class="itemtitle">
    <ul class="tab1" id="submenu">
      <li<?php if($operation=="field"){?> class="current"<?php }?>><a href="<?=__SELF__?>?do=model&operation=field&mid=<?=$id?>"><span>{<?=$rs['name']?>}字段管理</span></a></li>
      <li<?php if($operation=="editfield"){?> class="current"<?php }?>><a href="<?=__SELF__?>?do=model&operation=editfield&mid=<?=$id?>"><span>编辑模型字段</span></a></li>
    </ul>
  </div>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>排序:表单的显示顺序</li>
          <li>字段排序可拖拉</li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=model&operation=post" method="post">
    <input type="hidden" name="action" value="order" />
    <input type="hidden" name="id" value="<?=$id?>" />
    <table class="tb tb2 ">
      <tr>
        <th>排序</th>
        <th>字段名</th>
        <th>字段</th>
        <th>字段类型</th>
        <th>管理</th>
      </tr><tbody id="sortable">
      <?php for($i=0;$i<$_count;$i++){
      	  $Finfo=$field[$fArray[$i]][$id]?$field[$fArray[$i]][$id]:$field[$fArray[$i]][0];
      	  if($Finfo){
    ?>
      <tr id="field.<?=$fArray[$i]?>">
        <td class='hand'><input type="hidden" name="order[]" value="<?=$fArray[$i]?>" /><?=$i+1?></td>
        <td class='hand'><?=$Finfo['name']?></td>
        <td class='hand'><?=$fArray[$i]?></td>
        <td class='hand'><?=$Finfo['typeText']?></td>
        <td><?php if(in_array($fArray[$i],$SystemField)&& $Finfo['mid']=="0"){?>系统默认字段 禁止编辑<?php }else{?>
          <a href="<?=__SELF__?>?do=field&operation=edit&fid=<?=$Finfo['id']?>">修改</a> | <a href="<?=__SELF__?>?do=model&operation=delfield&mid=<?=$id?>&field=<?=$fArray[$i]?>"  onclick='return confirm("确定要从该模型中删除该字段?\n删除字段会删除该字段的数据.");'>删除</a> | <?php if(in_array($fArray[$i],$index)){?><a href="<?=__SELF__?>?do=model&operation=delindex&mid=<?=$id?>&field=<?=$fArray[$i]?>">取消索引</a><?php }else{?><a href="<?=__SELF__?>?do=model&operation=addindex&mid=<?=$id?>&field=<?=$fArray[$i]?>">设为索引</a><?php }}?></td>
      </tr>
      <?php }else{?>
        <tr id="field.<?=$fArray[$i]?>"><td colspan="5">[<?=$fArray[$i]?>]字段未定义 , <a href="<?=__SELF__?>?do=field&operation=edit&mid=<?=$id?>&name=<?=$fArray[$i]?>">添加</a></td></tr>
        <?php }}?>
        </tbody>
      <tr class="nobg">
        <td colspan="5"><div class="fixsel"> <input type="submit" class="btn" name="forumlinksubmit" value="提交"/> </div></td>
      </tr>
    </table>
  </form>
</div>

<script type="text/JavaScript">
$(function(){
	$("tbody").sortable({ placeholder: 'tbody-selected'}).disableSelection();
}); 
</script>
</body></html>