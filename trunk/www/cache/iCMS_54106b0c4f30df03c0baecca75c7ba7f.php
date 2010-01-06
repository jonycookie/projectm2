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
	    <?php $this->_run_iCMS(array('loop' => 'true', 'type' => 'top', 'module' => "catalog"));  if (isset($this->_iCMS['Gc6a5'])) unset($this->_iCMS['Gc6a5']);
$this->_iCMS['Gc6a5']['loop'] = is_array($this->_vars['catalog']) ? count($this->_vars['catalog']) : max(0, (int)$this->_vars['catalog']);
$this->_iCMS['_Gc6a5']=$this->_vars['catalog'];
unset($this->_vars['catalog']);
$this->_iCMS['Gc6a5']['show'] = true;
$this->_iCMS['Gc6a5']['max'] = $this->_iCMS['Gc6a5']['loop'];
$this->_iCMS['Gc6a5']['step'] = 1;
$this->_iCMS['Gc6a5']['start'] = $this->_iCMS['Gc6a5']['step'] > 0 ? 0 : $this->_iCMS['Gc6a5']['loop']-1;
if ($this->_iCMS['Gc6a5']['show']) {
	$this->_iCMS['Gc6a5']['total'] = $this->_iCMS['Gc6a5']['loop'];
	if ($this->_iCMS['Gc6a5']['total'] == 0){
		$this->_iCMS['Gc6a5']['show'] = false;
	}
} else{
	$this->_iCMS['Gc6a5']['total'] = 0;
}
if ($this->_iCMS['Gc6a5']['show']){

		for ($this->_iCMS['Gc6a5']['index'] = $this->_iCMS['Gc6a5']['start'], $this->_iCMS['Gc6a5']['iteration'] = 1;
			 $this->_iCMS['Gc6a5']['iteration'] <= $this->_iCMS['Gc6a5']['total'];
			 $this->_iCMS['Gc6a5']['index'] += $this->_iCMS['Gc6a5']['step'], $this->_iCMS['Gc6a5']['iteration']++){
$this->_iCMS['Gc6a5']['rownum'] = $this->_iCMS['Gc6a5']['iteration'];
$this->_iCMS['Gc6a5']['index_prev'] = $this->_iCMS['Gc6a5']['index'] - $this->_iCMS['Gc6a5']['step'];
$this->_iCMS['Gc6a5']['index_next'] = $this->_iCMS['Gc6a5']['index'] + $this->_iCMS['Gc6a5']['step'];
$this->_iCMS['Gc6a5']['first']	  = ($this->_iCMS['Gc6a5']['iteration'] == 1);
$this->_iCMS['Gc6a5']['last']	   = ($this->_iCMS['Gc6a5']['iteration'] == $this->_iCMS['Gc6a5']['total']);
$this->_vars['catalog']= array_merge($this->_iCMS['_Gc6a5'][$this->_iCMS['Gc6a5']['index']],$this->_iCMS['Gc6a5']);
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
  <div class="tag"> <?php $this->_run_iCMS(array('loop' => 'true', 'row' => '10', 'orderby' => 'hot', 'module' => "tag"));  if (isset($this->_iCMS['Gc923'])) unset($this->_iCMS['Gc923']);
$this->_iCMS['Gc923']['loop'] = is_array($this->_vars['tag']) ? count($this->_vars['tag']) : max(0, (int)$this->_vars['tag']);
$this->_iCMS['_Gc923']=$this->_vars['tag'];
unset($this->_vars['tag']);
$this->_iCMS['Gc923']['show'] = true;
$this->_iCMS['Gc923']['max'] = $this->_iCMS['Gc923']['loop'];
$this->_iCMS['Gc923']['step'] = 1;
$this->_iCMS['Gc923']['start'] = $this->_iCMS['Gc923']['step'] > 0 ? 0 : $this->_iCMS['Gc923']['loop']-1;
if ($this->_iCMS['Gc923']['show']) {
	$this->_iCMS['Gc923']['total'] = $this->_iCMS['Gc923']['loop'];
	if ($this->_iCMS['Gc923']['total'] == 0){
		$this->_iCMS['Gc923']['show'] = false;
	}
} else{
	$this->_iCMS['Gc923']['total'] = 0;
}
if ($this->_iCMS['Gc923']['show']){

		for ($this->_iCMS['Gc923']['index'] = $this->_iCMS['Gc923']['start'], $this->_iCMS['Gc923']['iteration'] = 1;
			 $this->_iCMS['Gc923']['iteration'] <= $this->_iCMS['Gc923']['total'];
			 $this->_iCMS['Gc923']['index'] += $this->_iCMS['Gc923']['step'], $this->_iCMS['Gc923']['iteration']++){
$this->_iCMS['Gc923']['rownum'] = $this->_iCMS['Gc923']['iteration'];
$this->_iCMS['Gc923']['index_prev'] = $this->_iCMS['Gc923']['index'] - $this->_iCMS['Gc923']['step'];
$this->_iCMS['Gc923']['index_next'] = $this->_iCMS['Gc923']['index'] + $this->_iCMS['Gc923']['step'];
$this->_iCMS['Gc923']['first']	  = ($this->_iCMS['Gc923']['iteration'] == 1);
$this->_iCMS['Gc923']['last']	   = ($this->_iCMS['Gc923']['iteration'] == $this->_iCMS['Gc923']['total']);
$this->_vars['tag']= array_merge($this->_iCMS['_Gc923'][$this->_iCMS['Gc923']['index']],$this->_iCMS['Gc923']);
?> <a href="<?php echo $this->_vars['tag']['url']; ?>" target="_blank"><?php echo $this->_vars['tag']['name']; ?></a> <?php }}?> </div>
</div>
<div id="container">
  <div class="area1">
    <div class="cleft"> <?php $this->_run_iCMS(array('loop' => 'true', 'pic' => 'true', 'row' => "7", 'module' => "list"));  if (isset($this->_iCMS['Gca90'])) unset($this->_iCMS['Gca90']);
$this->_iCMS['Gca90']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Gca90']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Gca90']['show'] = true;
$this->_iCMS['Gca90']['max'] = $this->_iCMS['Gca90']['loop'];
$this->_iCMS['Gca90']['step'] = 1;
$this->_iCMS['Gca90']['start'] = $this->_iCMS['Gca90']['step'] > 0 ? 0 : $this->_iCMS['Gca90']['loop']-1;
if ($this->_iCMS['Gca90']['show']) {
	$this->_iCMS['Gca90']['total'] = $this->_iCMS['Gca90']['loop'];
	if ($this->_iCMS['Gca90']['total'] == 0){
		$this->_iCMS['Gca90']['show'] = false;
	}
} else{
	$this->_iCMS['Gca90']['total'] = 0;
}
if ($this->_iCMS['Gca90']['show']){

		for ($this->_iCMS['Gca90']['index'] = $this->_iCMS['Gca90']['start'], $this->_iCMS['Gca90']['iteration'] = 1;
			 $this->_iCMS['Gca90']['iteration'] <= $this->_iCMS['Gca90']['total'];
			 $this->_iCMS['Gca90']['index'] += $this->_iCMS['Gca90']['step'], $this->_iCMS['Gca90']['iteration']++){
$this->_iCMS['Gca90']['rownum'] = $this->_iCMS['Gca90']['iteration'];
$this->_iCMS['Gca90']['index_prev'] = $this->_iCMS['Gca90']['index'] - $this->_iCMS['Gca90']['step'];
$this->_iCMS['Gca90']['index_next'] = $this->_iCMS['Gca90']['index'] + $this->_iCMS['Gca90']['step'];
$this->_iCMS['Gca90']['first']	  = ($this->_iCMS['Gca90']['iteration'] == 1);
$this->_iCMS['Gca90']['last']	   = ($this->_iCMS['Gca90']['iteration'] == $this->_iCMS['Gca90']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Gca90'][$this->_iCMS['Gca90']['index']],$this->_iCMS['Gca90']);
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
    <div class="cright"> <?php $this->_run_iCMS(array('loop' => 'true', 'pic' => 'true', 'row' => "7", 'module' => "list"));  if (isset($this->_iCMS['Gd0ef'])) unset($this->_iCMS['Gd0ef']);
$this->_iCMS['Gd0ef']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Gd0ef']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Gd0ef']['show'] = true;
$this->_iCMS['Gd0ef']['max'] = $this->_iCMS['Gd0ef']['loop'];
$this->_iCMS['Gd0ef']['step'] = 1;
$this->_iCMS['Gd0ef']['start'] = $this->_iCMS['Gd0ef']['step'] > 0 ? 0 : $this->_iCMS['Gd0ef']['loop']-1;
if ($this->_iCMS['Gd0ef']['show']) {
	$this->_iCMS['Gd0ef']['total'] = $this->_iCMS['Gd0ef']['loop'];
	if ($this->_iCMS['Gd0ef']['total'] == 0){
		$this->_iCMS['Gd0ef']['show'] = false;
	}
} else{
	$this->_iCMS['Gd0ef']['total'] = 0;
}
if ($this->_iCMS['Gd0ef']['show']){

		for ($this->_iCMS['Gd0ef']['index'] = $this->_iCMS['Gd0ef']['start'], $this->_iCMS['Gd0ef']['iteration'] = 1;
			 $this->_iCMS['Gd0ef']['iteration'] <= $this->_iCMS['Gd0ef']['total'];
			 $this->_iCMS['Gd0ef']['index'] += $this->_iCMS['Gd0ef']['step'], $this->_iCMS['Gd0ef']['iteration']++){
$this->_iCMS['Gd0ef']['rownum'] = $this->_iCMS['Gd0ef']['iteration'];
$this->_iCMS['Gd0ef']['index_prev'] = $this->_iCMS['Gd0ef']['index'] - $this->_iCMS['Gd0ef']['step'];
$this->_iCMS['Gd0ef']['index_next'] = $this->_iCMS['Gd0ef']['index'] + $this->_iCMS['Gd0ef']['step'];
$this->_iCMS['Gd0ef']['first']	  = ($this->_iCMS['Gd0ef']['iteration'] == 1);
$this->_iCMS['Gd0ef']['last']	   = ($this->_iCMS['Gd0ef']['iteration'] == $this->_iCMS['Gd0ef']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Gd0ef'][$this->_iCMS['Gd0ef']['index']],$this->_iCMS['Gd0ef']);
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
    <div class="colist"> <?php $this->_run_iCMS(array('loop' => 'true', 'type' => "top", 'row' => "4", 'module' => "catalog"));  if (isset($this->_iCMS['Gd43c'])) unset($this->_iCMS['Gd43c']);
$this->_iCMS['Gd43c']['loop'] = is_array($this->_vars['catalog']) ? count($this->_vars['catalog']) : max(0, (int)$this->_vars['catalog']);
$this->_iCMS['_Gd43c']=$this->_vars['catalog'];
unset($this->_vars['catalog']);
$this->_iCMS['Gd43c']['show'] = true;
$this->_iCMS['Gd43c']['max'] = $this->_iCMS['Gd43c']['loop'];
$this->_iCMS['Gd43c']['step'] = 1;
$this->_iCMS['Gd43c']['start'] = $this->_iCMS['Gd43c']['step'] > 0 ? 0 : $this->_iCMS['Gd43c']['loop']-1;
if ($this->_iCMS['Gd43c']['show']) {
	$this->_iCMS['Gd43c']['total'] = $this->_iCMS['Gd43c']['loop'];
	if ($this->_iCMS['Gd43c']['total'] == 0){
		$this->_iCMS['Gd43c']['show'] = false;
	}
} else{
	$this->_iCMS['Gd43c']['total'] = 0;
}
if ($this->_iCMS['Gd43c']['show']){

		for ($this->_iCMS['Gd43c']['index'] = $this->_iCMS['Gd43c']['start'], $this->_iCMS['Gd43c']['iteration'] = 1;
			 $this->_iCMS['Gd43c']['iteration'] <= $this->_iCMS['Gd43c']['total'];
			 $this->_iCMS['Gd43c']['index'] += $this->_iCMS['Gd43c']['step'], $this->_iCMS['Gd43c']['iteration']++){
$this->_iCMS['Gd43c']['rownum'] = $this->_iCMS['Gd43c']['iteration'];
$this->_iCMS['Gd43c']['index_prev'] = $this->_iCMS['Gd43c']['index'] - $this->_iCMS['Gd43c']['step'];
$this->_iCMS['Gd43c']['index_next'] = $this->_iCMS['Gd43c']['index'] + $this->_iCMS['Gd43c']['step'];
$this->_iCMS['Gd43c']['first']	  = ($this->_iCMS['Gd43c']['iteration'] == 1);
$this->_iCMS['Gd43c']['last']	   = ($this->_iCMS['Gd43c']['iteration'] == $this->_iCMS['Gd43c']['total']);
$this->_vars['catalog']= array_merge($this->_iCMS['_Gd43c'][$this->_iCMS['Gd43c']['index']],$this->_iCMS['Gd43c']);
?>
      <div class="<?php echo tpl_function_cycle(array('values' => "col1,col2"), $this);?>">
        <div class="title">
          <h3><span><?php echo $this->_vars['catalog']['name']; ?></span></h3>
        </div>
        <ul class="newsList">
          <?php $this->_run_iCMS(array('loop' => 'true', 'sub' => "all", 'row' => "10", 'sortid' => $this->_vars['catalog']['id'], 'module' => "list"));  if (isset($this->_iCMS['Gdb41'])) unset($this->_iCMS['Gdb41']);
$this->_iCMS['Gdb41']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Gdb41']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Gdb41']['show'] = true;
$this->_iCMS['Gdb41']['max'] = $this->_iCMS['Gdb41']['loop'];
$this->_iCMS['Gdb41']['step'] = 1;
$this->_iCMS['Gdb41']['start'] = $this->_iCMS['Gdb41']['step'] > 0 ? 0 : $this->_iCMS['Gdb41']['loop']-1;
if ($this->_iCMS['Gdb41']['show']) {
	$this->_iCMS['Gdb41']['total'] = $this->_iCMS['Gdb41']['loop'];
	if ($this->_iCMS['Gdb41']['total'] == 0){
		$this->_iCMS['Gdb41']['show'] = false;
	}
} else{
	$this->_iCMS['Gdb41']['total'] = 0;
}
if ($this->_iCMS['Gdb41']['show']){

		for ($this->_iCMS['Gdb41']['index'] = $this->_iCMS['Gdb41']['start'], $this->_iCMS['Gdb41']['iteration'] = 1;
			 $this->_iCMS['Gdb41']['iteration'] <= $this->_iCMS['Gdb41']['total'];
			 $this->_iCMS['Gdb41']['index'] += $this->_iCMS['Gdb41']['step'], $this->_iCMS['Gdb41']['iteration']++){
$this->_iCMS['Gdb41']['rownum'] = $this->_iCMS['Gdb41']['iteration'];
$this->_iCMS['Gdb41']['index_prev'] = $this->_iCMS['Gdb41']['index'] - $this->_iCMS['Gdb41']['step'];
$this->_iCMS['Gdb41']['index_next'] = $this->_iCMS['Gdb41']['index'] + $this->_iCMS['Gdb41']['step'];
$this->_iCMS['Gdb41']['first']	  = ($this->_iCMS['Gdb41']['iteration'] == 1);
$this->_iCMS['Gdb41']['last']	   = ($this->_iCMS['Gdb41']['iteration'] == $this->_iCMS['Gdb41']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Gdb41'][$this->_iCMS['Gdb41']['index']],$this->_iCMS['Gdb41']);
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
        <dd> <?php $this->_run_iCMS(array('loop' => 'true', 'row' => "7", 'module' => "list"));  if (isset($this->_iCMS['Gdebb'])) unset($this->_iCMS['Gdebb']);
$this->_iCMS['Gdebb']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Gdebb']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Gdebb']['show'] = true;
$this->_iCMS['Gdebb']['max'] = $this->_iCMS['Gdebb']['loop'];
$this->_iCMS['Gdebb']['step'] = 1;
$this->_iCMS['Gdebb']['start'] = $this->_iCMS['Gdebb']['step'] > 0 ? 0 : $this->_iCMS['Gdebb']['loop']-1;
if ($this->_iCMS['Gdebb']['show']) {
	$this->_iCMS['Gdebb']['total'] = $this->_iCMS['Gdebb']['loop'];
	if ($this->_iCMS['Gdebb']['total'] == 0){
		$this->_iCMS['Gdebb']['show'] = false;
	}
} else{
	$this->_iCMS['Gdebb']['total'] = 0;
}
if ($this->_iCMS['Gdebb']['show']){

		for ($this->_iCMS['Gdebb']['index'] = $this->_iCMS['Gdebb']['start'], $this->_iCMS['Gdebb']['iteration'] = 1;
			 $this->_iCMS['Gdebb']['iteration'] <= $this->_iCMS['Gdebb']['total'];
			 $this->_iCMS['Gdebb']['index'] += $this->_iCMS['Gdebb']['step'], $this->_iCMS['Gdebb']['iteration']++){
$this->_iCMS['Gdebb']['rownum'] = $this->_iCMS['Gdebb']['iteration'];
$this->_iCMS['Gdebb']['index_prev'] = $this->_iCMS['Gdebb']['index'] - $this->_iCMS['Gdebb']['step'];
$this->_iCMS['Gdebb']['index_next'] = $this->_iCMS['Gdebb']['index'] + $this->_iCMS['Gdebb']['step'];
$this->_iCMS['Gdebb']['first']	  = ($this->_iCMS['Gdebb']['iteration'] == 1);
$this->_iCMS['Gdebb']['last']	   = ($this->_iCMS['Gdebb']['iteration'] == $this->_iCMS['Gdebb']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Gdebb'][$this->_iCMS['Gdebb']['index']],$this->_iCMS['Gdebb']);
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
        <?php $this->_run_iCMS(array('loop' => 'true', 'pic' => 'true', 'sub' => "all", 'row' => '7', 'orderby' => "pubdate", 'module' => "list"));  if (isset($this->_iCMS['Ge21f'])) unset($this->_iCMS['Ge21f']);
$this->_iCMS['Ge21f']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Ge21f']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Ge21f']['show'] = true;
$this->_iCMS['Ge21f']['max'] = $this->_iCMS['Ge21f']['loop'];
$this->_iCMS['Ge21f']['step'] = 1;
$this->_iCMS['Ge21f']['start'] = $this->_iCMS['Ge21f']['step'] > 0 ? 0 : $this->_iCMS['Ge21f']['loop']-1;
if ($this->_iCMS['Ge21f']['show']) {
	$this->_iCMS['Ge21f']['total'] = $this->_iCMS['Ge21f']['loop'];
	if ($this->_iCMS['Ge21f']['total'] == 0){
		$this->_iCMS['Ge21f']['show'] = false;
	}
} else{
	$this->_iCMS['Ge21f']['total'] = 0;
}
if ($this->_iCMS['Ge21f']['show']){

		for ($this->_iCMS['Ge21f']['index'] = $this->_iCMS['Ge21f']['start'], $this->_iCMS['Ge21f']['iteration'] = 1;
			 $this->_iCMS['Ge21f']['iteration'] <= $this->_iCMS['Ge21f']['total'];
			 $this->_iCMS['Ge21f']['index'] += $this->_iCMS['Ge21f']['step'], $this->_iCMS['Ge21f']['iteration']++){
$this->_iCMS['Ge21f']['rownum'] = $this->_iCMS['Ge21f']['iteration'];
$this->_iCMS['Ge21f']['index_prev'] = $this->_iCMS['Ge21f']['index'] - $this->_iCMS['Ge21f']['step'];
$this->_iCMS['Ge21f']['index_next'] = $this->_iCMS['Ge21f']['index'] + $this->_iCMS['Ge21f']['step'];
$this->_iCMS['Ge21f']['first']	  = ($this->_iCMS['Ge21f']['iteration'] == 1);
$this->_iCMS['Ge21f']['last']	   = ($this->_iCMS['Ge21f']['iteration'] == $this->_iCMS['Ge21f']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Ge21f'][$this->_iCMS['Ge21f']['index']],$this->_iCMS['Ge21f']);
?>
        <li><a href="<?php echo $this->_vars['list']['url']; ?>"><img src="<?php echo $this->_run_modifier($this->_vars['list']['pic'], 'small', 'plugin', 1, 110, 110); ?>" width="110" height="110" border="0" alt="<?php echo $this->_vars['list']['title']; ?>" /></a>
          <p><a href="<?php echo $this->_vars['list']['url']; ?>" title="<?php echo $this->_vars['list']['title']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 10); ?></a></p>
        </li>
        <?php }}?>
      </ul>
    </div>
  </div>
  <div class="blank9"> </div>
  <div class="area2"> <?php $this->_run_iCMS(array('loop' => 'true', 'type' => "top", 'attr' => "list", 'module' => "catalog"));  if (isset($this->_iCMS['Ge491'])) unset($this->_iCMS['Ge491']);
$this->_iCMS['Ge491']['loop'] = is_array($this->_vars['catalog']) ? count($this->_vars['catalog']) : max(0, (int)$this->_vars['catalog']);
$this->_iCMS['_Ge491']=$this->_vars['catalog'];
unset($this->_vars['catalog']);
$this->_iCMS['Ge491']['show'] = true;
$this->_iCMS['Ge491']['max'] = $this->_iCMS['Ge491']['loop'];
$this->_iCMS['Ge491']['step'] = 1;
$this->_iCMS['Ge491']['start'] = $this->_iCMS['Ge491']['step'] > 0 ? 0 : $this->_iCMS['Ge491']['loop']-1;
if ($this->_iCMS['Ge491']['show']) {
	$this->_iCMS['Ge491']['total'] = $this->_iCMS['Ge491']['loop'];
	if ($this->_iCMS['Ge491']['total'] == 0){
		$this->_iCMS['Ge491']['show'] = false;
	}
} else{
	$this->_iCMS['Ge491']['total'] = 0;
}
if ($this->_iCMS['Ge491']['show']){

		for ($this->_iCMS['Ge491']['index'] = $this->_iCMS['Ge491']['start'], $this->_iCMS['Ge491']['iteration'] = 1;
			 $this->_iCMS['Ge491']['iteration'] <= $this->_iCMS['Ge491']['total'];
			 $this->_iCMS['Ge491']['index'] += $this->_iCMS['Ge491']['step'], $this->_iCMS['Ge491']['iteration']++){
$this->_iCMS['Ge491']['rownum'] = $this->_iCMS['Ge491']['iteration'];
$this->_iCMS['Ge491']['index_prev'] = $this->_iCMS['Ge491']['index'] - $this->_iCMS['Ge491']['step'];
$this->_iCMS['Ge491']['index_next'] = $this->_iCMS['Ge491']['index'] + $this->_iCMS['Ge491']['step'];
$this->_iCMS['Ge491']['first']	  = ($this->_iCMS['Ge491']['iteration'] == 1);
$this->_iCMS['Ge491']['last']	   = ($this->_iCMS['Ge491']['iteration'] == $this->_iCMS['Ge491']['total']);
$this->_vars['catalog']= array_merge($this->_iCMS['_Ge491'][$this->_iCMS['Ge491']['index']],$this->_iCMS['Ge491']);
?>
    <div class="<?php echo tpl_function_cycle(array('values' => "col1,col2"), $this);?>">
      <div class="title">
        <h3><span><?php echo $this->_vars['catalog']['name']; ?></span></h3>
      </div>
      <div class="newsA"> <?php $this->_run_iCMS(array('loop' => 'true', 'sub' => "all", 'row' => "13", 'sortid' => $this->_vars['catalog']['id'], 'module' => "list"));  if (isset($this->_iCMS['Ge8b8'])) unset($this->_iCMS['Ge8b8']);
$this->_iCMS['Ge8b8']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Ge8b8']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Ge8b8']['show'] = true;
$this->_iCMS['Ge8b8']['max'] = $this->_iCMS['Ge8b8']['loop'];
$this->_iCMS['Ge8b8']['step'] = 1;
$this->_iCMS['Ge8b8']['start'] = $this->_iCMS['Ge8b8']['step'] > 0 ? 0 : $this->_iCMS['Ge8b8']['loop']-1;
if ($this->_iCMS['Ge8b8']['show']) {
	$this->_iCMS['Ge8b8']['total'] = $this->_iCMS['Ge8b8']['loop'];
	if ($this->_iCMS['Ge8b8']['total'] == 0){
		$this->_iCMS['Ge8b8']['show'] = false;
	}
} else{
	$this->_iCMS['Ge8b8']['total'] = 0;
}
if ($this->_iCMS['Ge8b8']['show']){

		for ($this->_iCMS['Ge8b8']['index'] = $this->_iCMS['Ge8b8']['start'], $this->_iCMS['Ge8b8']['iteration'] = 1;
			 $this->_iCMS['Ge8b8']['iteration'] <= $this->_iCMS['Ge8b8']['total'];
			 $this->_iCMS['Ge8b8']['index'] += $this->_iCMS['Ge8b8']['step'], $this->_iCMS['Ge8b8']['iteration']++){
$this->_iCMS['Ge8b8']['rownum'] = $this->_iCMS['Ge8b8']['iteration'];
$this->_iCMS['Ge8b8']['index_prev'] = $this->_iCMS['Ge8b8']['index'] - $this->_iCMS['Ge8b8']['step'];
$this->_iCMS['Ge8b8']['index_next'] = $this->_iCMS['Ge8b8']['index'] + $this->_iCMS['Ge8b8']['step'];
$this->_iCMS['Ge8b8']['first']	  = ($this->_iCMS['Ge8b8']['iteration'] == 1);
$this->_iCMS['Ge8b8']['last']	   = ($this->_iCMS['Ge8b8']['iteration'] == $this->_iCMS['Ge8b8']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Ge8b8'][$this->_iCMS['Ge8b8']['index']],$this->_iCMS['Ge8b8']);
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
          <?php $this->_run_iCMS(array('loop' => 'true', 'sub' => "all", 'pic' => "true", 'row' => "3", 'sortid' => $this->_vars['catalog']['id'], 'module' => "list"));  if (isset($this->_iCMS['Geeba'])) unset($this->_iCMS['Geeba']);
$this->_iCMS['Geeba']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_Geeba']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['Geeba']['show'] = true;
$this->_iCMS['Geeba']['max'] = $this->_iCMS['Geeba']['loop'];
$this->_iCMS['Geeba']['step'] = 1;
$this->_iCMS['Geeba']['start'] = $this->_iCMS['Geeba']['step'] > 0 ? 0 : $this->_iCMS['Geeba']['loop']-1;
if ($this->_iCMS['Geeba']['show']) {
	$this->_iCMS['Geeba']['total'] = $this->_iCMS['Geeba']['loop'];
	if ($this->_iCMS['Geeba']['total'] == 0){
		$this->_iCMS['Geeba']['show'] = false;
	}
} else{
	$this->_iCMS['Geeba']['total'] = 0;
}
if ($this->_iCMS['Geeba']['show']){

		for ($this->_iCMS['Geeba']['index'] = $this->_iCMS['Geeba']['start'], $this->_iCMS['Geeba']['iteration'] = 1;
			 $this->_iCMS['Geeba']['iteration'] <= $this->_iCMS['Geeba']['total'];
			 $this->_iCMS['Geeba']['index'] += $this->_iCMS['Geeba']['step'], $this->_iCMS['Geeba']['iteration']++){
$this->_iCMS['Geeba']['rownum'] = $this->_iCMS['Geeba']['iteration'];
$this->_iCMS['Geeba']['index_prev'] = $this->_iCMS['Geeba']['index'] - $this->_iCMS['Geeba']['step'];
$this->_iCMS['Geeba']['index_next'] = $this->_iCMS['Geeba']['index'] + $this->_iCMS['Geeba']['step'];
$this->_iCMS['Geeba']['first']	  = ($this->_iCMS['Geeba']['iteration'] == 1);
$this->_iCMS['Geeba']['last']	   = ($this->_iCMS['Geeba']['iteration'] == $this->_iCMS['Geeba']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_Geeba'][$this->_iCMS['Geeba']['index']],$this->_iCMS['Geeba']);
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
        <?php $this->_run_iCMS(array('loop' => 'true', 'module' => "link"));  if (isset($this->_iCMS['Gf1ce'])) unset($this->_iCMS['Gf1ce']);
$this->_iCMS['Gf1ce']['loop'] = is_array($this->_vars['link']) ? count($this->_vars['link']) : max(0, (int)$this->_vars['link']);
$this->_iCMS['_Gf1ce']=$this->_vars['link'];
unset($this->_vars['link']);
$this->_iCMS['Gf1ce']['show'] = true;
$this->_iCMS['Gf1ce']['max'] = $this->_iCMS['Gf1ce']['loop'];
$this->_iCMS['Gf1ce']['step'] = 1;
$this->_iCMS['Gf1ce']['start'] = $this->_iCMS['Gf1ce']['step'] > 0 ? 0 : $this->_iCMS['Gf1ce']['loop']-1;
if ($this->_iCMS['Gf1ce']['show']) {
	$this->_iCMS['Gf1ce']['total'] = $this->_iCMS['Gf1ce']['loop'];
	if ($this->_iCMS['Gf1ce']['total'] == 0){
		$this->_iCMS['Gf1ce']['show'] = false;
	}
} else{
	$this->_iCMS['Gf1ce']['total'] = 0;
}
if ($this->_iCMS['Gf1ce']['show']){

		for ($this->_iCMS['Gf1ce']['index'] = $this->_iCMS['Gf1ce']['start'], $this->_iCMS['Gf1ce']['iteration'] = 1;
			 $this->_iCMS['Gf1ce']['iteration'] <= $this->_iCMS['Gf1ce']['total'];
			 $this->_iCMS['Gf1ce']['index'] += $this->_iCMS['Gf1ce']['step'], $this->_iCMS['Gf1ce']['iteration']++){
$this->_iCMS['Gf1ce']['rownum'] = $this->_iCMS['Gf1ce']['iteration'];
$this->_iCMS['Gf1ce']['index_prev'] = $this->_iCMS['Gf1ce']['index'] - $this->_iCMS['Gf1ce']['step'];
$this->_iCMS['Gf1ce']['index_next'] = $this->_iCMS['Gf1ce']['index'] + $this->_iCMS['Gf1ce']['step'];
$this->_iCMS['Gf1ce']['first']	  = ($this->_iCMS['Gf1ce']['iteration'] == 1);
$this->_iCMS['Gf1ce']['last']	   = ($this->_iCMS['Gf1ce']['iteration'] == $this->_iCMS['Gf1ce']['total']);
$this->_vars['link']= array_merge($this->_iCMS['_Gf1ce'][$this->_iCMS['Gf1ce']['index']],$this->_iCMS['Gf1ce']);
?><a href='<?php echo $this->_vars['link']['url']; ?>' target='_blank'><?php echo $this->_vars['link']['name']; ?></a><?php }}?>
      </ul>
    </div>
  </div>
</div>
<?php error_reporting(iCMS_TPL_BUG?E_ALL ^ E_NOTICE:0);!defined('iCMS') && exit('What are you doing?');?>
<div id="sitemap">
	<div class="inner">
	<ul>
		<li>
			<h4>娱乐</h4>
			<ul>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
			</ul>
		</li>
		<li>
			<h4>娱乐</h4>
			<ul>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
			</ul>
		</li>
		<li>
			<h4>娱乐</h4>
			<ul>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
			</ul>
		</li>
		<li>
			<h4>娱乐</h4>
			<ul>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
				<li><a href="#">受到了罚款速度</a></li>
			</ul>
		</li>
	</ul>
	</div>
</div>

<div id="copyright">&copy; <em><?php echo $this->_vars['site']['title']; ?></em></div>

</body>
</html>
