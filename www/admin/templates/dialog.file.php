<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=iCMS_CHARSET?>">
<link rel="stylesheet" href="admin/images/style.css" type="text/css" media="all" />
<link rel="stylesheet" href="admin/images/jquery.function.css" type="text/css" media="all" />
<script type="text/javascript" src="javascript/jquery.js"></script>
<script type="text/javascript" src="javascript/jquery.function.js"></script>
<script type="text/javascript" src="javascript/admin.fun.js"></script>
<script language="JavaScript" type="text/javascript">
$(function(){
	window.focus();
	$(".viewpic").snap("href");
});
</script>
</head>
<body><div class="container" id="cpcontainer">
<table class="tb tb2 nobdb" width="100%">
  <tr>
    <th height="20">当前路径：<strong><a href="<?=__SELF__?>?do=dialog&operation=<?=$operation?>&type=<?=$type?>&hit=<?=$hit?>&in=<?=$in?>&from=<?=$from?>"><?=$Folder.'/'.$dir?></a></strong></th>
  </tr>
</table>
<table class="tb tb2 nobdb" width="100%">
  <?php if ($L['parentfolder']){ ?>
  <tr>
    <td width="24"><a href="<?=__SELF__?>?do=dialog&operation=<?=$operation?>&type=<?=$type?>&hit=<?=$hit?>&in=<?=$in?>&from=<?=$from?>&dir=<?=$L['parentfolder']?>"><img src="admin/images/file/parentfolder.gif" border="0"></a></td>
    <td class="vtop rowform"><strong><a href="<?=__SELF__?>?do=dialog&operation=<?=$operation?>&type=<?=$type?>&hit=<?=$hit?>&in=<?=$in?>&from=<?=$from?>&dir=<?=$L['parentfolder']?>">．．</a></strong></td>
  </tr>
  <?php } for($i=0;$i<count($L['folder']);$i++){?>
  <tr>
    <td width="24"><a href="<?=__SELF__?>?do=dialog&operation=<?=$operation?>&type=<?=$type?>&hit=<?=$hit?>&in=<?=$in?>&from=<?=$from?>&dir=<?=$L['folder'][$i]['path']?>"><img src="admin/images/file/closedfolder.gif" border="0"></a></td>
    <td class="vtop rowform"><strong><?php if ($hit=='dir'){?><a href="javascript:void(0)" onclick="insert('<?=$L['folder'][$i]['path']?>','<?=$in?>');"><?php }else{?><a href="<?=__SELF__?>?do=dialog&operation=<?=$operation?>&type=<?=$type?>&hit=<?=$hit?>&in=<?=$in?>&from=<?=$from?>&dir=<?=$L['folder'][$i]['path']?>"><?php }?><?=$L['folder'][$i]['dir']?></a></strong></td>
  </tr>
  <?php } ?>
</table>
<table class="tb tb2 " width="100%">
  <tr>
    <th>文件名</th>
    <th>文件大小</th>
    <th>最后修改时间</th>
    </tr>
  <?php for($i=0;$i<count($L['FileList']);$i++){
    //$operation=='template'?'templates':$iCMS->config['uploadfiledir']
    if($operation=='template'){
    	$filepath=$L['FileList'][$i]['path'];
    }elseif($operation=='file'){
    	$filepath=$iCMS->config['uploadfiledir']."/".$L['FileList'][$i]['path'];
    	if(in_array($L['FileList'][$i]['ext'],array('jpg','gif','png','bmp','jpeg'))){
			$thumbfilepath=gethumb($filepath,'','',false,true);
			$li='';
			if($thumbfilepath)foreach($thumbfilepath as $wh=>$tfp){
				$tfp = $iCMS->dir.getfilepath($tfp,iPATH,'-');
    			$li.='<li><a href="javascript:void(0)" onclick="insert(\''.$tfp.'\',\''.$in.'\');" title="插入缩略图">'.$wh.'</a></li>';
    		}
    	}
    	$filepath=$iCMS->dir.$filepath;
    }
    ?>
  <tr>
    <td><a href="<?=$Folder."/".$L['FileList'][$i]['path']?>" class="viewpic" target="_blank"><?=$L['FileList'][$i]['icon']?></a> <?php if($hit=='file'){?><a href="javascript:void(0)" onclick="insert('<?=$filepath?>','<?=$in?>');"><?=$L['FileList'][$i]['name']?></a> <img src="admin/images/file/image.gif" align="absmiddle" alt="缩略图"><ul id='T<?=$i?>'><?=$li?></ul><?php }else{?><?=$L['FileList'][$i]['name']?><?php }?></td>
    <td><?=$L['FileList'][$i]['size']?></td>
    <td><?=$L['FileList'][$i]['time']?></td>
    </tr>
  <?php } ?>
</table>
<?php if($operation=='file'){?>
<table class="tb tb2 " width="100%">
    <tr>
      <form action="<?=__SELF__?>?do=dialog&operation=post" method="post" enctype="multipart/form-data" name="uploadfile" target="post" id="uploadfile">
        <td class="td25">上　传：</td>
        <td class="vtop rowform"><input name="file" type="file" class="uploadbtn" id="pic" /><input name="savedir" type="hidden" value="<?=$dir?>" /><input name="action" type="hidden" value="uploadfile" /> <input type="submit" value="上传" style="border:1px solid #999999;"/></td>
      </form>
    </tr>
    <tr>
      <form action="<?=__SELF__?>?do=dialog&operation=post" method="post" name="createdir" target="post" id="createdir">
        <td class="td25">新目录：</td>
        <td class="vtop rowform"><input type='text' name='dirname' value='' style='width:150px'><input name="savedir" type="hidden" value="<?=$dir?>" /><input name="action" type="hidden" value="createdir" /> <input type="submit" value="创建" style="border:1px solid #999999;"/></td>
      </form>
    </tr>
  </table>
<?php } ?>
</div>
<iframe width="100%" height="100" style="display:none" id="post" name="post"></iframe>
</body>
</html>