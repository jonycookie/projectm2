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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;字段管理','');</script>
<div class="container" id="cpcontainer">
  <div class="itemtitle">
    <h3>字段管理</h3>
    <ul class="tab1" id="submenu">
      <li><a href="<?=__SELF__?>?do=field&operation=edit"><span>新增字段</span></a></li>
    </ul>
  </div>
  <form action="<?=__SELF__?>?do=field&operation=post" method="post">
    <table class="tb tb2 ">
      <tr>
        <th style="width:100px;">字段名</th>
        <th style="width:120px;">字段</th>
        <th style="width:120px;">字段类型</th>
        <th style="width:120px;">所属模型</th>
        <th style="width:60px;">是否验证</th>
        <th style="width:60px;">是否隐藏字段</th>
        <th>管理</th>
      </tr>
      <?php for($i=0;$i<$_count;$i++){
    ?>
      <tr>
        <td><?=$rs[$i]['name']?></td>
        <td><?=$rs[$i]['field']?></td>
        <td><?=getFieldType($rs[$i]['type'])?></td>
        <td><?=$rs[$i]['mid']=="0"?'通用模型':$model[$rs[$i]['mid']]['name']?></td>
        <td><?=getFieldvalidate($rs[$i]['validate'])?></td>
        <td><?=$rs[$i]['hidden']=="1" ? "隐藏字段":'显示'?></td>
        <td><?php if(in_array($rs[$i]['field'],$SystemField)&& $rs[$i]['mid']=="0"){?>系统默认字段 禁止编辑<?php }else{?>
          <a href="<?=__SELF__?>?do=field&operation=edit&fid=<?=$rs[$i]['id']?>">修改</a> | <a href="<?=__SELF__?>?do=field&operation=del&fid=<?=$rs[$i]['id']?>"  onclick='return confirm("确定要删除?\n删除字段会删除所有使用该字段的数据.");'>删除</a> <?php }?></td>
      </tr>
      <?php }?>
    </table>
  </form>
</div>
</body></html>