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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;自定义模型管理','');</script>
<div class="container" id="cpcontainer">
  <div class="itemtitle">
    <h3>自定义模型管理</h3>
    <ul class="tab1" id="submenu">
      <li><a href="<?=__SELF__?>?do=model&operation=edit"><span>新增模型</span></a></li>
    </ul>
  </div>
    <table class="tb tb2 ">
      <tr>
        <th>ID</th>
        <th>模型名称</th>
        <th>模型表</th>
        <th>创建时间</th>
        <th>管理</th>
      </tr>
      <?php for($i=0;$i<$_count;$i++){
    ?>
      <tr>
        <td><?=$rs[$i]['id']?></td>
        <td><?=$rs[$i]['name']?></td>
        <td><?=$rs[$i]['table']?>_content</td>
        <td><?=get_date($rs[$i]['addtime'],"Y-m-d H:i")?></td>
        <td><a href="<?=__SELF__?>?do=content&operation=add&table=<?=$rs[$i]['table']?>&mid=<?=$rs[$i]['id']?>">添加内容</a> |
        <a href="<?=__SELF__?>?do=content&operation=manage&table=<?=$rs[$i]['table']?>&mid=<?=$rs[$i]['id']?>">内容管理</a> |
        <a href="<?=__SELF__?>?do=model&operation=bakup&table=<?=$rs[$i]['table']?>&mid=<?=$rs[$i]['id']?>">备份</a> |
        <a href="<?=__SELF__?>?do=model&operation=repair&table=<?=$rs[$i]['table']?>&mid=<?=$rs[$i]['id']?>">修复</a> | 
        <a href="<?=__SELF__?>?do=model&operation=optimize&table=<?=$rs[$i]['table']?>&mid=<?=$rs[$i]['id']?>">优化</a> | 
        <a href="<?=__SELF__?>?do=model&operation=export&mid=<?=$rs[$i]['id']?>">导出模型</a> |
        <a href="<?=__SELF__?>?do=model&operation=field&mid=<?=$rs[$i]['id']?>">字段管理</a> |
        <a href="<?=__SELF__?>?do=model&operation=edit&mid=<?=$rs[$i]['id']?>">编辑</a> | 
        <a href="<?=__SELF__?>?do=model&operation=truncate&table=<?=$rs[$i]['table']?>"  onclick='return confirm("确定要清空?\n此操作会清空该模型的所有数据.");'>清空</a> | 
        <a href="<?=__SELF__?>?do=model&operation=del&table=<?=$rs[$i]['table']?>&mid=<?=$rs[$i]['id']?>"  onclick='return confirm("确定要删除?\n删除模型会删除该模型的数据.");'>删除</a></td> 
      </tr>
      <?php }?>
      <tr>
        <td colspan="5" align="right"><?=$pagenav?></td>
      </tr>
    </table>
</div>
</body></html>