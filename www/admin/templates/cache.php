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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;更新缓存','');</script>
<div class="container" id="cpcontainer">
  <h3>更新系统统计</h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>清除模板缓存：影响清理后第一次访问速度，建议在未对模板进行修改时请勿进行清除模板缓存操作</li>
        </ul></td>
    </tr>
  </table>
  <form name="cpform" method="post" action="<?=__SELF__?>?do=cache&operation=post" id="cpform" >
    <input type="hidden" name="action" value="cache" />
    <table class="tb tb2 ">
      <tr>
        <th width="229"></th>
        <th width="714"></th>
      </tr>
      <tr>
        <td class="td21"><input name="config" type="checkbox" class="checkbox" id="config" value="1" />
        更新系统配置</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="td21"><input name="catalog" type="checkbox" class="checkbox" id="catalog" value="1" />
        重建栏目缓存</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="td21"><input name="model" type="checkbox" class="checkbox" id="model" value="1" />
        更新模型缓存</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="td21"><input name="field" type="checkbox" class="checkbox" id="field" value="1" />
        更新字段缓存</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="td21"><input name="keywords" type="checkbox" class="checkbox" id="keywords" value="1" />
        重建关键字缓存</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="td21"><input name="tags" type="checkbox" class="checkbox" id="tags" value="1" />
        重建标签缓存</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="td21"><input name="Re-Statistics" type="checkbox" class="checkbox" id="Re-Statistics" value="1" />
        栏目文章数重新统计</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="td21"><input name="tpl" type="checkbox" class="checkbox" id="tpl" value="1" />
        清除模板缓存</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td class="td21"><input type="submit" class="btn" name="cleanupsubmit" value="提交" /></td>
        <td>&nbsp;</td>
      </tr>
    </table>
  </form>
</div>
</body></html>