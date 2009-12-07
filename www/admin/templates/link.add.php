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
<div class="container" id="cpcontainer">
  <script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;友情链接&nbsp;&raquo;&nbsp;添加友情链接','<a href="<?=__SELF__?>?action=misc&operation=custommenu&do=add&title=cplog_misc_forumlinks&url=action%3Dmisc%26operation%3Dforumlinks" target="main"><img src="admin/images/btn_add2menu.gif" title="添加常用操作" width="19" height="18" /></a>');</script>
  <div class="itemtitle">
    <h3>友情链接</h3>
    <ul class="tab1" id="submenu">
      <li id="nav_manage"><a href="<?=__SELF__?>?do=link"><span>管理</span></a></li>
      <li id="nav_add" class="current"><a href="<?=__SELF__?>?do=link&operation=add"><span>添加友情链接</span></a></li>
    </ul>
  </div>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>未填写文字说明的项目将以紧凑型显示。</li>
        </ul></td>
    </tr>
  </table>
  <form name="cpform" method="post" action="<?=__SELF__?>?do=link&operation=post" id="cpform" >
    <input type="hidden" name="action" value="add" />
    <table class="tb tb2 ">
      <tr>
        <th>显示顺序</th>
        <th>网站名称</th>
        <th>网站 URL</th>
        <th>文字说明</th>
        <th>logo 地址(可选)</th>
      </tr>
      <tr>
        <td class="td25"><input name="displayorder" type="text" class="txt" value="0" size="3" /></td>
        <td><input type="text" class="txt" name="name" size="15" /></td>
        <td><input type="text" class="txt" name="url" size="20" /></td>
        <td class="td26"><input type="text" class="txt" name="description" size="30" /></td>
        <td><input type="text" class="txt" name="logo" size="20" /></td>
      </tr>
      <tr class="nobg">
        <td colspan="15" class="td25"><div class="fixsel">
            <input type="submit" class="btn" name="forumlinksubmit" value="提交"  />
        </div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>