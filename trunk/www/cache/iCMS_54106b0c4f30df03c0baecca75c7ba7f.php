<?php error_reporting(iCMS_TPL_BUG?E_ALL ^ E_NOTICE:0);!defined('iCMS') && exit('What are you doing?');?>
<?php require_once('D:\m2.com\www\include\template\plugins\function.cycle.php'); $this->register_function("cycle", "tpl_function_cycle"); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_vars['site']['title']; ?>-<?php echo $this->_vars['site']['seotitle']; ?></title>
<meta name="keywords" content="<?php echo $this->_vars['site']['keywords']; ?>">
<meta name="description" content="<?php echo $this->_run_modifier($this->_vars['site']['description'], 'html2txt', 'plugin', 1); ?>">
<meta name="copyright" content="<?php echo $this->_vars['site']['title']; ?>" />
<link href="<?php echo $this->_vars['site']['dir']; ?>/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<link href="<?php echo $this->_vars['site']['dir']; ?>/skins/global.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->_vars['site']['dir']; ?>/skins/index.css" rel="stylesheet" type="text/css" />
<?php $this->_run_iCMS(array('module' => "javascript")); ?>
<script type="text/javascript">
<!--
$(function(){
	$(".showpic").mouseover(function(){
		var _this=this;
		$(".showpic").each(function(i){
			var picNum=$(this).attr("picdiv"); 
			if(_this.id==this.id){
				if(picNum==0){
					$(this).addClass("btitle2");
					$("#titlelink").addClass("cDRed");
				}else{
					$(this).addClass("active");
					$("#picli"+picNum).addClass("active");
				}
				$("#picdiv"+picNum).show();
			}else{
				if(picNum==0){
					$("#headline").addClass("btitle");
					$("#titlelink").removeClass("cDRed");
				}else{
					$(this).removeClass("active");
					$("#picli"+picNum).removeClass("active");
				}
				
				$("#picdiv"+picNum).hide();
			}
		});
	});
});
-->
</script>
</head>
<body>
<div id="toolbar"><a href="<?php echo $this->_vars['site']['url']; ?>/register.php" target="_blank">注册</a> <a href="<?php echo $this->_vars['site']['url']; ?>/usercp.php" target="_blank">登陆</a></div>
<div id="header">
	<h1><a href="<?php echo $this->_vars['site']['url']; ?>"><?php echo $this->_vars['site']['title']; ?></a></h1>
	<div id="catalogs">
	  <ul>
	    <li><a href="<?php echo $this->_vars['site']['url']; ?>">首页</a></li>
	    <?php $this->_run_iCMS(array('loop' => 'true', 'type' => 'top', 'module' => "catalog"));  if (isset($this->_iCMS['G4027'])) unset($this->_iCMS['G4027']);
$this->_iCMS['G4027']['loop'] = is_array($this->_vars['catalog']) ? count($this->_vars['catalog']) : max(0, (int)$this->_vars['catalog']);
$this->_iCMS['_G4027']=$this->_vars['catalog'];
unset($this->_vars['catalog']);
$this->_iCMS['G4027']['show'] = true;
$this->_iCMS['G4027']['max'] = $this->_iCMS['G4027']['loop'];
$this->_iCMS['G4027']['step'] = 1;
$this->_iCMS['G4027']['start'] = $this->_iCMS['G4027']['step'] > 0 ? 0 : $this->_iCMS['G4027']['loop']-1;
if ($this->_iCMS['G4027']['show']) {
	$this->_iCMS['G4027']['total'] = $this->_iCMS['G4027']['loop'];
	if ($this->_iCMS['G4027']['total'] == 0){
		$this->_iCMS['G4027']['show'] = false;
	}
} else{
	$this->_iCMS['G4027']['total'] = 0;
}
if ($this->_iCMS['G4027']['show']){

		for ($this->_iCMS['G4027']['index'] = $this->_iCMS['G4027']['start'], $this->_iCMS['G4027']['iteration'] = 1;
			 $this->_iCMS['G4027']['iteration'] <= $this->_iCMS['G4027']['total'];
			 $this->_iCMS['G4027']['index'] += $this->_iCMS['G4027']['step'], $this->_iCMS['G4027']['iteration']++){
$this->_iCMS['G4027']['rownum'] = $this->_iCMS['G4027']['iteration'];
$this->_iCMS['G4027']['index_prev'] = $this->_iCMS['G4027']['index'] - $this->_iCMS['G4027']['step'];
$this->_iCMS['G4027']['index_next'] = $this->_iCMS['G4027']['index'] + $this->_iCMS['G4027']['step'];
$this->_iCMS['G4027']['first']	  = ($this->_iCMS['G4027']['iteration'] == 1);
$this->_iCMS['G4027']['last']	   = ($this->_iCMS['G4027']['iteration'] == $this->_iCMS['G4027']['total']);
$this->_vars['catalog']= array_merge($this->_iCMS['_G4027'][$this->_iCMS['G4027']['index']],$this->_iCMS['G4027']);
?>
	    <li id="catalog_<?php echo $this->_vars['catalog']['id']; ?>"><a href="<?php echo $this->_vars['catalog']['url']; ?>"><?php echo $this->_vars['catalog']['name']; ?></a></li>
	    <?php }}?>
	  </ul>
	</div>
