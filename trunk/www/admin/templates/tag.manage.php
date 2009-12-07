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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;TAG管理','');
	function doaction(obj){
		switch(obj.value){ 
			case "del":
				if(confirm("确定要删除！！！")){
					return true;
				}else{
					obj.value="";
					return false;
				}
			break;
		}
	}
</script>
<div class="container" id="cpcontainer">
  <div class="itemtitle">
    <ul class="tab1" id="submenu">
      <li<?php if($operation=="manage"){?> class="current"<?php }?>><a href="<?=__SELF__?>?do=tag&operation=manage"><span>TAG管理</span></a></li>
      <li<?php if($operation=="sort"){?> class="current"<?php }?>><a href="<?=__SELF__?>?do=tag&operation=sort"><span>TAG归类管理</span></a></li>
    </ul>
  </div>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>点击ID可查看该TAG</li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=tag&operation=post" method="post">
  <table class="tb tb2 ">
    <tr>
      <td>&nbsp;</td>
      <th>ID</th>
      <th>TAG</th>
      <th>TAG归类</th>
      <th>使用数</th>
      <th>管理</th>
    </tr>
    <?php for($i=0;$i<$_count;$i++){
    	$iurlArray=array('id'=>$rs[$i]['id'],'link'=>pinyin($rs[$i]['name'],$iCMS->config['CLsplit']),'name'=>$rs[$i]['name']);
		$htmlurl=$iCMS->iurl('tag',$iurlArray,'',iPATH);
		$rs[$i]['url']= $iCMS->iurl('tag',$iurlArray);
    ?>
    <tr>
      <td><input type="checkbox" class="checkbox" name="id[]" value="<?=$rs[$i]['id']?>" /></td>
      <td><a href="<?=$rs[$i]['url']?>" target="_blank"><?=$rs[$i]['id']?></a></td>
      <td><input type="text" class="txt" name="name[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['name']?>" size="15" /></td>
      <td><select name="sortid[<?=$rs[$i]['id']?>]" style="width:auto;">
        <option value="0"> == 暂无归类 == </option>
        <?=tagsort($rs[$i]['sortid'])?>
        </select>
      </td>
      <td><a href="<?=__SELF__?>?do=article&operation=manage&tag=<?=rawurlencode($rs[$i]['name'])?>"><?=$rs[$i]['count']?></a></td>
      <td><?php if($iCMS->config['tagrule']!="php"&&$iCMS->config['ishtm']){?><a href="<?=__SELF__?>?do=tag&operation=updateHTML&id=<?=$rs[$i]['id']?>"><?=file_exists($htmlurl)?"更新":"待发布"?></a> | <?php } ?>
        <a href="<?=__SELF__?>?do=tag&operation=del&id=<?=$rs[$i]['id']?>"onClick="return confirm('确定要删除?');">删除 </a> | <?php if ($rs[$i]['visible']){ ?><a href="<?=__SELF__?>?do=tag&operation=disabled&id=<?=$rs[$i]['id']?>" title='点击禁用此TAG'>禁用</a><?php }else{  ?><a href="<?=__SELF__?>?do=tag&operation=open&id=<?=$rs[$i]['id']?>" title='点击启用此TAG'>启用</a><?php }?></td>
    </tr>
    <?php } ?>
    <tr>
      <td colspan="7"><?=$pagenav?></td>
    </tr>
    <tr class="nobg">
      <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'id')" />
        <label for="chkall">全选</label></td>
      <td colspan="6"><div class="fixsel">
        <select name="action" id="action" onChange="doaction(this);">
          <option value="tagedit">编辑</option>
          <option value="del">删除</option>
        <?php if($iCMS->config['ishtm']||$iCMS->config['tagrule']!="php"){?>
          <option value="html">生成静态</option>
          	  <?php }?>
            </select>
          <input type="submit" class="btn" name="forumlinksubmit" value="提交"  />
        </div></td>
    </tr>
  </table>
  </form>
</div>
</body></html>