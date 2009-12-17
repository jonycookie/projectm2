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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;HTML更新&nbsp;&raquo;&nbsp更新TAG','');</script>
<div class="container" id="cpcontainer">
  <h3>更新TAG</h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>请选择下列其中一种方式，如不采用请留空</li>
          <li>下列方式优先权：按分类>按TAG ID>按日期</li>
          <li>结束时间为空时则使用当前时间为结束时间</li>
          <li>归类/日期 方式TAG ID 将缓存24小时</li>
        </ul></td>
    </tr>
  </table>
  <form name="cpform" method="get" action="<?=__SELF__?>" id="cpform" >
    <input type="hidden" name="do" value="html" />
    <input type="hidden" name="operation" value="create" />
    <input type="hidden" name="action" value="tag" />
    <table class="tb tb2 ">
      <tr>
        <td width="120">按归类:</td>
        <td><select name="sortid[]" id="sortid" multiple="multiple" size="15">
          <option value='all'>所 有 归 类</option>
    	  <optgroup label="======================================"></optgroup>
    	  <?=tagsort()?>
        </select></td>
      </tr>
      <tr>
        <td>按TAG ID：</td>
        <td>开始ID：<input name="startid" type="text" id="startid" class="txt" style="width:80px" /><br />-<br />结束ID：<input name="endid" type="text" id="endid" class="txt" style="width:80px" /></td>
      </tr>
      <tr>
        <td>按日期：</td>
        <td>开始时间：<input type="text" class="txt" name="starttime" value="" onclick="showcalendar(event, this)" style="width:120px"><br />-<br />结束时间：<input type="text" class="txt" name="endtime" value="" onclick="showcalendar(event, this)" style="width:120px"></td>
      </tr><!--
       <tr>
        <td width="120">指定生成页数:</td>
        <td><input type="text" class="txt" name="cpn" value="" style="width:120px"></td>
      </tr>
       <tr>
        <td width="120">间隔时间(s):</td>
        <td><input type="text" class="txt" name="time" value="1" style="width:120px"></td>
      </tr>-->
      <tr>
        <td colspan="2" class="td21"><input type="submit" class="btn" name="cleanupsubmit" value="提交" /></td>
      </tr>
    </table>
  </form>
</div>
</body></html>