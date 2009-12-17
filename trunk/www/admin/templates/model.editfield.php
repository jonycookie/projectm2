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
.tbody-selected {
	background:#F2F9FD;
	border:1px solid #A6C9D7;
	height:22px;
}
.hand {
	cursor: hand;
	cursor: pointer;
}
.tr-state-disabled td{
	opacity:0.35;
	color:#9D9D9D;
}
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
          <li>字段可拖拉</li>
          <li>灰色字段为系统默认字段,要更改排序请到字段管理</li>
        </ul></td>
    </tr>
  </table>
  <table class="tb tb2 ">
    <tr>
      <th class="partition" style="width:50%">{
        <?=$rs['name']?>
        }字段</th>
      <th style="width:2px;background:#F2F9FD;border:1px solid #A6C9D7;border-top:none;"></th>
      <th class="partition" style="width:auto;">所有已定义的字段</th>
    </tr>
    <tr>
      <td valign="top"><form action="<?=__SELF__?>?do=model&operation=post" method="post" id="editfield">
          <input type="hidden" name="action" value="editfield" />
          <input type="hidden" name="id" value="<?=$id?>" />
          <table class="tb tb2">
            <tr>
              <th>排序</th>
              <th>字段名</th>
              <th>字段</th>
            </tr>
            <tbody id="modelfield">
              <?php for($i=0;$i<$_count;$i++){
      	  $Finfo=$field[$fArray[$i]][$id]?$field[$fArray[$i]][$id]:$field[$fArray[$i]][0];
      	  if($Finfo){
    ?>
              <tr id="field.<?=$fArray[$i]?>" class="<?php if(in_array($fArray[$i],$SystemField)){?>tr-state-disabled<?php }else{?>hand<?php }?>">
                <td><input type="hidden" name="order[]" value="<?=$fArray[$i]?>" /><?=$i+1?></td>
                <td><?=$Finfo['name']?></td>
                <td><?=$fArray[$i]?></td>
              </tr>
              <?php }else{?>
              <tr id="field.<?=$fArray[$i]?>">
                <td colspan="3">[
                  <?=$fArray[$i]?>
                  ]字段未定义 , <a href="<?=__SELF__?>?do=field&operation=edit&mid=<?=$id?>&name=<?=$fArray[$i]?>">添加</a></td>
              </tr>
              <?php }}?>
              <tr><td colspan="3">&nbsp;</td></tr> 
            </tbody>
          </table>
        </form></td>
      <td style='background:#F2F9FD;border:1px solid #A6C9D7;border-top:none;'></td>
      <td valign="top" style="padding-left:5px;"><table class="tb tb2 ">
          <tr>
            <th style="width:120px;">排序</th>
            <th style="width:120px;">字段名</th>
            <th style="width:120px;">字段</th>
            <th style="width:120px;">字段类型</th>
            <th style="width:120px;">所属模型</th>
            <th style="width:120px;">是否验证</th>
          </tr>
          <tbody id="AllField">
            <?php for($i=0;$i<$_fcount;$i++){
              	  if(!in_array($fRs[$i]['field'],$fArray)&&!in_array($fRs[$i]['field'],array('visible','comments','digg','hits'))){
              	  	  $fRs[$i]['rules']=unserialize($fRs[$i]['rules']);
              	  	  $len	= $fRs[$i]['type']=="number"?$fRs[$i]['rules']['maxnum']:$fRs[$i]['rules']['maxlength'];
    ?><?php /*
               <tr id="field.<?=$fRs[$i]['field']?>">
                <td class='hand'><input type="hidden" name="order[]" value="<?=$fRs[$i]['field']?>" />
                  <?=$i+1?></td>
                <td class='hand'><?=$fRs[$i]['name']?></td>
                <td class='hand'><?=$fRs[$i]['field']?></td>
              </tr> */ ?>
           <tr class='hand'>
              <td><input type="hidden" name="order[]" value="<?=$fRs[$i]['field']?>" />
               <input type="hidden" name="type[<?=$fRs[$i]['field']?>][<?=$fRs[$i]['mid']?>]" value="<?=$fRs[$i]['type']?>" />
               <input type="hidden" name="len[<?=$fRs[$i]['field']?>][<?=$fRs[$i]['mid']?>]" value="<?=$len?>" />
               <input type="hidden" name="default[<?=$fRs[$i]['field']?>][<?=$fRs[$i]['mid']?>]" value="<?=$fRs[$i]['default']?>" />
                 <?=$i+1?></td>
              <td><?=$fRs[$i]['name']?></td>
              <td><?=$fRs[$i]['field']?></td>
              <td><?=getFieldType($fRs[$i]['type'])?></td>
              <td><?=$fRs[$i]['mid']=="0"?'通用模型':$model[$fRs[$i]['mid']]['name']?></td>
              <td><?=getFieldvalidate($fRs[$i]['validate'])?></td>
            </tr>
            <?php }}?>
              <tr><td colspan="6">&nbsp;</td></tr> 
          </tbody>
        </table></td>
    </tr>
    <tr class="nobg">
      <td colspan="3"><div class="fixsel">
          <input type="button" class="btn" name="forumlinksubmit" value="提交" onclick="$('#editfield').submit();" />
        </div></td>
    </tr>
  </table>
</div>
<script type="text/JavaScript">
$(function(){
	$("#modelfield,#AllField").sortable({connectWith: 'tbody',cancel: '.tr-state-disabled',placeholder: "tbody-selected", revert: true }).disableSelection();;
//	$("#AllField").sortable({connectWith: 'tbody',placeholder: "tbody-selected", revert: true });
}); 
</script>
</body></html>