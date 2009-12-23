<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
iCMS_admincp_head();
?><div class="container" id="cpcontainer">
  <script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;友情链接','<a href="<?=__SELF__?>?action=misc&operation=custommenu&do=add&title=cplog_misc_forumlinks&url=action%3Dmisc%26operation%3Dforumlinks" target="main"><img src="admin/images/btn_add2menu.gif" title="添加常用操作" width="19" height="18" /></a>');</script>
  <div class="itemtitle">
    <h3>友情链接</h3>
    <ul class="tab1" id="submenu">
      <li id="nav_manage" class="current"><a href="<?=__SELF__?>?do=link"><span>管理</span></a></li>
      <li id="nav_add"><a href="<?=__SELF__?>?do=link&operation=add"><span>添加友情链接</span></a></li>
    </ul>
  </div>
  <form name="cpform" method="post" action="<?=__SELF__?>?do=link&operation=post" id="cpform" >
    <input type="hidden" name="action" value="edit" />
    <table class="tb tb2 ">
      <tr>
        <th></th>
        <th>排序</th>
        <th>网站名称</th>
        <th>网站 URL</th>
        <th>文字说明</th>
        <th>logo 地址(可选)</th>
      </tr>
      <?php for($i=0;$i<$_count;$i++){?>
      <tr>
        <td class="td25"><input type="checkbox" class="checkbox" name="delete[]" value="<?=$rs[$i]['id']?>" /></td>
        <td class="td28"><input type="text" class="txt" name="displayorder[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['orderid']?>" size="3" /></td>
        <td><input type="text" class="txt" name="name[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['name']?>" size="15" /></td>
        <td><input type="text" class="txt" name="url[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['url']?>" size="20" /></td>
        <td class="td26"><input type="text" class="txt" name="description[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['desc']?>" size="30" /></td>
        <td><input type="text" class="txt" name="logo[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['logo']?>" size="20" /></td>
      </tr>
      <?php }?>
      <tr>
        <td height="22" colspan="6" align="right"><?=$pagenav?></td>
      </tr>
      <tr class="nobg">
        <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'delete')" />
          <label for="chkall">删?</label></td>
        <td colspan="15"><div class="fixsel">
            <input type="submit" class="btn" name="forumlinksubmit" value="提交"  />
          </div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>