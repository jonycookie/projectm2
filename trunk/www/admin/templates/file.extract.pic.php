<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
iCMS_admincp_head();
?><div id="append_parent"></div>
<script type="text/javascript" src="javascript/calendar.js"></script>
<link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;HTML更新&nbsp;&raquo;&nbsp更新文章','');</script>
<div class="container" id="cpcontainer">
  <h3>提取文章缩略图</h3>
  <form name="cpform" method="get" action="<?=__SELF__?>" id="cpform" >
    <input type="hidden" name="do" value="file" />
    <input type="hidden" name="operation" value="extract" />
    <input type="hidden" name="o" value="1" />
    <table class="tb tb2 ">
      <tr>
        <td width="120">按栏目:</td>
        <td><select name="cid[]" id="cid" multiple="multiple" size="15">
          <option value='all'>所 有 栏 目</option>
    	  <optgroup label="======================================"></optgroup>
          <?=$catalog->select(0,0,1,'channel&list','all')?>
        </select></td>
      </tr>
      <tr>
        <td>按文章ID：</td>
        <td>开始ID：<input name="startid" type="text" id="startid" class="txt" style="width:80px" /><br />-<br />结束ID：<input name="endid" type="text" id="endid" class="txt" style="width:80px" /></td>
      </tr>
      <tr>
        <td>按日期：</td>
        <td>开始时间：<input type="text" class="txt" name="starttime" value="" onclick="showcalendar(event, this)" style="width:120px"><br />-<br />结束时间：<input type="text" class="txt" name="endtime" value="" onclick="showcalendar(event, this)" style="width:120px"></td>
      </tr>
      <tr>
        <td>操作：</td>
        <td>    	<input name="action" class="radio" type="radio" value="thumb" />提取缩略图  <input name="action" class="radio" type="radio" value="into" />图片入库</td>
      </tr>
      <tr>
        <td colspan="2" class="td21"><input type="submit" class="btn" name="cleanupsubmit" value="开始" /></td>
      </tr>
    </table>
  </form>
</div>
</body></html>