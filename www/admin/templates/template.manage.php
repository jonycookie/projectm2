<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
iCMS_admincp_head();
?><div class="container" id="cpcontainer">
<table class="tb tb2 nobdb" width="100%">
  <tr>
    <th height="20">当前路径：<strong><a href="<?=__SELF__?>?do=template&operation=manage"><?=$Folder.'/'.$dir?></a></strong></th>
  </tr>
</table>
<table class="tb tb2 nobdb" width="100%">
  <?php if ($L['parentfolder']){ ?>
  <tr>
    <td width="24"><a href="<?=__SELF__?>?do=template&operation=manage&dir=<?=$L['parentfolder']?>"><img src="admin/images/file/parentfolder.gif" border="0"></a></td>
    <td class="vtop rowform"><strong><a href="<?=__SELF__?>?do=template&operation=manage&dir=<?=$L['parentfolder']?>">．．</a></strong></td>
  </tr>
  <?php } 
  	  for($i=0;$i<count($L['folder']);$i++){?>
  <tr>
    <td width="24"><a href="<?=__SELF__?>?do=template&operation=manage&dir=<?=$L['folder'][$i]['path']?>"><img src="admin/images/file/closedfolder.gif" border="0"></a></td>
    <td class="vtop rowform"><strong><a href="<?=__SELF__?>?do=template&operation=manage&dir=<?=$L['folder'][$i]['path']?>"><?=$L['folder'][$i]['dir']?></a></strong></td>
  </tr>
  <?php } ?>
</table>
<?php if ($L['FileList']){ ?>
<table class="tb tb2 " width="100%">
  <tr>
    <th>文件名</th>
    <th>文件大小</th>
    <th>最后修改时间</th>
    <th>操作</th>
    </tr>
  <?php for($i=0;$i<count($L['FileList']);$i++){
    $filepath=$L['FileList'][$i]['path'];
    ?>
  <tr>
    <td><?=$L['FileList'][$i]['icon']?> <?=$L['FileList'][$i]['name']?></td>
    <td><?=$L['FileList'][$i]['size']?></td>
    <td><?=$L['FileList'][$i]['time']?></td>
    <td><!--a href="admincp.php?do=file&operation=rename&path=<?=$Folder."/".$L['FileList'][$i]['path']?>">重命名 | </a-->
    	<?php if (in_array($L['FileList'][$i]['ext'],array('htm','html','css','js'))){ ?>
    	<a href="<?=__SELF__?>?do=template&operation=edit&path=<?=$Folder."/".$L['FileList'][$i]['path']?>">编辑</a>
    	<?php } if ($L['FileList'][$i]['ext']=='htm'){ ?>
    	 | <a href="<?=__SELF__?>?do=template&operation=clear&path=<?=substr($Folder."/".$L['FileList'][$i]['path'],1)?>">清除缓存</a> 
    	<?php } ?>
    	<!--a href="admincp.php?do=file&operation=delTpl&path=<?=$Folder."/".$L['FileList'][$i]['path']?>">删除</a--></td>
    </tr>
  <?php } ?>
</table>
<?php } ?>
</div>
</body>
</html>