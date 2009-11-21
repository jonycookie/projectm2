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
  <script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;栏目&nbsp;&raquo;&nbsp;<?=empty($rs['id'])?'新建':'编辑';?>栏目','<a href="<?=__SELF__?>?action=misc&operation=custommenu&do=add&title=cplog_forums_edit&url=action%3Dforums%26operation%3Dedit%26fid%3D3" target="main"><img src="admin/images/btn_add2menu.gif" title="添加常用操作" width="19" height="18" /></a>');</script>
  <div class="itemtitle">
    <h3>
      <?=empty($rs['id'])?'新建栏目':'编辑栏目 - '.$rs['name'];?>
    </h3>
    <ul class="tab1" id="submenu">
      <li id="nav_basic" class="current"><a href="javascript:void(0)"><span>基本设置</span></a></li>
    </ul>
  </div>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>以下设置没有继承性，即仅对当前栏目有效，不会对下级子栏目产生影响。</li>
        </ul></td>
    </tr>
  </table>
  <form name="cpform" method="post" action="<?=__SELF__?>?do=catalog&operation=post" id="cpform" >
    <input name="cid" type="hidden" value="<?=$rs['id'] ?>" />
    <input name="action" type="hidden" id="action" value="save" />
    <div id="basic">
      <table class="tb tb2 nobdb">
        <tr>
          <th colspan="15" class="partition">基本设置</th>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">栏目名称:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="name" id="name" value="<?=$rs['name'] ?>" type="text" class="txt"  /></td>
          <td class="vtop tips2">栏目名称</td>
        </tr>
    	<tr>
          <td colspan="2" class="td27">所属模型:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><select name="mid" id="mid">
    		<option value="0"<?php if($rs['mid']=='0') echo ' selected="selected"'?>>通用模型</option>
		    <?=getModelselect($rs['mid'])?>
          </select>
           </td>
          <td class="vtop tips2">本栏目的上级栏目或分类</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27"><?=$iCMS->config['ishtm']?'文件保存目录':'栏目别名'?>:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="dir" type="text" id="dir" value="<?=$rs['dir']?>" size="36"/>
            <input name="pinyin" type="checkbox" id="pinyin" value="1" class="checkbox"/>
            拼音 </td>
          <td class="vtop tips2"><?=$iCMS->config['ishtm']?'本栏目生成静态文件保存目录':'为空时程序将自动以栏目名称拼音填充'?> </td>
        </tr>
        <tr class="nobg">
        <tr class="nobg">
          <td colspan="2" class="td27">栏目域名:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="domain" id="domain" value="<?=$rs['domain'] ?>" type="text" class="txt"  /></td>
          <td class="vtop tips2"><?=$iCMS->config['ishtm']?'请手动绑定域名到文件保存目录':'栏目域名'?></td>
        </tr>
          <td colspan="2" class="td27">上级栏目:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><?php if($Admin->CP($rootid) || empty($rootid)){ ?><select name="rootid">
              <option value="0">===无[顶级栏目]===</option>
              <?=$catalog->select($rootid,0,1,'channel&list&page','all') ?>
            </select><?php }else{?>
            <input name="rootid" id="rootid" type="hidden" value="<?=$rootid?>" />
            <input readonly="true" value="<?=$catalog->catalog[$rootid]['name']?>" type="text" class="txt" />
           <?php }?>
           </td>
          <td class="vtop tips2">本栏目的上级栏目或分类</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">显示栏目:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li<?php if($rs['ishidden']=='0') echo ' class="checked"'?>>
                <input class="radio" type="radio" name="ishidden" value="0" <?php if($rs['ishidden']=='0') echo 'checked'?>>
                是</li>
              <li<?php if($rs['ishidden']=='1') echo ' class="checked"'?>>
                <input class="radio" type="radio" name="ishidden" value="1" <?php if($rs['ishidden']=='1') echo 'checked'?>>
                否</li>
            </ul></td>
          <td class="vtop tips2">选择“否”将暂时将栏目隐藏不显示，但栏目内容仍将保留，且用户仍可通过直接提供的 URL 访问到此栏目</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">支持投稿:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li<?php if($rs['issend']=='1') echo ' class="checked"'?>>
                <input class="radio" type="radio" name="issend" value="1" <?php if($rs['issend']=='1') echo 'checked'?>>
                是</li>
              <li<?php if($rs['issend']=='0') echo ' class="checked"'?>>
                <input class="radio" type="radio" name="issend" value="0" <?php if($rs['issend']=='0') echo 'checked'?>>
                否</li>
            </ul></td>
          <td class="vtop tips2">选择“否”该栏目将不允许用户发布内容</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">审核投稿:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li<?php if($rs['isexamine']=='1') echo ' class="checked"'?>>
                <input class="radio" type="radio" name="isexamine" value="1" <?php if($rs['isexamine']=='1') echo 'checked'?>>
                是</li>
              <li<?php if($rs['isexamine']=='0') echo ' class="checked"'?>>
                <input class="radio" type="radio" name="isexamine" value="0" <?php if($rs['isexamine']=='0') echo 'checked'?>>
                否</li>
            </ul></td>
          <td class="vtop tips2">选择“否”用户发布的内容将不用通过管理员审核,直接显示</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">访问密码:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="password" value="<?=$rs['password']?>" type="text" class="txt"  /></td>
          <td class="vtop tips2">当您设置密码后，用户必须输入密码才可以访问到此栏目</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">栏目转向 URL:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="url" value="<?=$rs['url']?>" type="text" class="txt"  /></td>
          <td class="vtop tips2">如果设置转向 URL(例如 http://www.idreamsoft.cn)，用户点击本分栏目将进入转向中设置的 URL。一旦设定将无法进入栏目页面，请确认是否需要使用此功能，留空为不设置转向 URL</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">栏目图标:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="icon" value="<?=$rs['icon']?>" type="text" class="txt" id="icon" /><img src="admin/images/selectfile.gif" width="67" height="19" align="absmiddle" onclick="showDialog('<?=__SELF__?>?do=dialog&operation=file&hit=file&type=gif,jpg,png,bmp,jpeg','icon');"/></td>
          <td class="vtop tips2">栏目图标，可填写相对或绝对地址。</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">栏目关键词:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><textarea  rows="6" onkeyup="textareasize(this)" name="keywords" id="keywords" cols="50" class="tarea"><?=$rs['keywords']?></textarea></td>
          <td class="vtop tips2">此关键词用于搜索引擎优化，放在 meta 的 keyword 标签中，多个关键字间请用半角逗号 "," 隔开</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">栏目简介:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><textarea  rows="6" onkeyup="textareasize(this)" name="description" id="description" cols="50" class="tarea"><?=$rs['description']?></textarea></td>
          <td class="vtop tips2">对本栏目的简短描述</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">栏目排列顺序:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="order" id="order" value="<?php echo $rs['order'] ?>" type="text" class="txt"  /></td>
          <td class="vtop tips2">栏目的显示顺序</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">栏目属性:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li<?php if($rs['attr']=='list') echo ' class="checked"'?>>
                <input class="radio" type="radio" value="list" name="attr" <?php if ($rs['attr']=='list') echo ' checked="checked"'?> onclick="ctpl('list');"/>
                最终列表栏目</li>
              <li<?php if($rs['attr']=='channel') echo ' class="checked"'?>>
                <input class="radio" type="radio" value="channel" name="attr" <?php if ($rs['attr']=='channel') echo ' checked="checked"'?> onclick="ctpl('channel');"/>
                频道封面</li>
              <li<?php if($rs['attr']=='page') echo ' class="checked"'?>>
                <input class="radio" type="radio" value="page" name="attr" <?php if ($rs['attr']=='page') echo ' checked="checked"'?> onclick="ctpl('page');"/>
                独立页面</li>
            </ul></td>
          <td class="vtop tips2">最终列表栏目（允许在本栏目发布文档，并生成文档列表） <br />
            频道封面（栏目本身不允许发布文档） <br />
            独立页面（栏目本身不允许发布文档）</td>
        </tr>
        <tbody id="channel" style="display:none" class="selectpl">
          <tr class="nobg">
            <td colspan="2" class="td27">频道封面模板:</td>
          </tr>
          <tr>
            <td class="vtop rowform"><input name="tpl[channel]" type="text" id="channeltpl" value="<?=$rs['tpl_index']?$rs['tpl_index']:'{TPL}/channel.htm'?>" size="40" style="width:200px;"/>
              <img src="admin/images/selecttpl.gif" width="67" height="19" align="absmiddle" onclick="showDialog('<?=__SELF__?>?do=dialog&operation=template&hit=file&type=htm','channeltpl');"/></td>
            <td class="vtop tips2">设置本栏目模板 <br />
              {TPL}网站默认模板</td>
          </tr>
        </tbody>
        <tbody id="list" style="display:none" class="selectpl">
          <tr class="nobg">
            <td colspan="2" class="td27">列表栏目模板:</td>
          </tr>
          <tr>
            <td class="vtop rowform"><input name="tpl[list]" type="text" id="listpl" value="<?=$rs['tpl_list']?$rs['tpl_list']:'{TPL}/list.htm'?>" size="40" style="width:200px;"/>
              <img src="admin/images/selecttpl.gif" width="67" height="19" align="absmiddle" onclick="showDialog('<?=__SELF__?>?do=dialog&operation=template&hit=file&type=htm','listpl');"/></td>
            <td class="vtop tips2">设置本栏目模板 <br />
              {TPL}网站默认模板</td>
          </tr>
          <tr class="nobg">
            <td colspan="2" class="td27">栏目内容模板:</td>
          </tr>
          <tr>
            <td class="vtop rowform"><input name="tpl[content]" type="text" id="contentpl" value="<?=$rs['tpl_contents']?$rs['tpl_contents']:'{TPL}/show.htm'?>" size="40" style="width:200px;"/>
              <img src="admin/images/selecttpl.gif" width="67" height="19" align="absmiddle" onclick="showDialog('<?=__SELF__?>?do=dialog&operation=template&hit=file&type=htm','contentpl');"/></td>
            <td class="vtop tips2">设置本栏目模板 <br />
              {TPL}网站默认模板</td>
          </tr>
        </tbody>
        <tbody id="page" style="display:none" class="selectpl">
          <tr class="nobg">
            <td colspan="2" class="td27">独立页面模板:</td>
          </tr>
          <tr>
            <td class="vtop rowform"><input name="tpl[page]" type="text" id="pagetpl" value="<?=$rs['tpl_index']?$rs['tpl_index']:'{TPL}/page.htm'?>" size="40" style="width:200px;"/>
              <img src="admin/images/selecttpl.gif" width="67" height="19" align="absmiddle" onclick="showDialog('<?=__SELF__?>?do=dialog&operation=template&hit=file&type=htm','pagetpl');"/></td>
            <td class="vtop tips2">设置本栏目模板 <br />
              {TPL}网站默认模板</td>
          </tr>
        </tbody>
      </table>
    </div>
    <table class="tb tb2 nobdt">
      <tr class="nobg">
        <td colspan="15"><div class="fixsel">
            <input type="submit" class="btn" name="detailsubmit" value="提交"  />
          </div></td>
      </tr>
    </table>
  </form>
</div>
<script language="JavaScript" type="text/javascript">
ctpl('<?=$rs['attr']?>');
function ctpl(v){
	$(".selectpl").hide();
	$("#"+v).show();
}
$(function(){
	$("#pinyin").click(function(){
		$("#dir").toggle(); 
	}); 
	$("#cata").submit(function(){
		if($("#name").val()==''){
			alert("栏目名不能为空!");
			$("#name").focus();
			return false;
		}
	}); 
	$("#mid").change( function() {
	  	if(this.value>0){
	  		$("#listpl").val('{TPL}/list.content.htm');
	  		$("#contentpl").val('{TPL}/show.content.htm');
	  	}
	}); 
});
</script>
</body></html>