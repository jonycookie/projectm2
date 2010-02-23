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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;字段管理&nbsp;&raquo;&nbsp;<?=empty($id)?'新增':'编辑'?>字段','');</script>
<script type="text/JavaScript"></script>
<div class="container" id="cpcontainer">
  <h3>
    <?=empty($id)?'新增':'编辑'?>字段</h3>
  <form action="<?=__SELF__?>?do=field&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <input type="hidden" name="id" value="<?=$id?>" />
    <table class="tb tb2 ">
      <tr class="nobg">
        <th colspan="2">名称:</th>
      </tr>
      <tr>
        <td><input name="name" type="text" id="name" value="<?=$name?>" class="txt" style="width:160px;"/> <input name="hidden" type="checkbox" id="hidden" value="1" class="checkbox" />隐藏字段</td>
        <td>隐藏字段 将不会在表单上显示</td>
      </tr>
      <tr class="nobg">
        <td colspan="2">字段:</td>
      </tr>
      <tr>
        <td><input name="field" type="text" id="field" value="<?=$rs['field']?>" class="txt" /></td>
        <td>请以字母开头,留空将按字段名称拼音</td>
      </tr>
      <tr class="nobg">
        <td colspan="2">所属模型:</td>
      </tr>
      <tr>
        <td><select name="mid" id="mid">
    		<option value="0">通用模型</option>
		    <?=getModelselect($mid)?>
          </select>
        </td>
        <td></td>
      </tr>
      <tr class="nobg">
        <td colspan="2">类型:</td>
      </tr>
      <tr>
        <td><select name="type" id="type">
            <option value="number">数字(number)</option>
            <option value="text">字符串(text)</option>
            <option value="radio">单选(radio)</option>
            <option value="checkbox">多选(checkbox)</option>
            <option value="textarea">文本(textarea)</option>
            <option value="editor">编辑器(editor)</option>
            <option value="select">选择(select)</option>
            <option value="multiple">多选选择(multiple)</option>
            <option value="calendar">日历(calendar)</option>
            <option value="email">电子邮件(email)</option>
            <option value="url">超级链接(url)</option>
            <option value="image">图片(image)</option>
            <option value="upload">上传(upload)</option>
          </select>
        </td>
        <td>如果该字段正在使用,修改类型会使用数据丢失</td>
      </tr>
      <tr class="nobg">
        <td colspan="2">默认值:</td>
      </tr>
      <tr>
        <td><input name="default" type="text" value="<?=$rs['default']?>" class="txt" /></td>
        <td></td>
      </tr>
      <tr class="nobg">
        <td colspan="2">是否验证:</td>
      </tr>
      <tr>
        <td><select name="validate" id="validate">
            <option value="N">不验证</option>
            <option value="0">不能为空</option>
            <option value="1">匹配字母</option>
            <option value="2">匹配数字</option>
            <option value="4">Email验证</option>
            <option value="5">url验证</option>
            <option value="preg">自定义正则</option>
          </select><div id="validate_preg" style="display: none;margin-top:10px;">自定义正则:<input name="validate" value="<?=$rs['validate']?>" type="text" class="txt" disabled="disabled"/></div></td>
        <td></td>
      </tr>
     <tr>
        <td colspan="2">简短描述(可选):</td>
      </tr>
      <tr class="noborder">
        <td><textarea  rows="6" onkeyup="textareasize(this)" name="description" id="description" cols="50" class="tarea"><?=$rs['description']?></textarea></td>
        <td></td>
      </tr>
      <tbody id="style_number">
        <tr>
          <th colspan="15" class="partition">数字(number)</th>
        </tr>
        <tr>
          <td colspan="2">数值最大值（可选）:</td>
        </tr>
        <tr class="noborder">
          <td><input name="rules[number][maxnum]" value="<?=$rs['rules']['maxnum']?>" type="text" class="txt"/></td>
          <td></td>
        </tr>
        <tr>
          <td colspan="2">数值最小值（可选）:</td>
        </tr>
        <tr class="noborder">
          <td><input name="rules[number][minnum]" value="<?=$rs['rules']['minnum']?>" type="text" class="txt"   /></td>
          <td></td>
        </tr>
      </tbody>
      <tbody id="style_text" style="display: none">
        <tr>
          <th colspan="15" class="partition">字串(text)</th>
        </tr>
        <tr>
          <td colspan="2">内容最大长度（可选）:</td>
        </tr>
        <tr class="noborder">
          <td><input name="rules[text][maxlength]" value="<?=$rs['rules']['maxlength']?>" type="text" class="txt"   /></td>
          <td></td>
        </tr>
      </tbody>
      <tbody id="style_textarea" style="display: none">
        <tr>
          <th colspan="15" class="partition">文本(textarea)</th>
        </tr>
        <tr>
          <td colspan="2">内容最大长度（可选）:</td>
        </tr>
        <tr class="noborder">
          <td><input name="rules[textarea][maxlength]" value="<?=$rs['rules']['maxlength']?>" type="text" class="txt"   /></td>
          <td></td>
        </tr>
      </tbody>
      <tbody id="style_select" style="display: none">
        <tr>
          <th colspan="15" class="partition">选择(select)</th>
        </tr>
        <tr>
          <td colspan="2">选项内容:</td>
        </tr>
        <tr class="noborder">
          <td><textarea  rows="6" onkeyup="textareasize(this)" name="rules[select][choices]" id="rules[select][choices]" cols="50" class="tarea"><?=$rs['rules']['choices']?></textarea></td>
          <td>只在项目为可选时有效，每行一个选项，等号前面为选项索引(建议用数字)，后面为内容，例如: <br />
            <i>1 = 光电鼠标<br />
            2 = 机械鼠标<br />
            3 = 没有鼠标</i><br />
            注意: 选项确定后请勿修改索引和内容的对应关系，但仍可以新增选项。如需调换显示顺序，可以通过移动整行的上下位置来实现</td>
        </tr>
      </tbody>
      <tbody id="style_multiple" style="display: none">
        <tr>
          <th colspan="15" class="partition">多选选择(multiple)</th>
        </tr>
        <tr>
          <td colspan="2">选项内容:</td>
        </tr>
        <tr class="noborder">
          <td><textarea  rows="6" onkeyup="textareasize(this)" name="rules[multiple][choices]" id="rules[multiple][choices]" cols="50" class="tarea"><?=$rs['rules']['choices']?></textarea></td>
          <td>只在项目为可选时有效，每行一个选项，等号前面为选项索引(建议用数字)，后面为内容，例如: <br />
            <i>1 = 光电鼠标<br />
            2 = 机械鼠标<br />
            3 = 没有鼠标</i><br />
            注意: 选项确定后请勿修改索引和内容的对应关系，但仍可以新增选项。如需调换显示顺序，可以通过移动整行的上下位置来实现</td>
        </tr>
      </tbody>
      <tbody id="style_radio" style="display: none">
        <tr>
          <th colspan="15" class="partition">单选(radio)</th>
        </tr>
        <tr>
          <td colspan="2">选项内容:</td>
        </tr>
        <tr class="noborder">
          <td><textarea  rows="6" onkeyup="textareasize(this)" name="rules[radio][choices]" id="rules[radio][choices]" cols="50" class="tarea"><?=$rs['rules']['choices']?></textarea></td>
          <td>只在项目为可选时有效，每行一个选项，等号前面为选项索引(建议用数字)，后面为内容，例如: <br />
            <i>1 = 光电鼠标<br />
            2 = 机械鼠标<br />
            3 = 没有鼠标</i><br />
            注意: 选项确定后请勿修改索引和内容的对应关系，但仍可以新增选项。如需调换显示顺序，可以通过移动整行的上下位置来实现</td>
        </tr>
      </tbody>
      <tbody id="style_checkbox" style="display: none">
        <tr>
          <th colspan="15" class="partition">多选(checkbox)</th>
        </tr>
        <tr>
          <td colspan="2">选项内容:</td>
        </tr>
        <tr class="noborder">
          <td><textarea  rows="6" onkeyup="textareasize(this)" name="rules[checkbox][choices]" id="rules[checkbox][choices]" cols="50" class="tarea"><?=$rs['rules']['choices']?></textarea></td>
          <td>只在项目为可选时有效，每行一个选项，等号前面为选项索引(建议用数字)，后面为内容，例如: <br />
            <i>1 = 光电鼠标<br />
            2 = 机械鼠标<br />
            3 = 没有鼠标</i><br />
            注意: 选项确定后请勿修改索引和内容的对应关系，但仍可以新增选项。如需调换显示顺序，可以通过移动整行的上下位置来实现</td>
        </tr>
      </tbody>
      <tbody id="style_image" style="display: none">
        <tr>
          <th colspan="15" class="partition">图片(image)</th>
        </tr>
        <tr>
          <td colspan="2">图片最大宽度（可选）:</td>
        </tr>
        <tr class="noborder">
          <td><input name="rules[image][maxwidth]" value="" type="text" class="txt"/><?=$rs['rules']['maxwidth']?></td>
          <td></td>
        </tr>
        <tr>
          <td colspan="2">图片最大高度（可选）:</td>
        </tr>
        <tr class="noborder">
          <td><input name="rules[image][maxheight]" value="" type="text" class="txt"/><?=$rs['rules']['maxheight']?></td>
          <td></td>
        </tr>
      </tbody>
      <tr>
        <td colspan="15"><div class="fixsel">
            <input type="submit" class="btn" name="editsubmit" value="提交"  />
          </div></td>
      </tr>
    </table>
  </form>
</div>
<script type="text/JavaScript">
$(function(){
	$("#type").change(function(){
		$('tbody[id^=style]').hide(); 
		$('#style_'+this.value).show(); 
		
	}); 
	$("#validate").change(function(){
		if(this.value=='preg'){
			$('#validate_preg').show(); 
			$('input[name=validate]').attr("disabled","");
		}else{
			$('#validate_preg').hide(); 
			$('input[name=validate]').attr("disabled","disabled");
		}
	}); 
<?php if($rs['hidden']=="1"){ ?>
	$('#hidden').attr("checked","checked");<?php } ?>
<?php if($rs['type']){ ?>
	$('#type').val("<?=$rs['type']?>").change();<?php } ?>
<?php if(in_array($rs['validate'],array('N','0','1','2','4','5'))){ ?>
	$('#validate').val("<?=$rs['validate']?>");
<?php }else if($rs['validate']=="preg"){ ?>
	$('#validate').val("preg").change();
<?php } ?>		
});
</script>
</body></html>