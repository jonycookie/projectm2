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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;HTML更新&nbsp;&raquo;&nbsp更新首页','');</script>
<?php if(isset($_GET['all'])){?>
<script type="text/JavaScript">$(function(){$("#cpform").submit();})</script>
<?php }?>
<div class="container" id="cpcontainer">
  <h3>更新首页</h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>如果看不到左侧菜单,请刷新网页!  注:按F5或者刷新本页无效</li>
        </ul></td>
    </tr>
  </table>  <form name="cpform" method="get" action="<?=__SELF__?>" id="cpform" >
    <input type="hidden" name="do" value="html" />
    <input type="hidden" name="operation" value="create" />
    <input type="hidden" name="action" value="index" />
<?php if(isset($_GET['all'])){?>
	<input type="hidden" name="all" value="" />
<?php }?>
    <table class="tb tb2 ">
      <tr>
        <td class="vtop tips2">选择主页模板:</td>
        <td class="vtop rowform"><input name="indexTPL" type="text" id="indexTPL" value="<?=$iCMS->config['indexTPL']?>" class="txt"/></td>
        <td class="vtop tips2"><img src="admin/images/selecttpl.gif" width="67" height="19" align="absmiddle" onclick="showDialog('<?=__SELF__?>?do=dialog&operation=template&hit=file&type=htm','indexTPL');"/></td>
      </tr>
      <tr>
        <td class="vtop tips2">首页文件名:</td>
        <td class="vtop rowform"><input name="indexname" type="text" id="indexname" value="<?=$iCMS->config['indexname']?>" class="txt"/><?=$iCMS->config['htmlext']?></td>
        <td class="vtop tips2"></td>
      </tr>
      <tr>
        <td colspan="3" class="td21"><input type="submit" class="btn" name="cleanupsubmit" value="提交" /></td>
      </tr>
    </table>
  </form>
</div>
</body></html>