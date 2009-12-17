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