</div>
<div id="advertise">
	<?php $this->_run_iCMS(array('name' => "首页顶部广告", 'module' => "advertise")); ?>
</div>
<div id="search">
  <form name="searchForm" method="get" action="<?php echo $this->_vars['site']['url']; ?>/search.php" id="searchForm" target="_blank">
    <table border="0" align="left" cellpadding="2" cellspacing="1">
      <tr>
        <td width="58" height="2"><select name="stype" id="stype" >
            <option value="title" selected>标题</option>
            <option value="content">内容</option>
          </select></td>
        <td width="252"><input name="keyword" type="text" class="i1" id="keyword" size="36" /></td>
        <td width="43"><input type="image" src="<?php echo $this->_vars['site']['tplurl']; ?>/images/search.gif" /></td>
        <td width="60" align="center">热门标签:</td>
      </tr>
    </table>
  </form>
  <script type="text/javascript">
	$(function(){
		$("#searchForm").submit( function() {
			if($("#keyword").val()==""){
				alert("请填写关键字");
				$("#keyword").focus();
				return false;
			}
		});
	});
  </script>
  <div class="tag"> <?php $this->_run_iCMS(array('loop' => 'true', 'row' => '10', 'orderby' => 'hot', 'module' => "tag"));  if (isset($this->_iCMS['G42a1'])) unset($this->_iCMS['G42a1']);
$this->_iCMS['G42a1']['loop'] = is_array($this->_vars['tag']) ? count($this->_vars['tag']) : max(0, (int)$this->_vars['tag']);
$this->_iCMS['_G42a1']=$this->_vars['tag'];
unset($this->_vars['tag']);
$this->_iCMS['G42a1']['show'] = true;
$this->_iCMS['G42a1']['max'] = $this->_iCMS['G42a1']['loop'];
$this->_iCMS['G42a1']['step'] = 1;
$this->_iCMS['G42a1']['start'] = $this->_iCMS['G42a1']['step'] > 0 ? 0 : $this->_iCMS['G42a1']['loop']-1;
if ($this->_iCMS['G42a1']['show']) {
	$this->_iCMS['G42a1']['total'] = $this->_iCMS['G42a1']['loop'];
	if ($this->_iCMS['G42a1']['total'] == 0){
		$this->_iCMS['G42a1']['show'] = false;
	}
} else{
	$this->_iCMS['G42a1']['total'] = 0;
}
if ($this->_iCMS['G42a1']['show']){

		for ($this->_iCMS['G42a1']['index'] = $this->_iCMS['G42a1']['start'], $this->_iCMS['G42a1']['iteration'] = 1;
			 $this->_iCMS['G42a1']['iteration'] <= $this->_iCMS['G42a1']['total'];
			 $this->_iCMS['G42a1']['index'] += $this->_iCMS['G42a1']['step'], $this->_iCMS['G42a1']['iteration']++){
$this->_iCMS['G42a1']['rownum'] = $this->_iCMS['G42a1']['iteration'];
$this->_iCMS['G42a1']['index_prev'] = $this->_iCMS['G42a1']['index'] - $this->_iCMS['G42a1']['step'];
$this->_iCMS['G42a1']['index_next'] = $this->_iCMS['G42a1']['index'] + $this->_iCMS['G42a1']['step'];
$this->_iCMS['G42a1']['first']	  = ($this->_iCMS['G42a1']['iteration'] == 1);
$this->_iCMS['G42a1']['last']	   = ($this->_iCMS['G42a1']['iteration'] == $this->_iCMS['G42a1']['total']);
$this->_vars['tag']= array_merge($this->_iCMS['_G42a1'][$this->_iCMS['G42a1']['index']],$this->_iCMS['G42a1']);
?> <a href="<?php echo $this->_vars['tag']['url']; ?>" target="_blank"><?php echo $this->_vars['tag']['name']; ?></a> <?php }}?> </div>
</div>
<div id="container">
  <div class="area1">
    <div class="cleft"> <?php $this->_run_iCMS(array('loop' => 'true', 'pic' => 'true', 'row' => "7", 'module' => "list"));  if (isset($this->_iCMS['G4452'])) unset($this->_iCMS['G4452']);
