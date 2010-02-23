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
  <script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;广告管理','<a href="<?=__SELF__?>?action=misc&operation=custommenu&do=add&title=cplog_advertisements&url=action%3Dadvertisements" target="main"><img src="admin/images/btn_add2menu.gif" title="添加常用操作" width="19" height="18" /></a>');
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
  <div class="itemtitle">
    <h3>广告管理</h3>
    <ul class="tab1">
      <li class="current"><a href="<?=__SELF__?>?do=advertise"><span>管理</span></a></li>
      <li><a href="<?=__SELF__?>?do=advertise&operation=add"><span>添加</span></a> </li>
    </ul>
  </div>
  <form name="cpform" method="post" action="<?=__SELF__?>?do=advertise&operation=post" id="cpform" >
    <table class="tb tb2 ">
      <tr>
        <th></th>
        <th>当前状态</th>
        <th>广告标签</th>
        <th>广告描述</th>
        <th>展现方式</th>
        <th>起始时间</th>
        <th>终止时间</th>
        <th></th>
      </tr>
      <?php for($i=0;$i<$_count;$i++){?>
      <tr>
        <td class="td25"><input class="checkbox" type="checkbox" name="id[]" value="<?=$rs[$i]['id']?>"></td>
        <td><?=$rs[$i]['status']?'启用':'关闭'?></td>
        <td class="td28">&lt;!--{iCMS:advertise name="<?=$rs[$i]['varname']?>"}--&gt;</td>
        <td><?=$rs[$i]['title']?></td>
        <td><?php switch ($rs[$i]['style']){case 'code':echo "代码";break;case 'text':echo "文字";break;case 'image':echo "图片";break;case 'flash':echo "FLASH";break;}?></td>
        <td><?=get_date($rs[$i]['starttime'],'Y-m-d');?></td>
        <td><?=empty($rs[$i]['endtime'])?'无限制':get_date($rs[$i]['endtime'],'Y-m-d');?></td>
        <td><?=$rs[$i]['status']?'<a href="'.__SELF__.'?do=advertise&operation=status&id='.$rs[$i]['id'].'&act=0">关闭</a>':'<a href="'.__SELF__.'?do=advertise&operation=status&id='.$rs[$i]['id'].'&act=1">启用</a>'?>
          <a href="<?=__SELF__?>?do=advertise&operation=add&advid=<?=$rs[$i]['id']?>" class="act">修改</a></td>
      </tr>
      <?php }?>
      <tr>
        <td height="22" colspan="8" align="right"><?=$pagenav?></td>
      </tr>
      <tr class="nobg">
        <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'id')" />
          <label for="chkall">全选</label></td>
        <td colspan="8"><div class="fixsel"><select name="action" id="action" onChange="doaction(this);">
          <option value="">====批量操作====</option>
          <option value="del">删除</option>
          <option value="js">生成js</option>
            </select>
            <input type="submit" class="btn" name="advsubmit" value="提交"  />
          </div></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>