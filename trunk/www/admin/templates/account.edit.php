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
  <h3>管理员管理</h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>点击ID可查看该管理员</li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=account&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <input type="hidden" name="uid" value="<?=$rs->uid?>" />
    <table class="tb tb2 ">
      <tr>
        <th colspan="2">个人资料</th>
      </tr>
      <tr class="nobg">
        <td colspan="2">用户名:</td>
      </tr>
      <tr>
        <td><input name="name" type="text" id="name" value="<?=$rs->username?>" <?php if($rs->uid) { ?>readonly="true"<?php }?> class="txt"/></td>
        <td></td>
      </tr>
        <tr class="nobg">
        <td colspan="2"><?php if($rs->uid) { ?>新<?php }?>密码:</td>
      </tr>
     <tr>
        <td><input name="pwd" type="password" id="pwd" class="txt"/> </td>
        <td>不更改请留空</td>
      </tr>
       <tr class="nobg">
        <td colspan="2">确认密码:</td>
      </tr>
      <tr>
        <td><input name="pwd2" type="password" id="pwd2" class="txt"/> </td>
        <td>不更改请留空</td>
      </tr>
       <tr class="nobg">
        <td colspan="2">管理组:</td>
      </tr>
      <tr>
        <td><select name="groupid" id="groupid" style="width:auto;"><option value='0'>==无==</option><?=$group->select($rs->groupid,'a')?></select></td>
        <td>请选择管理组</td>
      </tr>
      	  
      <?php if($rs->uid) { ?>
      <tr class="nobg">
        <td colspan="2">最后登陆IP:</td>
      </tr>
      <tr>
        <td><input type="text" disabled class="txt" value="<?=$rs->lastip?>" readonly="true"/></td>
        <td></td>
      </tr>
      <tr class="nobg">
        <td colspan="2">最后登陆时间:</td>
      </tr>
      <tr>
        <td><input type="text" disabled class="txt" value="<?=get_date($rs->lastlogintime,"Y-m-d H:i:s")?>" readonly="true"/></td>
        <td></td>
      </tr>
      <?php }?>
      <tr class="nobg">
        <td colspan="2">昵称</td>
      </tr>
      <tr>
        <td><input name="name" type="text" class="txt" value="<?=$rs->name?>"/></td>
        <td>用于编辑名</td>
      </tr>
      <tr class="nobg">
        <td colspan="2">E-mail</td>
      </tr>
      <tr>
        <td><input name="email" type="text" class="txt" value="<?=$rs->email?>"/></td>
        <td></td>
      </tr>
     <tr class="nobg">
        <td colspan="2"><div class="fixsel"> <input type="submit" class="btn" value="提交"  /> </div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>