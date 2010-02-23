<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=iCMS_CHARSET?>">
<link rel="stylesheet" href="admin/images/style.css" type="text/css" media="all" />
<link rel="stylesheet" href="admin/images/jquery.Jcrop.css" type="text/css" media="all" />
<link rel="stylesheet" href="admin/images/ui.slider.css" type="text/css" media="all" />
<script type="text/javascript" src="javascript/jquery.js"></script>
<script type="text/javascript" src="javascript/jquery.Jcrop.js"></script>
<script type="text/javascript" src="javascript/jquery.ui.core.js"></script>
<script type="text/javascript" src="javascript/jquery.slider.js"></script>
<script language="JavaScript" type="text/javascript">
var jcrop_api;
$(function(){
	initJcrop();
	ratioOptions();
	$("#slider").slider({
		value:<?=$rate?>,
		min: <?=$sliderMin?>,
		max: 100,
		start: function(event, ui) {
			jcrop_api.destroy();
		},					
		slide: function(event, ui) {
		//	if(ui.value<<?=$sliderMin?>) ui.slider( 'destroy' ) ;
			$("#amount").val(ui.value);
			var nw=parseInt((ui.value/100)*<?=$width?>);
			$("#pic,#preview").width(nw);
			$('#preview').height($('#pic').height());
//			jcrop_api.resize(nw,$('#pic').height());
			$("#nw").text(nw);
			jcrop_api.focus();
		},
		stop: function(event, ui) {
			initJcrop();
			var Options =  $("input[name=Options]:checked").val();
			$("input[name=Options][value="+Options+"]").click();
		}
	});
	$("#amount").val(<?=$rate?>);
	$("#x,#y,#w,#h").change( function() {
		var Options =  $("input[name=Options]:checked").val();
		setOptions(Options);
	}); 
	$("input[name=Options]").click(function() {
		setOptions(this.value);
	});
});
function initJcrop(){
	jcrop_api = $.Jcrop('#pic');
};
function ratioOptions(){
	jcrop_api.setOptions({
		allowResize: true,
		aspectRatio: <?=$tw?>/<?=$th?>,
		minSize:[<?=$tw?>,<?=$th?>],
		onChange: update
	});
	$("input[name=Options][value=ratio]").attr("checked","checked");
}
function setOptions(o){
	var s={
		x:parseInt($('#x').val()),y:parseInt($('#y').val()),
		w:parseInt($('#w').val()),h:parseInt($('#h').val())
	};
	if(o=="size"){
		jcrop_api.setOptions({allowResize: false,aspectRatio: 0});
	}else if(o=="ratio"){
		ratioOptions();
	}else if(o=="free"){
		jcrop_api.setOptions({ allowResize: true,aspectRatio: 0});
	}
	jcrop_api.setSelect([s.x,s.y,s.w,s.h]);
	jcrop_api.focus();
}
function update(c){
	var Options =  $("input[name=Options]:checked").val();
	if(Options!="ratio") return;
	$('#x').val(c.x);
	$('#y').val(c.y);
	$('#w').val(c.w);
	$('#h').val(c.h);
	if (parseInt(c.w) > 0){
		var rx = <?=$tw?> / c.w;
		var ry = <?=$th?> / c.h;
		var nw = $('#pic').width();
		var nh = $('#pic').height();
		$('#preview').css({
			width: Math.round(rx * nw) + 'px',
			height: Math.round(ry * nh) + 'px',
			marginLeft: '-' + Math.round(rx * c.x) + 'px',
			marginTop: '-' + Math.round(ry * c.y) + 'px'
		});
	}
};
function check(){
	$("#pWidth").val($('#pic').width());
	$('#PHeight').val($('#pic').height());
	if (parseInt($('#w').val())) return true;
	alert('请选择剪切范围.');
	return false;
};
</script>
<style type="text/css">
.cropbox {
	margin:5px;
	text-align:center;
	padding:5px;
	overflow:scroll;
}
.cropbox img {
	border:#CCCCCC solid 1px;
}
.cropbox .jcrop-holder {
	margin-left:auto;
	margin-right:auto;
}
fieldset {
	margin:5px auto;
	text-align:center;
}
</style>
</head>
<body>
<div class="container" id="cpcontainer">
  <fieldset>
  <legend>图片剪切</legend>
  <div class="cropbox"><img src="<?=$pFile?>" width="<?=$pw?>" id="pic"/> </div>
  </fieldset>
  <fieldset>
  <legend>设置</legend>
  <table class="tb tb2 " style="width:98%;">
    <tr>
      <td style="width:100px;">原始比率:</td>
      <td align="right" style="width:500px;"><span id="nw"><?=$pw?></span>/
        <?=$width?>
        <br />
        <div id="slider"></div></td>
      <td style="width:100px;"><input type="text" id="amount" style="width:36px; border:#CCCCCC solid 1px;"/>
        %</td>
    </tr>
    <tr>
      <td colspan="3"><table>
          <form action="<?=__SELF__?>?do=dialog&operation=post" method="post" onsubmit="return check();">
          	<input type="hidden" name="action" value="crop"/>
            <input type="hidden" id="in" name="in" value="<?=$in?>"/>
            <input type="hidden" id="pFile" name="pFile" value="<?=$pFile?>"/>
            <input type="hidden" id="pWidth" name="width" value=""/>
            <input type="hidden" id="PHeight" name="height" value=""/>
            <tr>
              <td>
                <fieldset>
                <legend>选项</legend>
                <input name="Options" type="radio" value="ratio" class="radio"/>固定比例 
                <input name="Options" type="radio" value="size" class="radio" />固定尺寸 
                <input name="Options" type="radio" value="free" class="radio" />自由裁剪 
                </fieldset>
             
                <fieldset>
                <legend>坐标</legend>
                X
                <input type="text" id="x" name="x" value="0"/>
                Y
                <input type="text" id="y" name="y" value="0"/>
                </fieldset>
                <fieldset>
                <legend>尺寸</legend>
                宽:
                <input type="text" id="w" name="w" value="0"/>
                高:
                <input type="text" id="h" name="h" value="0"/>
                <br />
                </fieldset>
                <input type="submit" value="剪切" class="btn"/>
              </td>
              <td ><fieldset>
                <legend>预览</legend>
                <div style="width:<?=$tw?>px;height:<?=$th?>px;overflow:hidden; margin:5px;"> <img src="<?=$pFile?>" id="preview" width="<?=$pw?>" style="border:#CCCCCC solid 1px;"/> </div>
                </fieldset></td>
            </tr>
          </form>
        </table></td>
    </tr>
  </table>
  </fieldset>
</div>
</body>
</html>
