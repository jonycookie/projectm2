<?php error_reporting(iCMS_TPL_BUG?E_ALL ^ E_NOTICE:0);!defined('iCMS') && exit('What are you doing?');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_vars['sort']['name']; ?>-<?php echo $this->_vars['site']['title']; ?>-<?php echo $this->_vars['site']['seotitle']; ?></title>
<meta name="keywords" content="<?php echo $this->_vars['sort']['keywords']; ?>">
<meta name="description" content="<?php echo $this->_run_modifier($this->_vars['sort']['description'], 'html2txt', 'plugin', 1); ?>">
<meta name="copyright" content="<?php echo $this->_vars['site']['title']; ?>" />
<link href="<?php echo $this->_vars['site']['dir']; ?>/favicon.ico" rel="shortcut icon" type="image/x-icon" />
<link href="<?php echo $this->_vars['site']['dir']; ?>/skins/global.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->_vars['site']['dir']; ?>/skins/article.css" rel="stylesheet" type="text/css" />
<?php $this->_run_iCMS(array('module' => "javascript")); ?>
</head>
<body>
<?php error_reporting(iCMS_TPL_BUG?E_ALL ^ E_NOTICE:0);!defined('iCMS') && exit('What are you doing?');?>
<div id="toolbar"><a href="<?php echo $this->_vars['site']['url']; ?>/register.php" target="_blank">注册</a> <a href="<?php echo $this->_vars['site']['url']; ?>/usercp.php" target="_blank">登陆</a></div>
<div id="header">
	<div class="r"><?php $this->_run_iCMS(array('name' => "全站顶部广告", 'module' => "advertise")); ?></div>
	<div id="logo"><img src="<?php echo $this->_vars['site']['tplurl']; ?>/images/logo.gif" /></div>
	<div id="catalogs">
	  <ul>
	    <li><a href="<?php echo $this->_vars['site']['url']; ?>">首页</a></li>
	    <?php $this->_run_iCMS(array('loop' => 'true', 'type' => 'top', 'module' => "catalog"));  if (isset($this->_iCMS['G802f'])) unset($this->_iCMS['G802f']);
