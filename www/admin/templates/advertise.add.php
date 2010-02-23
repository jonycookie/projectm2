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
<div class="container" id="cpcontainer">
  <script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;广告管理&nbsp;&raquo;&nbsp;<?=empty($id)?'添加':'修改'?>广告','<a href="<?=__SELF__?>?action=misc&operation=custommenu&do=add&title=cplog_advertisements&url=action%3Dadvertisements" target="main"><img src="admin/images/btn_add2menu.gif" title="添加常用操作" width="19" height="18" /></a>');</script>
  <div class="itemtitle">
    <h3>
      <?=empty($id)?'添加':'修改'?>
      广告</h3>
    <ul class="tab1">
      <li><a href="<?=__SELF__?>?do=advertise"><span>管理</span></a></li>
      <li class="current"><a href="<?=__SELF__?>?do=advertise&operation=add"><span>添加</span></a> </li>
    </ul>
  </div>
  <script type="text/javascript" src="javascript/calendar.js"></script>
  <form name="cpform" method="post" action="<?=__SELF__?>?do=advertise&operation=post" id="cpform" >
    <input type="hidden" name="action" value="add" />
    <input type="hidden" name="id" value="<?=$id?>" />
    <table class="tb tb2 ">
      <tr>
        <th colspan="15" class="partition"><?=empty($id)?'添加':'修改'?>广告</th>
      </tr>
      <tr class="nobg">
        <td colspan="2">广告标识符(必填):</td>
      </tr>
      <tr>
        <td><input name="varname" value="<?=$rs['varname']?>" type="text" class="txt"  /></td>
        <td>模板标签: &lt;!--{iCMS:advertise name="广告标识符"}--&gt;</td>
      </tr>
      <tr class="nobg">
        <td colspan="2">广告状态:</td>
      </tr>
      <tr>
        <td><select name="state" >
        <option value="1"<?php if($rs['state']=="1")echo " selected"?>> 启用 </option>
            <option value="0"<?php if($rs['state']=="0")echo " selected"?>> 关闭</option>
