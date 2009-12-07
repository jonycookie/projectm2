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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;HTML更新&nbsp;&raquo;&nbsp更新栏目','');</script>
<div class="container" id="cpcontainer">
  <h3>更新栏目</h3>
  <form name="cpform" method="get" action="<?=__SELF__?>" id="cpform" >
    <input type="hidden" name="do" value="html" />
    <input type="hidden" name="operation" value="create" />
    <input type="hidden" name="action" value="catalog" />
    <table class="tb tb2 ">
      <tr>
        <td class="vtop tips2" width="120">选择栏目:</td>
        <td class="vtop rowform"><select name="cid[]" id="cid" multiple="multiple" size="15">
          <option value='all'>所 有 栏 目</option>
    	  <optgroup label="======================================"></optgroup>
          <?=$catalog->select(0,0,1,'channel&list','all')?>
        </select></td>
      </tr>
       <tr>
        <td class="vtop tips2" width="120">指定生成页数:</td>
        <td class="vtop rowform"><input type="text" class="txt" name="cpn" value="" style="width:120px"></td>
      </tr>
       <tr>
        <td class="vtop tips2" width="120">间隔时间(s):</td>
        <td class="vtop rowform"><input type="text" class="txt" name="time" value="1" style="width:120px"></td>
      </tr>
     <tr>
        <td colspan="2" class="td21"><input type="submit" class="btn" name="cleanupsubmit" value="提交" /></td>
      </tr>
    </table>
  </form>
</div>
</body></html>