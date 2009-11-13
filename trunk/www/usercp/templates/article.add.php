<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
?>
<?php include "shortcut-button.php";?>
<div class="content-box">
  <div class="content-box-header">
    <h3><?=empty($id)?'添加':'编辑'?>文章</h3>
  </div>
  <div class="content-box-content">
    <div class="tab-content default-tab" id="tab2">
      <form action="<?=__SELF__?>?do=article&operation=post" method="post" enctype="multipart/form-data" name="savearticle" id="savearticle">
        <fieldset>
        <p>
          <label>栏目</label>
          <select name="catalog" id="catalog" class="small-input">
            <?php if($cata_option){?>
            <option value="0"> == 请选择所属栏目 == </option>
            <?php echo $cata_option;}else{?>
            <option value="0"> == 暂无栏目 == </option>
            <?php }?>
          </select>
          </p>
        <p>
          <label>标题</label>
          <input type="text" name="title" class="text-input medium-input" id="title" value="<?=$rs['title']?>" />
          </p>
        <p>
          <label>出处</label>
          <input type="text" name="source" class="text-input medium-input" id="source" value="<?=$rs['source']?>" />
        </p>
        <p>
          <label>作者</label>
          <input type="text" name="author" class="text-input medium-input" id="author" value="<?=$rs['author']?>" />
        </p>
        <p>
          <label>缩略图</label>
          <input type="text" name="pic" class="text-input medium-input" id="pic" value="<?=$rs['pic']?>" />
        </p>
        <p>
          <label>摘要</label>
          <textarea class="text-input textarea wysiwyg" name="description" id="description" cols="50" rows="8"><?=$rs['description']?>
</textarea>
        </p>
        <p>
          <label>关键字</label>
          <input class="text-input large-input" type="text" id="keywords" name="keywords" value="<?=$rs['keywords']?>"/>
          多个关键字请用,格开 </p>
        <p><span style="float:right;"><img src="<?=$iCMS->dir?>admin/images/add.gif" onclick="setEditorSize('+','content',200)" title="增加编辑器高度"/> <img src="<?=$iCMS->dir?>admin/images/desc.gif" onclick="setEditorSize('-','content',200)" title="减少编辑器高度"/></span>
          <label>内容</label>
          <?=$editor->CreateHtml()?>
        </p>
        <p>
          <input name="aid" type="hidden" id="aid" value="<?=$id?>" />
          <input name="userid" type="hidden" id="userid" value="<?=$rs['userid']?>" />
          <input name="action" type="hidden" id="action" value="save" />
          <input type="submit" value="提交" class="button" />
          &nbsp;&nbsp;
          <input type="reset" value="重置" class="button" />
        </p>
        </fieldset>
        <div class="clear"></div>
        <!-- End .clear -->
      </form>
    </div>
  </div>
</div>
<script language="JavaScript" type="text/javascript">
$(function(){
	$("#title").focus();
	$("#savearticle").submit(function(){
		if($("#catalog option:selected").attr("value")=="0"){
			alert("请选择所属栏目");
			$("#catalog").focus();
			return false;
		}
		if($("#title").val()==''){
			alert("标题不能为空!");
			$("#title").focus();
			return false;
		}
		var oEditor = FCKeditorAPI.GetInstance('content') ;
		if(oEditor.GetXHTML( true )==''){
			alert("内容不能为空!");
			oEditor.Focus();
			return false;
		}
	}); 
});
</script>
<?php /*
<div class="notification attention png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Attention notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
<div class="notification information png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Information notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
<div class="notification success png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Success notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
<div class="notification error png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Error notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
*/?>