$this->_iCMS['G802f']['loop'] = is_array($this->_vars['catalog']) ? count($this->_vars['catalog']) : max(0, (int)$this->_vars['catalog']);
$this->_iCMS['_G802f']=$this->_vars['catalog'];
unset($this->_vars['catalog']);
$this->_iCMS['G802f']['show'] = true;
$this->_iCMS['G802f']['max'] = $this->_iCMS['G802f']['loop'];
$this->_iCMS['G802f']['step'] = 1;
$this->_iCMS['G802f']['start'] = $this->_iCMS['G802f']['step'] > 0 ? 0 : $this->_iCMS['G802f']['loop']-1;
if ($this->_iCMS['G802f']['show']) {
	$this->_iCMS['G802f']['total'] = $this->_iCMS['G802f']['loop'];
	if ($this->_iCMS['G802f']['total'] == 0){
		$this->_iCMS['G802f']['show'] = false;
	}
} else{
	$this->_iCMS['G802f']['total'] = 0;
}
if ($this->_iCMS['G802f']['show']){

		for ($this->_iCMS['G802f']['index'] = $this->_iCMS['G802f']['start'], $this->_iCMS['G802f']['iteration'] = 1;
			 $this->_iCMS['G802f']['iteration'] <= $this->_iCMS['G802f']['total'];
			 $this->_iCMS['G802f']['index'] += $this->_iCMS['G802f']['step'], $this->_iCMS['G802f']['iteration']++){
$this->_iCMS['G802f']['rownum'] = $this->_iCMS['G802f']['iteration'];
$this->_iCMS['G802f']['index_prev'] = $this->_iCMS['G802f']['index'] - $this->_iCMS['G802f']['step'];
$this->_iCMS['G802f']['index_next'] = $this->_iCMS['G802f']['index'] + $this->_iCMS['G802f']['step'];
$this->_iCMS['G802f']['first']	  = ($this->_iCMS['G802f']['iteration'] == 1);
$this->_iCMS['G802f']['last']	   = ($this->_iCMS['G802f']['iteration'] == $this->_iCMS['G802f']['total']);
$this->_vars['catalog']= array_merge($this->_iCMS['_G802f'][$this->_iCMS['G802f']['index']],$this->_iCMS['G802f']);
?>
	    <li id="catalog_<?php echo $this->_vars['catalog']['id']; ?>" <?php if ($this->_vars['sort']['id'] == $this->_vars['catalog']['id']): ?>class="active"<?php endif; ?>><a href="<?php echo $this->_vars['catalog']['url']; ?>"><?php echo $this->_vars['catalog']['name']; ?></a></li>
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
  <div class="tag"> <?php $this->_run_iCMS(array('loop' => 'true', 'row' => '10', 'orderby' => 'hot', 'module' => "tag"));  if (isset($this->_iCMS['G8852'])) unset($this->_iCMS['G8852']);
$this->_iCMS['G8852']['loop'] = is_array($this->_vars['tag']) ? count($this->_vars['tag']) : max(0, (int)$this->_vars['tag']);
$this->_iCMS['_G8852']=$this->_vars['tag'];
unset($this->_vars['tag']);
$this->_iCMS['G8852']['show'] = true;
$this->_iCMS['G8852']['max'] = $this->_iCMS['G8852']['loop'];
$this->_iCMS['G8852']['step'] = 1;
$this->_iCMS['G8852']['start'] = $this->_iCMS['G8852']['step'] > 0 ? 0 : $this->_iCMS['G8852']['loop']-1;
if ($this->_iCMS['G8852']['show']) {
	$this->_iCMS['G8852']['total'] = $this->_iCMS['G8852']['loop'];
	if ($this->_iCMS['G8852']['total'] == 0){
		$this->_iCMS['G8852']['show'] = false;
	}
} else{
	$this->_iCMS['G8852']['total'] = 0;
}
if ($this->_iCMS['G8852']['show']){

		for ($this->_iCMS['G8852']['index'] = $this->_iCMS['G8852']['start'], $this->_iCMS['G8852']['iteration'] = 1;
			 $this->_iCMS['G8852']['iteration'] <= $this->_iCMS['G8852']['total'];
			 $this->_iCMS['G8852']['index'] += $this->_iCMS['G8852']['step'], $this->_iCMS['G8852']['iteration']++){
$this->_iCMS['G8852']['rownum'] = $this->_iCMS['G8852']['iteration'];
$this->_iCMS['G8852']['index_prev'] = $this->_iCMS['G8852']['index'] - $this->_iCMS['G8852']['step'];
$this->_iCMS['G8852']['index_next'] = $this->_iCMS['G8852']['index'] + $this->_iCMS['G8852']['step'];
$this->_iCMS['G8852']['first']	  = ($this->_iCMS['G8852']['iteration'] == 1);
$this->_iCMS['G8852']['last']	   = ($this->_iCMS['G8852']['iteration'] == $this->_iCMS['G8852']['total']);
$this->_vars['tag']= array_merge($this->_iCMS['_G8852'][$this->_iCMS['G8852']['index']],$this->_iCMS['G8852']);
?> <a href="<?php echo $this->_vars['tag']['url']; ?>" target="_blank"><?php echo $this->_vars['tag']['name']; ?></a> <?php }}?> </div>
</div>

<div id="container">
  <div id="main"> <!--左侧-->
    <div class="pleft"> <!--位置导航-->
      <div class="thisplace">
        <div class="title"><?php echo $this->_vars['sort']['name']; ?></div>
        <div class="placenav"> <span>当前位置 :</span><a href="<?php echo $this->_vars['site']['index']; ?>">首页</a> &gt; <?php echo $this->_vars['sort']['nav']; ?> > 列表 </div>
      </div>
      <!--图片新闻-->
      <div class="picnews margintop">
        <dl>
          <?php $this->_run_iCMS(array('loop' => 'true', 'pic' => 'true', 'row' => '4', 'orderby' => "pubdate", 'sortid' => $this->_vars['sort']['id'], 'module' => "list"));  if (isset($this->_iCMS['G3a02'])) unset($this->_iCMS['G3a02']);
$this->_iCMS['G3a02']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_G3a02']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['G3a02']['show'] = true;
$this->_iCMS['G3a02']['max'] = $this->_iCMS['G3a02']['loop'];
$this->_iCMS['G3a02']['step'] = 1;
$this->_iCMS['G3a02']['start'] = $this->_iCMS['G3a02']['step'] > 0 ? 0 : $this->_iCMS['G3a02']['loop']-1;
if ($this->_iCMS['G3a02']['show']) {
	$this->_iCMS['G3a02']['total'] = $this->_iCMS['G3a02']['loop'];
	if ($this->_iCMS['G3a02']['total'] == 0){
		$this->_iCMS['G3a02']['show'] = false;
	}
} else{
	$this->_iCMS['G3a02']['total'] = 0;
}
if ($this->_iCMS['G3a02']['show']){

		for ($this->_iCMS['G3a02']['index'] = $this->_iCMS['G3a02']['start'], $this->_iCMS['G3a02']['iteration'] = 1;
			 $this->_iCMS['G3a02']['iteration'] <= $this->_iCMS['G3a02']['total'];
			 $this->_iCMS['G3a02']['index'] += $this->_iCMS['G3a02']['step'], $this->_iCMS['G3a02']['iteration']++){
$this->_iCMS['G3a02']['rownum'] = $this->_iCMS['G3a02']['iteration'];
$this->_iCMS['G3a02']['index_prev'] = $this->_iCMS['G3a02']['index'] - $this->_iCMS['G3a02']['step'];
$this->_iCMS['G3a02']['index_next'] = $this->_iCMS['G3a02']['index'] + $this->_iCMS['G3a02']['step'];
$this->_iCMS['G3a02']['first']	  = ($this->_iCMS['G3a02']['iteration'] == 1);
$this->_iCMS['G3a02']['last']	   = ($this->_iCMS['G3a02']['iteration'] == $this->_iCMS['G3a02']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_G3a02'][$this->_iCMS['G3a02']['index']],$this->_iCMS['G3a02']);
?>
          <dd><a href="<?php echo $this->_vars['list']['url']; ?>" class="pimg"><img width="120" height="120" src="<?php echo $this->_run_modifier($this->_vars['list']['pic'], 'small', 'plugin', 1); ?>" /></a> <span><a href="<?php echo $this->_vars['list']['url']; ?>"><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 10); ?></a></span> </dd>
          <?php }}?>
        </dl>
      </div>
      <!--新闻列表-->
      <div class="newslist"> <?php $this->_run_iCMS(array('loop' => 'true', 'page' => 'true', 'row' => '10', 'sortid' => $this->_vars['sort']['id'], 'module' => "list"));  if (isset($this->_iCMS['G3c92'])) unset($this->_iCMS['G3c92']);
$this->_iCMS['G3c92']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_G3c92']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['G3c92']['show'] = true;
$this->_iCMS['G3c92']['max'] = $this->_iCMS['G3c92']['loop'];
$this->_iCMS['G3c92']['step'] = 1;
$this->_iCMS['G3c92']['start'] = $this->_iCMS['G3c92']['step'] > 0 ? 0 : $this->_iCMS['G3c92']['loop']-1;
if ($this->_iCMS['G3c92']['show']) {
	$this->_iCMS['G3c92']['total'] = $this->_iCMS['G3c92']['loop'];
	if ($this->_iCMS['G3c92']['total'] == 0){
		$this->_iCMS['G3c92']['show'] = false;
	}
} else{
	$this->_iCMS['G3c92']['total'] = 0;
}
if ($this->_iCMS['G3c92']['show']){

		for ($this->_iCMS['G3c92']['index'] = $this->_iCMS['G3c92']['start'], $this->_iCMS['G3c92']['iteration'] = 1;
			 $this->_iCMS['G3c92']['iteration'] <= $this->_iCMS['G3c92']['total'];
			 $this->_iCMS['G3c92']['index'] += $this->_iCMS['G3c92']['step'], $this->_iCMS['G3c92']['iteration']++){
$this->_iCMS['G3c92']['rownum'] = $this->_iCMS['G3c92']['iteration'];
$this->_iCMS['G3c92']['index_prev'] = $this->_iCMS['G3c92']['index'] - $this->_iCMS['G3c92']['step'];
$this->_iCMS['G3c92']['index_next'] = $this->_iCMS['G3c92']['index'] + $this->_iCMS['G3c92']['step'];
$this->_iCMS['G3c92']['first']	  = ($this->_iCMS['G3c92']['iteration'] == 1);
$this->_iCMS['G3c92']['last']	   = ($this->_iCMS['G3c92']['iteration'] == $this->_iCMS['G3c92']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_G3c92'][$this->_iCMS['G3c92']['index']],$this->_iCMS['G3c92']);
?>
        <dl>
          <dt><a href="<?php echo $this->_vars['list']['url']; ?>" target="_blank"><?php echo $this->_vars['list']['title']; ?></a></dt>
          <dd class="preview"><?php if ($this->_vars['list']['pic']): ?> <a href="<?php echo $this->_vars['list']['url']; ?>"><img class="pic" align="left" width="125" height="85" src="<?php echo $this->_run_modifier($this->_vars['list']['pic'], 'small', 'plugin', 1); ?>" /></a> <?php endif;  echo $this->_vars['list']['description']; ?>...</dd>
          <dd class="info">作者：<span><?php echo $this->_vars['list']['author']; ?></span>发表于：<span><?php echo $this->_run_modifier($this->_vars['list']['pubdate'], 'date', 'plugin', 1, "Y-m-d H:i"); ?></span>　点击：<span><?php echo $this->_vars['list']['hits']; ?></span>　评论：<span><?php echo $this->_vars['list']['comments']; ?></span> <a href="<?php echo $this->_vars['list']['url']; ?>">查阅全文...</a></dd>
        </dl>
        <?php }}?> <!--分页-->
        <div class="pages">
          <div class="plist"> <?php echo $this->_vars['pagenav']; ?> <a href="<?php echo $this->_vars['site']['url']; ?>/more.php?id=<?php echo $this->_vars['sort']['id']; ?>&page=<?php echo $this->_vars['pageA']['current']; ?>">更多...</a></div>
        </div>
      </div>
    </div>
    <!--右侧-->
    <div class="pright"> <!--侧边信息列表-->
      <div class="classbox">
        <dl>
          <dt>栏目列表</dt>
          <dd>
            <ul>
              <?php $this->_run_iCMS(array('type' => 'self', 'loop' => 'true', 'att' => 'list', 'id' => $this->_vars['sort']['id'], 'module' => "catalog"));  if (isset($this->_iCMS['G45b7'])) unset($this->_iCMS['G45b7']);
$this->_iCMS['G45b7']['loop'] = is_array($this->_vars['catalog']) ? count($this->_vars['catalog']) : max(0, (int)$this->_vars['catalog']);
$this->_iCMS['_G45b7']=$this->_vars['catalog'];
unset($this->_vars['catalog']);
$this->_iCMS['G45b7']['show'] = true;
$this->_iCMS['G45b7']['max'] = $this->_iCMS['G45b7']['loop'];
$this->_iCMS['G45b7']['step'] = 1;
$this->_iCMS['G45b7']['start'] = $this->_iCMS['G45b7']['step'] > 0 ? 0 : $this->_iCMS['G45b7']['loop']-1;
if ($this->_iCMS['G45b7']['show']) {
	$this->_iCMS['G45b7']['total'] = $this->_iCMS['G45b7']['loop'];
	if ($this->_iCMS['G45b7']['total'] == 0){
		$this->_iCMS['G45b7']['show'] = false;
	}
} else{
	$this->_iCMS['G45b7']['total'] = 0;
}
if ($this->_iCMS['G45b7']['show']){

		for ($this->_iCMS['G45b7']['index'] = $this->_iCMS['G45b7']['start'], $this->_iCMS['G45b7']['iteration'] = 1;
			 $this->_iCMS['G45b7']['iteration'] <= $this->_iCMS['G45b7']['total'];
			 $this->_iCMS['G45b7']['index'] += $this->_iCMS['G45b7']['step'], $this->_iCMS['G45b7']['iteration']++){
$this->_iCMS['G45b7']['rownum'] = $this->_iCMS['G45b7']['iteration'];
$this->_iCMS['G45b7']['index_prev'] = $this->_iCMS['G45b7']['index'] - $this->_iCMS['G45b7']['step'];
$this->_iCMS['G45b7']['index_next'] = $this->_iCMS['G45b7']['index'] + $this->_iCMS['G45b7']['step'];
$this->_iCMS['G45b7']['first']	  = ($this->_iCMS['G45b7']['iteration'] == 1);
$this->_iCMS['G45b7']['last']	   = ($this->_iCMS['G45b7']['iteration'] == $this->_iCMS['G45b7']['total']);
$this->_vars['catalog']= array_merge($this->_iCMS['_G45b7'][$this->_iCMS['G45b7']['index']],$this->_iCMS['G45b7']);
?>
              <li><a href='<?php echo $this->_vars['catalog']['url']; ?>'><?php echo $this->_vars['catalog']['name']; ?></a></li>
              <?php }}?>
            </ul>
          </dd>
        </dl>
      </div>
      <div class="rlist margintop">
        <div class="title">随机推荐</div>
        <div class="rbox">
          <ul>
            <?php $this->_run_iCMS(array('loop' => 'true', 'orderby' => 'digg', 'row' => "20", 'sortid' => $this->_vars['sort']['id'], 'module' => "list"));  if (isset($this->_iCMS['G4755'])) unset($this->_iCMS['G4755']);
$this->_iCMS['G4755']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_G4755']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['G4755']['show'] = true;
$this->_iCMS['G4755']['max'] = $this->_iCMS['G4755']['loop'];
$this->_iCMS['G4755']['step'] = 1;
$this->_iCMS['G4755']['start'] = $this->_iCMS['G4755']['step'] > 0 ? 0 : $this->_iCMS['G4755']['loop']-1;
if ($this->_iCMS['G4755']['show']) {
	$this->_iCMS['G4755']['total'] = $this->_iCMS['G4755']['loop'];
	if ($this->_iCMS['G4755']['total'] == 0){
		$this->_iCMS['G4755']['show'] = false;
	}
} else{
	$this->_iCMS['G4755']['total'] = 0;
}
if ($this->_iCMS['G4755']['show']){

		for ($this->_iCMS['G4755']['index'] = $this->_iCMS['G4755']['start'], $this->_iCMS['G4755']['iteration'] = 1;
			 $this->_iCMS['G4755']['iteration'] <= $this->_iCMS['G4755']['total'];
			 $this->_iCMS['G4755']['index'] += $this->_iCMS['G4755']['step'], $this->_iCMS['G4755']['iteration']++){
$this->_iCMS['G4755']['rownum'] = $this->_iCMS['G4755']['iteration'];
$this->_iCMS['G4755']['index_prev'] = $this->_iCMS['G4755']['index'] - $this->_iCMS['G4755']['step'];
$this->_iCMS['G4755']['index_next'] = $this->_iCMS['G4755']['index'] + $this->_iCMS['G4755']['step'];
$this->_iCMS['G4755']['first']	  = ($this->_iCMS['G4755']['iteration'] == 1);
$this->_iCMS['G4755']['last']	   = ($this->_iCMS['G4755']['iteration'] == $this->_iCMS['G4755']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_G4755'][$this->_iCMS['G4755']['index']],$this->_iCMS['G4755']);
?>
            <li><a href='<?php echo $this->_vars['list']['url']; ?>'><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 24); ?></a></li>
            <?php }}?>
          </ul>
        </div>
      </div>
      <!--侧边信息列表-->
      <div class="rlist margintop">
        <div class="title">热门关注</div>
        <div class="rbox">
          <ul>
            <?php $this->_run_iCMS(array('loop' => 'true', 'orderby' => 'hot', 'row' => "20", 'sortid' => $this->_vars['sort']['id'], 'module' => "list"));  if (isset($this->_iCMS['G4925'])) unset($this->_iCMS['G4925']);
$this->_iCMS['G4925']['loop'] = is_array($this->_vars['list']) ? count($this->_vars['list']) : max(0, (int)$this->_vars['list']);
$this->_iCMS['_G4925']=$this->_vars['list'];
unset($this->_vars['list']);
$this->_iCMS['G4925']['show'] = true;
$this->_iCMS['G4925']['max'] = $this->_iCMS['G4925']['loop'];
$this->_iCMS['G4925']['step'] = 1;
$this->_iCMS['G4925']['start'] = $this->_iCMS['G4925']['step'] > 0 ? 0 : $this->_iCMS['G4925']['loop']-1;
if ($this->_iCMS['G4925']['show']) {
	$this->_iCMS['G4925']['total'] = $this->_iCMS['G4925']['loop'];
	if ($this->_iCMS['G4925']['total'] == 0){
		$this->_iCMS['G4925']['show'] = false;
	}
} else{
	$this->_iCMS['G4925']['total'] = 0;
}
if ($this->_iCMS['G4925']['show']){

		for ($this->_iCMS['G4925']['index'] = $this->_iCMS['G4925']['start'], $this->_iCMS['G4925']['iteration'] = 1;
			 $this->_iCMS['G4925']['iteration'] <= $this->_iCMS['G4925']['total'];
			 $this->_iCMS['G4925']['index'] += $this->_iCMS['G4925']['step'], $this->_iCMS['G4925']['iteration']++){
$this->_iCMS['G4925']['rownum'] = $this->_iCMS['G4925']['iteration'];
$this->_iCMS['G4925']['index_prev'] = $this->_iCMS['G4925']['index'] - $this->_iCMS['G4925']['step'];
$this->_iCMS['G4925']['index_next'] = $this->_iCMS['G4925']['index'] + $this->_iCMS['G4925']['step'];
$this->_iCMS['G4925']['first']	  = ($this->_iCMS['G4925']['iteration'] == 1);
$this->_iCMS['G4925']['last']	   = ($this->_iCMS['G4925']['iteration'] == $this->_iCMS['G4925']['total']);
$this->_vars['list']= array_merge($this->_iCMS['_G4925'][$this->_iCMS['G4925']['index']],$this->_iCMS['G4925']);
?>
             <li><a href='<?php echo $this->_vars['list']['url']; ?>'><?php echo $this->_run_modifier($this->_vars['list']['title'], 'cut', 'plugin', 1, 24); ?></a></li>
           <?php }}?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php error_reporting(iCMS_TPL_BUG?E_ALL ^ E_NOTICE:0);!defined('iCMS') && exit('What are you doing?');?>
<div id="footer">
	<div id="copyright">&copy; <em><?php echo $this->_vars['site']['title']; ?></em></div>
</div>
</body>
</html>