$this->_iCMS['G4452']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_G4452']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['G4452']['show'] = true;
$this->_iCMS['G4452']['max'] = $this->_iCMS['G4452']['loop'];
$this->_iCMS['G4452']['step'] = 1;
$this->_iCMS['G4452']['start'] = $this->_iCMS['G4452']['step'] > 0 ? 0 : $this->_iCMS['G4452']['loop']-1;
if ($this->_iCMS['G4452']['show']) {
	$this->_iCMS['G4452']['total'] = $this->_iCMS['G4452']['loop'];
	if ($this->_iCMS['G4452']['total'] == 0){
		$this->_iCMS['G4452']['show'] = false;
	}
} else{
	$this->_iCMS['G4452']['total'] = 0;
}
if ($this->_iCMS['G4452']['show']){

		for ($this->_iCMS['G4452']['index'] = $this->_iCMS['G4452']['start'], $this->_iCMS['G4452']['iteration'] = 1;
			 $this->_iCMS['G4452']['iteration'] <= $this->_iCMS['G4452']['total'];
			 $this->_iCMS['G4452']['index'] += $this->_iCMS['G4452']['step'], $this->_iCMS['G4452']['iteration']++){
$this->_iCMS['G4452']['rownum'] = $this->_iCMS['G4452']['iteration'];
$this->_iCMS['G4452']['index_prev'] = $this->_iCMS['G4452']['index'] - $this->_iCMS['G4452']['step'];
$this->_iCMS['G4452']['index_next'] = $this->_iCMS['G4452']['index'] + $this->_iCMS['G4452']['step'];
$this->_iCMS['G4452']['first']	  = ($this->_iCMS['G4452']['iteration'] == 1);
$this->_iCMS['G4452']['last']	   = ($this->_iCMS['G4452']['iteration'] == $this->_iCMS['G4452']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_G4452'][$this->_iCMS['G4452']['index']],$this->_iCMS['G4452']);
?> <?php if ($this->_vars['list']['first']): ?>
      <div id="headline" class="btitle2 showpic" picdiv="<?php echo $this->_vars['list']['index']; ?>">
        <h1><a href="<?php echo $this->_vars['list']['url']; ?>" id="titlelink" title="<?php echo $this->_vars['list']['title']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 20); ?></a></h1>
      </div>
      <div class="newstitle"> <?php else: ?>
        <ul id="picul<?php echo $this->_vars['list']['index']; ?>" class="showpic" picdiv="<?php echo $this->_vars['list']['index']; ?>">
          <li id="picli<?php echo $this->_vars['list']['index']; ?>">·<a href="<?php echo $this->_vars['list']['url']; ?>" title="<?php echo $this->_vars['list']['title']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 20); ?></a> <span><?php echo $this->_run_modifier($this->_vars['list']['description'], 'cut', 'plugin', 1, 8); ?></span></li>
        </ul>
        <?php endif; ?> <?php }}?> </div>
    </div>
    <div class="cright"> <?php $this->_run_iCMS(array('loop' => 'true', 'pic' => 'true', 'row' => "7", 'module' => "list"));  if (isset($this->_iCMS['G6937'])) unset($this->_iCMS['G6937']);
$this->_iCMS['G6937']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_G6937']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['G6937']['show'] = true;
$this->_iCMS['G6937']['max'] = $this->_iCMS['G6937']['loop'];
$this->_iCMS['G6937']['step'] = 1;
$this->_iCMS['G6937']['start'] = $this->_iCMS['G6937']['step'] > 0 ? 0 : $this->_iCMS['G6937']['loop']-1;
if ($this->_iCMS['G6937']['show']) {
	$this->_iCMS['G6937']['total'] = $this->_iCMS['G6937']['loop'];
	if ($this->_iCMS['G6937']['total'] == 0){
		$this->_iCMS['G6937']['show'] = false;
	}
} else{
	$this->_iCMS['G6937']['total'] = 0;
}
if ($this->_iCMS['G6937']['show']){

		for ($this->_iCMS['G6937']['index'] = $this->_iCMS['G6937']['start'], $this->_iCMS['G6937']['iteration'] = 1;
			 $this->_iCMS['G6937']['iteration'] <= $this->_iCMS['G6937']['total'];
			 $this->_iCMS['G6937']['index'] += $this->_iCMS['G6937']['step'], $this->_iCMS['G6937']['iteration']++){
$this->_iCMS['G6937']['rownum'] = $this->_iCMS['G6937']['iteration'];
$this->_iCMS['G6937']['index_prev'] = $this->_iCMS['G6937']['index'] - $this->_iCMS['G6937']['step'];
$this->_iCMS['G6937']['index_next'] = $this->_iCMS['G6937']['index'] + $this->_iCMS['G6937']['step'];
$this->_iCMS['G6937']['first']	  = ($this->_iCMS['G6937']['iteration'] == 1);
$this->_iCMS['G6937']['last']	   = ($this->_iCMS['G6937']['iteration'] == $this->_iCMS['G6937']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_G6937'][$this->_iCMS['G6937']['index']],$this->_iCMS['G6937']);
?>
      <div id="picdiv<?php echo $this->_vars['list']['index']; ?>" style="display:<?php if ($this->_vars['list']['first']): ?>block<?php else: ?>none<?php endif; ?>;">
        <div class="fpic"><a href="<?php echo $this->_vars['list']['url']; ?>"><img src="<?php echo $this->_run_modifier($this->_vars['list']['pic'], 'small', 'plugin', 1, 400, 200); ?>" width="400" height="200" border="0" alt="<?php echo $this->_vars['list']['title']; ?>" /></a></div>
        <div class="digest"><strong>核心提示：</strong><?php echo $this->_run_modifier($this->_run_modifier($this->_vars['list']['description'], 'html2txt', 'plugin', 1), 'cut', 'plugin', 1, 50); ?>……<a href="<?php echo $this->_vars['list']['url']; ?>">[详细]</a></div>
      </div>
      <?php }}?> </div>
    <div class="clear"> </div>
  </div>
  <div class="blank9"> </div>
  <div class="area0">
    <div class="colist"> <?php $this->_run_iCMS(array('loop' => 'true', 'type' => "top", 'row' => "4", 'module' => "catalog"));  if (isset($this->_iCMS['G6c78'])) unset($this->_iCMS['G6c78']);
$this->_iCMS['G6c78']['loop'] = is_array($this->_vars['catalog']) ? count($this->_vars['catalog']) : max(0, (int)$this->_vars['catalog']);
$this->_iCMS['_G6c78']=$this->_vars['catalog'];
unset($this->_vars['catalog']);
$this->_iCMS['G6c78']['show'] = true;
$this->_iCMS['G6c78']['max'] = $this->_iCMS['G6c78']['loop'];
$this->_iCMS['G6c78']['step'] = 1;
$this->_iCMS['G6c78']['start'] = $this->_iCMS['G6c78']['step'] > 0 ? 0 : $this->_iCMS['G6c78']['loop']-1;
if ($this->_iCMS['G6c78']['show']) {
	$this->_iCMS['G6c78']['total'] = $this->_iCMS['G6c78']['loop'];
	if ($this->_iCMS['G6c78']['total'] == 0){
		$this->_iCMS['G6c78']['show'] = false;
	}
} else{
	$this->_iCMS['G6c78']['total'] = 0;
}
if ($this->_iCMS['G6c78']['show']){

		for ($this->_iCMS['G6c78']['index'] = $this->_iCMS['G6c78']['start'], $this->_iCMS['G6c78']['iteration'] = 1;
			 $this->_iCMS['G6c78']['iteration'] <= $this->_iCMS['G6c78']['total'];
			 $this->_iCMS['G6c78']['index'] += $this->_iCMS['G6c78']['step'], $this->_iCMS['G6c78']['iteration']++){
$this->_iCMS['G6c78']['rownum'] = $this->_iCMS['G6c78']['iteration'];
$this->_iCMS['G6c78']['index_prev'] = $this->_iCMS['G6c78']['index'] - $this->_iCMS['G6c78']['step'];
$this->_iCMS['G6c78']['index_next'] = $this->_iCMS['G6c78']['index'] + $this->_iCMS['G6c78']['step'];
$this->_iCMS['G6c78']['first']	  = ($this->_iCMS['G6c78']['iteration'] == 1);
$this->_iCMS['G6c78']['last']	   = ($this->_iCMS['G6c78']['iteration'] == $this->_iCMS['G6c78']['total']);
$this->_vars['catalog']= array_merge($this->_iCMS['_G6c78'][$this->_iCMS['G6c78']['index']],$this->_iCMS['G6c78']);
?>
      <div class="<?php echo tpl_function_cycle(array('values' => "col1,col2"), $this);?>">
        <div class="title">
          <h3><span><?php echo $this->_vars['catalog']['name']; ?></span></h3>
        </div>
        <ul class="newsList">
          <?php $this->_run_iCMS(array('loop' => 'true', 'sub' => "all", 'row' => "10", 'sortid' => $this->_vars['catalog']['id'], 'module' => "list"));  if (isset($this->_iCMS['Gb036'])) unset($this->_iCMS['Gb036']);
$this->_iCMS['Gb036']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Gb036']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Gb036']['show'] = true;
$this->_iCMS['Gb036']['max'] = $this->_iCMS['Gb036']['loop'];
$this->_iCMS['Gb036']['step'] = 1;
$this->_iCMS['Gb036']['start'] = $this->_iCMS['Gb036']['step'] > 0 ? 0 : $this->_iCMS['Gb036']['loop']-1;
if ($this->_iCMS['Gb036']['show']) {
	$this->_iCMS['Gb036']['total'] = $this->_iCMS['Gb036']['loop'];
	if ($this->_iCMS['Gb036']['total'] == 0){
		$this->_iCMS['Gb036']['show'] = false;
	}
} else{
	$this->_iCMS['Gb036']['total'] = 0;
}
if ($this->_iCMS['Gb036']['show']){

		for ($this->_iCMS['Gb036']['index'] = $this->_iCMS['Gb036']['start'], $this->_iCMS['Gb036']['iteration'] = 1;
			 $this->_iCMS['Gb036']['iteration'] <= $this->_iCMS['Gb036']['total'];
			 $this->_iCMS['Gb036']['index'] += $this->_iCMS['Gb036']['step'], $this->_iCMS['Gb036']['iteration']++){
$this->_iCMS['Gb036']['rownum'] = $this->_iCMS['Gb036']['iteration'];
$this->_iCMS['Gb036']['index_prev'] = $this->_iCMS['Gb036']['index'] - $this->_iCMS['Gb036']['step'];
$this->_iCMS['Gb036']['index_next'] = $this->_iCMS['Gb036']['index'] + $this->_iCMS['Gb036']['step'];
$this->_iCMS['Gb036']['first']	  = ($this->_iCMS['Gb036']['iteration'] == 1);
$this->_iCMS['Gb036']['last']	   = ($this->_iCMS['Gb036']['iteration'] == $this->_iCMS['Gb036']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Gb036'][$this->_iCMS['Gb036']['index']],$this->_iCMS['Gb036']);
?>
          <li><span class="date"><?php echo $this->_run_modifier($this->_vars['list']['pubdate'], 'date', 'plugin', 1, "m-d"); ?></span><a href="<?php echo $this->_vars['list']['url']; ?>" title="<?php echo $this->_vars['list']['title']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 16); ?></a></li>
          <?php }}?>
        </ul>
        <p class="alignR pRight"><a href="<?php echo $this->_vars['catalog']['url']; ?>" class="cBlue">更多<?php echo $this->_vars['catalog']['name']; ?>&gt;&gt;</a></p>
      </div>
      <?php if ($this->_vars['catalog']['rownum'] % 2 == "0"): ?>
      <div class="blank9"> </div>
      <?php endif; ?> <?php }}?> </div>
    <div class="dignews">
      <div class="digtitle">
        <ul id="digtitle">
          <li><a href="javascript:void(0);" class="thisclass">最近更新</a></li>
        </ul>
      </div>
      <dl id="diglist">
        <dd> <?php $this->_run_iCMS(array('loop' => 'true', 'row' => "7", 'module' => "list"));  if (isset($this->_iCMS['Gb44e'])) unset($this->_iCMS['Gb44e']);
$this->_iCMS['Gb44e']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Gb44e']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Gb44e']['show'] = true;
$this->_iCMS['Gb44e']['max'] = $this->_iCMS['Gb44e']['loop'];
$this->_iCMS['Gb44e']['step'] = 1;
$this->_iCMS['Gb44e']['start'] = $this->_iCMS['Gb44e']['step'] > 0 ? 0 : $this->_iCMS['Gb44e']['loop']-1;
if ($this->_iCMS['Gb44e']['show']) {
	$this->_iCMS['Gb44e']['total'] = $this->_iCMS['Gb44e']['loop'];
	if ($this->_iCMS['Gb44e']['total'] == 0){
		$this->_iCMS['Gb44e']['show'] = false;
	}
} else{
	$this->_iCMS['Gb44e']['total'] = 0;
}
if ($this->_iCMS['Gb44e']['show']){

		for ($this->_iCMS['Gb44e']['index'] = $this->_iCMS['Gb44e']['start'], $this->_iCMS['Gb44e']['iteration'] = 1;
			 $this->_iCMS['Gb44e']['iteration'] <= $this->_iCMS['Gb44e']['total'];
			 $this->_iCMS['Gb44e']['index'] += $this->_iCMS['Gb44e']['step'], $this->_iCMS['Gb44e']['iteration']++){
$this->_iCMS['Gb44e']['rownum'] = $this->_iCMS['Gb44e']['iteration'];
$this->_iCMS['Gb44e']['index_prev'] = $this->_iCMS['Gb44e']['index'] - $this->_iCMS['Gb44e']['step'];
$this->_iCMS['Gb44e']['index_next'] = $this->_iCMS['Gb44e']['index'] + $this->_iCMS['Gb44e']['step'];
$this->_iCMS['Gb44e']['first']	  = ($this->_iCMS['Gb44e']['iteration'] == 1);
$this->_iCMS['Gb44e']['last']	   = ($this->_iCMS['Gb44e']['iteration'] == $this->_iCMS['Gb44e']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Gb44e'][$this->_iCMS['Gb44e']['index']],$this->_iCMS['Gb44e']);
?>
          <div class="digbox" id="digboxa">
            <div class="diglink"><span id='digg_<?php echo $this->_vars['list']['id']; ?>'><?php echo $this->_vars['list']['digg']; ?></span> <a class="digvisited" href="javascript:digg('digg',<?php echo $this->_vars['list']['id']; ?>);"><!--顶一下--></a></div>
            <div class="ntitle"><a href='<?php echo $this->_vars['list']['url']; ?>' title="<?php echo $this->_vars['list']['title']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 16); ?></a><span><?php echo $this->_run_modifier($this->_vars['list']['pubdate'], 'date', 'plugin', 1, "Y-m-d"); ?></span> </div>
            <div class="preview"> <?php echo $this->_run_modifier($this->_run_modifier($this->_vars['list']['description'], 'html2txt', 'plugin', 1), 'cut', 'plugin', 1, 40); ?>... </div>
          </div>
          <?php }}?> </dd>
      </dl>
    </div>
  </div>
  <div class="blank9"> </div>
  <div class="area col" style="padding:5px 0px; height:190px">
    <div class="title">
      <h3><span style="color:#666666">每日精华</span></h3>
    </div>
    <div class="pic">
      <ul class="picList">
        <?php $this->_run_iCMS(array('loop' => 'true', 'pic' => 'true', 'sub' => "all", 'row' => '7', 'orderby' => "pubdate", 'module' => "list"));  if (isset($this->_iCMS['Gb79b'])) unset($this->_iCMS['Gb79b']);
$this->_iCMS['Gb79b']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Gb79b']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Gb79b']['show'] = true;
$this->_iCMS['Gb79b']['max'] = $this->_iCMS['Gb79b']['loop'];
$this->_iCMS['Gb79b']['step'] = 1;
$this->_iCMS['Gb79b']['start'] = $this->_iCMS['Gb79b']['step'] > 0 ? 0 : $this->_iCMS['Gb79b']['loop']-1;
if ($this->_iCMS['Gb79b']['show']) {
	$this->_iCMS['Gb79b']['total'] = $this->_iCMS['Gb79b']['loop'];
	if ($this->_iCMS['Gb79b']['total'] == 0){
		$this->_iCMS['Gb79b']['show'] = false;
	}
} else{
	$this->_iCMS['Gb79b']['total'] = 0;
}
if ($this->_iCMS['Gb79b']['show']){

		for ($this->_iCMS['Gb79b']['index'] = $this->_iCMS['Gb79b']['start'], $this->_iCMS['Gb79b']['iteration'] = 1;
			 $this->_iCMS['Gb79b']['iteration'] <= $this->_iCMS['Gb79b']['total'];
			 $this->_iCMS['Gb79b']['index'] += $this->_iCMS['Gb79b']['step'], $this->_iCMS['Gb79b']['iteration']++){
$this->_iCMS['Gb79b']['rownum'] = $this->_iCMS['Gb79b']['iteration'];
$this->_iCMS['Gb79b']['index_prev'] = $this->_iCMS['Gb79b']['index'] - $this->_iCMS['Gb79b']['step'];
$this->_iCMS['Gb79b']['index_next'] = $this->_iCMS['Gb79b']['index'] + $this->_iCMS['Gb79b']['step'];
$this->_iCMS['Gb79b']['first']	  = ($this->_iCMS['Gb79b']['iteration'] == 1);
$this->_iCMS['Gb79b']['last']	   = ($this->_iCMS['Gb79b']['iteration'] == $this->_iCMS['Gb79b']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Gb79b'][$this->_iCMS['Gb79b']['index']],$this->_iCMS['Gb79b']);
?>
        <li><a href="<?php echo $this->_vars['list']['url']; ?>"><img src="<?php echo $this->_run_modifier($this->_vars['list']['pic'], 'small', 'plugin', 1, 110, 110); ?>" width="110" height="110" border="0" alt="<?php echo $this->_vars['list']['title']; ?>" /></a>
          <p><a href="<?php echo $this->_vars['list']['url']; ?>" title="<?php echo $this->_vars['list']['title']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 10); ?></a></p>
        </li>
        <?php }}?>
      </ul>
    </div>
  </div>
  <div class="blank9"> </div>
  <div class="area2"> <?php $this->_run_iCMS(array('loop' => 'true', 'type' => "top", 'attr' => "list", 'module' => "catalog"));  if (isset($this->_iCMS['Gba0d'])) unset($this->_iCMS['Gba0d']);
$this->_iCMS['Gba0d']['loop'] = is_array($this->_vars['catalog']) ? count($this->_vars['catalog']) : max(0, (int)$this->_vars['catalog']);
$this->_iCMS['_Gba0d']=$this->_vars['catalog'];
unset($this->_vars['catalog']);
$this->_iCMS['Gba0d']['show'] = true;
$this->_iCMS['Gba0d']['max'] = $this->_iCMS['Gba0d']['loop'];
$this->_iCMS['Gba0d']['step'] = 1;
$this->_iCMS['Gba0d']['start'] = $this->_iCMS['Gba0d']['step'] > 0 ? 0 : $this->_iCMS['Gba0d']['loop']-1;
if ($this->_iCMS['Gba0d']['show']) {
	$this->_iCMS['Gba0d']['total'] = $this->_iCMS['Gba0d']['loop'];
	if ($this->_iCMS['Gba0d']['total'] == 0){
		$this->_iCMS['Gba0d']['show'] = false;
	}
} else{
	$this->_iCMS['Gba0d']['total'] = 0;
}
if ($this->_iCMS['Gba0d']['show']){

		for ($this->_iCMS['Gba0d']['index'] = $this->_iCMS['Gba0d']['start'], $this->_iCMS['Gba0d']['iteration'] = 1;
			 $this->_iCMS['Gba0d']['iteration'] <= $this->_iCMS['Gba0d']['total'];
			 $this->_iCMS['Gba0d']['index'] += $this->_iCMS['Gba0d']['step'], $this->_iCMS['Gba0d']['iteration']++){
$this->_iCMS['Gba0d']['rownum'] = $this->_iCMS['Gba0d']['iteration'];
$this->_iCMS['Gba0d']['index_prev'] = $this->_iCMS['Gba0d']['index'] - $this->_iCMS['Gba0d']['step'];
$this->_iCMS['Gba0d']['index_next'] = $this->_iCMS['Gba0d']['index'] + $this->_iCMS['Gba0d']['step'];
$this->_iCMS['Gba0d']['first']	  = ($this->_iCMS['Gba0d']['iteration'] == 1);
$this->_iCMS['Gba0d']['last']	   = ($this->_iCMS['Gba0d']['iteration'] == $this->_iCMS['Gba0d']['total']);
$this->_vars['catalog']= array_merge($this->_iCMS['_Gba0d'][$this->_iCMS['Gba0d']['index']],$this->_iCMS['Gba0d']);
?>
    <div class="<?php echo tpl_function_cycle(array('values' => "col1,col2"), $this);?>">
      <div class="title">
        <h3><span><?php echo $this->_vars['catalog']['name']; ?></span></h3>
      </div>
      <div class="newsA"> <?php $this->_run_iCMS(array('loop' => 'true', 'sub' => "all", 'row' => "13", 'sortid' => $this->_vars['catalog']['id'], 'module' => "list"));  if (isset($this->_iCMS['Gbf22'])) unset($this->_iCMS['Gbf22']);
$this->_iCMS['Gbf22']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Gbf22']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Gbf22']['show'] = true;
$this->_iCMS['Gbf22']['max'] = $this->_iCMS['Gbf22']['loop'];
$this->_iCMS['Gbf22']['step'] = 1;
$this->_iCMS['Gbf22']['start'] = $this->_iCMS['Gbf22']['step'] > 0 ? 0 : $this->_iCMS['Gbf22']['loop']-1;
if ($this->_iCMS['Gbf22']['show']) {
	$this->_iCMS['Gbf22']['total'] = $this->_iCMS['Gbf22']['loop'];
	if ($this->_iCMS['Gbf22']['total'] == 0){
		$this->_iCMS['Gbf22']['show'] = false;
	}
} else{
	$this->_iCMS['Gbf22']['total'] = 0;
}
if ($this->_iCMS['Gbf22']['show']){

		for ($this->_iCMS['Gbf22']['index'] = $this->_iCMS['Gbf22']['start'], $this->_iCMS['Gbf22']['iteration'] = 1;
			 $this->_iCMS['Gbf22']['iteration'] <= $this->_iCMS['Gbf22']['total'];
			 $this->_iCMS['Gbf22']['index'] += $this->_iCMS['Gbf22']['step'], $this->_iCMS['Gbf22']['iteration']++){
$this->_iCMS['Gbf22']['rownum'] = $this->_iCMS['Gbf22']['iteration'];
$this->_iCMS['Gbf22']['index_prev'] = $this->_iCMS['Gbf22']['index'] - $this->_iCMS['Gbf22']['step'];
$this->_iCMS['Gbf22']['index_next'] = $this->_iCMS['Gbf22']['index'] + $this->_iCMS['Gbf22']['step'];
$this->_iCMS['Gbf22']['first']	  = ($this->_iCMS['Gbf22']['iteration'] == 1);
$this->_iCMS['Gbf22']['last']	   = ($this->_iCMS['Gbf22']['iteration'] == $this->_iCMS['Gbf22']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Gbf22'][$this->_iCMS['Gbf22']['index']],$this->_iCMS['Gbf22']);
?> <?php if ($this->_vars['list']['first']): ?>
        <div class="btitle">
          <h2><a href="<?php echo $this->_vars['list']['url']; ?>" title="<?php echo $this->_vars['list']['title']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 20); ?></a></h2>
          <p><?php echo $this->_run_modifier($this->_run_modifier($this->_vars['list']['description'], 'html2txt', 'plugin', 1), 'cut', 'plugin', 1, 50); ?></p>
        </div>
        <ul class="newsList">
          <?php else: ?>
          <li><a href="<?php echo $this->_vars['list']['url']; ?>" title="<?php echo $this->_vars['list']['title']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 20); ?></a> <span class="date">(<?php echo $this->_run_modifier($this->_vars['list']['pubdate'], 'date', 'plugin', 1, "Y/m/d"); ?>)</span></li>
          <?php if ($this->_vars['list']['rownum']%6 == 0): ?>
        </ul>
        <ul class="newsList">
          <?php endif; ?> <?php endif; ?> <?php }}?>
        </ul>
        <p class="alignR pRight"><a href="<?php echo $this->_vars['catalog']['url']; ?>" class="cBlue">更多<?php echo $this->_vars['catalog']['name']; ?>&gt;&gt;</a></p>
      </div>
      <div class="picA">
        <div class="title">
          <h4><?php echo $this->_vars['catalog']['name']; ?>精品推荐</h4>
        </div>
        <ul class="picList">
          <?php $this->_run_iCMS(array('loop' => 'true', 'sub' => "all", 'pic' => "true", 'row' => "3", 'sortid' => $this->_vars['catalog']['id'], 'module' => "list"));  if (isset($this->_iCMS['Gc4ba'])) unset($this->_iCMS['Gc4ba']);
$this->_iCMS['Gc4ba']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Gc4ba']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Gc4ba']['show'] = true;
$this->_iCMS['Gc4ba']['max'] = $this->_iCMS['Gc4ba']['loop'];
$this->_iCMS['Gc4ba']['step'] = 1;
$this->_iCMS['Gc4ba']['start'] = $this->_iCMS['Gc4ba']['step'] > 0 ? 0 : $this->_iCMS['Gc4ba']['loop']-1;
if ($this->_iCMS['Gc4ba']['show']) {
	$this->_iCMS['Gc4ba']['total'] = $this->_iCMS['Gc4ba']['loop'];
	if ($this->_iCMS['Gc4ba']['total'] == 0){
		$this->_iCMS['Gc4ba']['show'] = false;
	}
} else{
	$this->_iCMS['Gc4ba']['total'] = 0;
}
if ($this->_iCMS['Gc4ba']['show']){

		for ($this->_iCMS['Gc4ba']['index'] = $this->_iCMS['Gc4ba']['start'], $this->_iCMS['Gc4ba']['iteration'] = 1;
			 $this->_iCMS['Gc4ba']['iteration'] <= $this->_iCMS['Gc4ba']['total'];
			 $this->_iCMS['Gc4ba']['index'] += $this->_iCMS['Gc4ba']['step'], $this->_iCMS['Gc4ba']['iteration']++){
$this->_iCMS['Gc4ba']['rownum'] = $this->_iCMS['Gc4ba']['iteration'];
$this->_iCMS['Gc4ba']['index_prev'] = $this->_iCMS['Gc4ba']['index'] - $this->_iCMS['Gc4ba']['step'];
$this->_iCMS['Gc4ba']['index_next'] = $this->_iCMS['Gc4ba']['index'] + $this->_iCMS['Gc4ba']['step'];
$this->_iCMS['Gc4ba']['first']	  = ($this->_iCMS['Gc4ba']['iteration'] == 1);
$this->_iCMS['Gc4ba']['last']	   = ($this->_iCMS['Gc4ba']['iteration'] == $this->_iCMS['Gc4ba']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Gc4ba'][$this->_iCMS['Gc4ba']['index']],$this->_iCMS['Gc4ba']);
?>
          <li><a href="<?php echo $this->_vars['list']['url']; ?>"><img src="<?php echo $this->_run_modifier($this->_vars['list']['pic'], 'small', 'plugin', 1, 120, 90); ?>" width="120" height="90" border="0" alt="<?php echo $this->_vars['list']['title']; ?>" /></a>
            <p><a href="<?php echo $this->_vars['list']['url']; ?>" class="cBlue" title="<?php echo $this->_vars['list']['title']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 10); ?></a></p>
          </li>
          <?php }}?>
        </ul>
      </div>
    </div><?php if ($this->_vars['catalog']['rownum']%2 == 0): ?><div class="blank0"></div><?php endif; ?>
    <?php }}?> </div>
  <div class="blank9"> </div>
  <div class="flink">
    <div class="title1">
      <dl>
        <dt>友情链接 & 合作伙伴 </dt>
        <dd><a href="message.php">申请加入</a> </dd>
      </dl>
    </div>
    <div class="flinkcon">
      <ul>
        <?php $this->_run_iCMS(array('loop' => 'true', 'module' => "link"));  if (isset($this->_iCMS['Gc842'])) unset($this->_iCMS['Gc842']);
$this->_iCMS['Gc842']['loop'] = is_array($this->_vars['link']) ? count($this->_vars['link']) : max(0, (int)$this->_vars['link']);
$this->_iCMS['_Gc842']=$this->_vars['link'];
unset($this->_vars['link']);
$this->_iCMS['Gc842']['show'] = true;
$this->_iCMS['Gc842']['max'] = $this->_iCMS['Gc842']['loop'];
$this->_iCMS['Gc842']['step'] = 1;
$this->_iCMS['Gc842']['start'] = $this->_iCMS['Gc842']['step'] > 0 ? 0 : $this->_iCMS['Gc842']['loop']-1;
if ($this->_iCMS['Gc842']['show']) {
	$this->_iCMS['Gc842']['total'] = $this->_iCMS['Gc842']['loop'];
	if ($this->_iCMS['Gc842']['total'] == 0){
		$this->_iCMS['Gc842']['show'] = false;
	}
} else{
	$this->_iCMS['Gc842']['total'] = 0;
}
if ($this->_iCMS['Gc842']['show']){

		for ($this->_iCMS['Gc842']['index'] = $this->_iCMS['Gc842']['start'], $this->_iCMS['Gc842']['iteration'] = 1;
			 $this->_iCMS['Gc842']['iteration'] <= $this->_iCMS['Gc842']['total'];
			 $this->_iCMS['Gc842']['index'] += $this->_iCMS['Gc842']['step'], $this->_iCMS['Gc842']['iteration']++){
$this->_iCMS['Gc842']['rownum'] = $this->_iCMS['Gc842']['iteration'];
$this->_iCMS['Gc842']['index_prev'] = $this->_iCMS['Gc842']['index'] - $this->_iCMS['Gc842']['step'];
$this->_iCMS['Gc842']['index_next'] = $this->_iCMS['Gc842']['index'] + $this->_iCMS['Gc842']['step'];
$this->_iCMS['Gc842']['first']	  = ($this->_iCMS['Gc842']['iteration'] == 1);
$this->_iCMS['Gc842']['last']	   = ($this->_iCMS['Gc842']['iteration'] == $this->_iCMS['Gc842']['total']);
$this->_vars['link']= array_merge($this->_iCMS['_Gc842'][$this->_iCMS['Gc842']['index']],$this->_iCMS['Gc842']);
?><a href='<?php echo $this->_vars['link']['url']; ?>' target='_blank'><?php echo $this->_vars['link']['name']; ?></a><?php }}?>
      </ul>
    </div>
  </div>
</div>
<?php error_reporting(iCMS_TPL_BUG?E_ALL ^ E_NOTICE:0);!defined('iCMS') && exit('What are you doing?');?>
<div id="footer">
	<div id="copyright">&copy; <em><?php echo $this->_vars['site']['title']; ?></em></div>
</div>
</body>
</html>