</select></td>
        <td>关闭状态广告将不显示</td>
      </tr>
      <tr class="nobg">
        <td colspan="2">广告起始时间(选填):</td>
      </tr>
      <tr>
        <td><input type="text" class="txt" name="starttime" value="<?=$rs['starttime']?>" onclick="showcalendar(event, this)">        </td>
        <td>设置广告起始生效的时间，格式 yyyy-mm-dd，留空为不限制起始时间</td>
      </tr>
      <tr class="nobg">
        <td colspan="2">广告结束时间(选填):</td>
      </tr>
      <tr>
        <td><input type="text" class="txt" name="endtime" value="<?=$rs['endtime']?>" onclick="showcalendar(event, this)">        </td>
        <td>设置广告展示结束的时间，格式 yyyy-mm-dd，留空为不限制结束时间</td>
      </tr>
      <tr class="nobg">
        <td colspan="2">展现方式:</td>
      </tr>
      <tr>
        <td><select name="style" onchange="var styles, key;styles=new Array('code','text','image','flash'); for(key in styles) {var obj=document.getElementById('style_'+styles[key]); obj.style.display=styles[key]==this.options[this.selectedIndex].value?'':'none';}">
            <option value="code"<?php if($rs['style']=="code")echo " selected"?>> 代码</option>
            <option value="text"<?php if($rs['style']=="text")echo " selected"?>> 文字</option>
            <option value="image"<?php if($rs['style']=="image")echo " selected"?>> 图片</option>
            <option value="flash"<?php if($rs['style']=="flash")echo " selected"?>> Flash</option>
          </select></td>
        <td>请选择所需的广告展现方式</td>
      </tr>
      <tbody id="style_code">
        <tr>
          <th colspan="15" class="partition">HTML 代码</th>
        </tr>
        <tr class="nobg">
          <td colspan="2">广告 HTML 代码:</td>
        </tr>
        <tr>
          <td colspan="2" style=" width:auto;">请直接输入需要展现的广告的 HTML 代码<br /><textarea  rows="6" onkeyup="textareasize(this)" name="adv[code][html]" id="adv[code][html]" cols="50" class="tarea" style="width:520px;"><?=$adv['code']['html']?></textarea></td>
        </tr>
      </tbody>
      <tbody id="style_text" style="display: none">
        <tr>
          <th colspan="15" class="partition">文字广告</th>
        </tr>
        <tr class="nobg">
          <td colspan="2">文字内容(必填):</td>
        </tr>
        <tr>
          <td><input name="adv[text][title]" value="<?=$adv['text']['title']?>" type="text" class="txt"  /></td>
          <td>请输入文字广告的显示内容</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">文字链接(必填):</td>
        </tr>
        <tr>
          <td><input name="adv[text][link]" value="<?=$adv['text']['link']?>" type="text" class="txt"  /></td>
          <td>请输入文字广告指向的 URL 链接地址</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">文字大小(选填):</td>
        </tr>
        <tr>
          <td><input name="adv[text][size]" value="<?=$adv['text']['size']?>" type="text" class="txt"  /></td>
          <td>请输入文字广告的内容显示字体，可使用 pt、px、em 为单位</td>
        </tr>
      </tbody>
      <tbody id="style_image" style="display: none">
        <tr>
          <th colspan="15" class="partition">图片广告</th>
        </tr>
        <tr class="nobg">
          <td colspan="2">图片地址(必填):</td>
        </tr>
        <tr>
          <td><input name="adv[image][url]" value="<?=$adv['image']['url']?>" type="text" class="txt"  /></td>
          <td>请输入图片广告的图片调用地址</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">图片链接(必填):</td>
        </tr>
        <tr>
          <td><input name="adv[image][link]" value="<?=$adv['image']['link']?>" type="text" class="txt"  /></td>
          <td>请输入图片广告指向的 URL 链接地址</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">图片宽度(选填):</td>
        </tr>
        <tr>
          <td><input name="adv[image][width]" value="<?=$adv['image']['width']?>" type="text" class="txt"  /></td>
          <td>请输入图片广告的宽度，单位为像素</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">图片高度(选填):</td>
        </tr>
        <tr>
          <td><input name="adv[image][height]" value="<?=$adv['image']['height']?>" type="text" class="txt"  /></td>
          <td>请输入图片广告的高度，单位为像素</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">图片替换文字(选填):</td>
        </tr>
        <tr>
          <td><input name="adv[image][alt]" value="<?=$adv['image']['alt']?>" type="text" class="txt"  /></td>
          <td>请输入图片广告的鼠标悬停文字信息</td>
        </tr>
      </tbody>
      <tbody id="style_flash" style="display: none">
        <tr>
          <th colspan="15" class="partition">Flash 广告</th>
        </tr>
        <tr class="nobg">
          <td colspan="2">Flash 地址(必填):</td>
        </tr>
        <tr>
          <td><input name="adv[flash][url]" value="<?=$adv['flash']['url']?>" type="text" class="txt"  /></td>
          <td>请输入 Flash 广告的调用地址</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">Flash 宽度(必填):</td>
        </tr>
        <tr>
          <td><input name="adv[flash][width]" value="<?=$adv['flash']['width']?>" type="text" class="txt"  /></td>
          <td>请输入 Flash 广告的宽度，单位为像素</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">Flash 高度(必填):</td>
        </tr>
        <tr>
          <td><input name="adv[flash][height]" value="<?=$adv['flash']['height']?>" type="text" class="txt"  /></td>
          <td>请输入 Flash 广告的高度，单位为像素</td>
        </tr>
      </tbody>
      <tr class="nobg">
        <td colspan="15"><div class="fixsel">
            <input type="submit" class="btn" name="advsubmit" value="提交"  />
          </div></td>
      </tr>
    </table>
  </form>
</div>
<script type="text/javascript">
var styles, key;
styles=new Array('code','text','image','flash'); 
for(key in styles) {
	document.getElementById('style_'+styles[key]).style.display='none'; 
<?="document.getElementById('style_".$rs['style']."').style.display='';"?>	
}
</script>

</body></html>