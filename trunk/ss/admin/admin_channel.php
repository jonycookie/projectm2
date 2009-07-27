<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_channel.php 11651 2009-03-17 06:33:07Z zhanglijun $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managechannel')) {
	showmessage('no_authority_management_operation');
}

//INIT RESULT VAR
$listarr = array();
$thevalue = array();
$protect_channel = array('bbs', 'news', 'uchblog', 'uchimage');

//POST METHOD
if(submitcheck('listsubmit')) {
	
	foreach ($_POST['nameid'] as $nameid => $channeltype) {
		$status = intval($_POST['show'][$nameid]);
		if($_POST['default'] == $nameid) $status = 2;
		if($channeltype == 'user') {
			if(!empty($_POST['delete'][$nameid])) {
				//删除频道文件
				@unlink(S_ROOT.'./channel/channel_'.$nameid.'.php');
				@unlink(S_ROOT.'./templates/'.$_SCONFIG['template'].'/channel_'.$nameid.'.html.php');
				deletetable('channels', array('nameid' => $nameid));
			} else {
				$setsqlarr = array(
					'name' => $_POST['name'][$nameid],
					'url' => $_POST['url'][$nameid],
					'status' => $status,
					'tpl' => $_POST['tpl'][$nameid],
					'displayorder' => $_POST['displayorder'][$nameid]
				);
				updatetable('channels', $setsqlarr, array('nameid' => $nameid));
			}
		} else {
			$setsqlarr = array(
				'name' => $_POST['name'][$nameid],
				'url' => $_POST['url'][$nameid],
				'status' => $status,
				'displayorder' => $_POST['displayorder'][$nameid]
			);
			updatetable('channels', $setsqlarr, array('nameid' => $nameid));
		}
	}

	//更新缓存
	updateuserspacemid();
	updatesettingcache();

	showmessage('channel_update_ok', $theurl);

} elseif (submitcheck('valuesubmit')) {
	
	$nameid = trim(strtolower($_POST['nameid']));
	if(empty($nameid) || !ereg("[a-zA-Z]+$", $nameid)) {
		showmessage('channel_action_error');
	}

    if(in_array($nameid, $protect_channel)) {
        showmessage('channel_action_protect');
    }
	if($nameid == 'sample') {
		showmessage('channel_action_exist');
	}
	if($_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('channels')." WHERE nameid='$nameid'"), 0)) {
		showmessage('channel_action_exist');
	}

	if(!empty($_POST['usesample'])) {
		//复制程序文件
		$src = S_ROOT.'./channel/channel_sample.php';
		$obj = S_ROOT.'./channel/channel_'.$nameid.'.php';
		if(!file_exists($src)) {
			showmessage('channel_php_src_error');
		}
		if(!@copy($src, $obj)) {
			$data = implode('', file ($src));
			writefile($obj, $data);
		}
		//复制模板
		$src = S_ROOT.'./templates/'.$_SCONFIG['template'].'/channel_sample.html.php';
		$obj = S_ROOT.'./templates/'.$_SCONFIG['template'].'/channel_'.$nameid.'.html.php';
		if(!file_exists($src)) {
			showmessage('channel_tpl_src_error');
		}
		if(!@copy($src, $obj)) {
			$data = implode('', file ($src));
			writefile($obj, $data);
		}
	}
	
	//添加
	$sqlarr = array(
		'nameid' => $nameid,
		'name' => $_POST['name'],
		'status' => 1,
		'type' => 'user',
		'tpl' => 'channel_'.$nameid
	);
	inserttable('channels', $sqlarr);

	//更新缓存
	updatesettingcache();
	updateuserspacemid();
	
	showmessage('channel_add_ok', $theurl);

}

//GET METHOD
$addclass = $viewclass = '';
if (empty($_GET['op'])) {

	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('channels')." ORDER BY displayorder");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($value['name'])) $value['name'] = $lang[$value['nameid']];
		$value['visit'] = $value['url'];
		if(empty($value['visit'])) {
			if($value['type'] == 'user') {
				$value['visit'] = geturl("action/channel/name/$value[nameid]");
			} elseif($value['type'] == 'model') {
				$value['visit'] = S_URL.'/m.php?name='.$value['nameid'];
			} else {
				$value['visit'] = geturl("action/$value[nameid]");
			}
		}
		$listarr[$value['nameid']] = $value;
	}
	$viewclass = ' class="active"';
	
} elseif ($_GET['op'] == 'add') {
	
	$thevalue = array(
		'name' => '',
		'nameid' => '',
		'usesample' => 1
	);
	$addclass = ' class="active"';
	
} elseif ($_GET['op'] == 'edittpl') {
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('channels')." WHERE nameid='$_GET[nameid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['type'] == 'model') {
			showmessage('channel_is_model');
		}
		if(empty($value['tpl'])) {
			if(in_array($value['nameid'], array('uchblog', 'uchimage'))) {
				$value['nameid'] = substr($value['nameid'], 3);
			}
			$value['tpl'] = "{$value['nameid']}_index";
		}
		header("Location: admincp.php?action=tpl&op=edit&filename=$value[tpl]");
		exit;
	} else {
		showmessage('channel_no_exists');
	}
}

//SHOW HTML
//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['admincp_header_channel'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$theurl.'" class="view">'.$alang['admincp_header_channel'].'</a></td>
					<td'.$addclass.'><a href="'.$theurl.'&op=add" class="add">'.$alang['channel_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if(is_array($listarr) && $listarr) {
	
	$adminmenu = '<input name="importdelete" type="radio" value="1" checked /> '.$alang['poll_delete'];
	
	echo label(array('type'=>'form-start', 'name'=>'listform', 'action' => $theurl));
	echo label(array('type'=>'help', 'text' => $alang['help_channel']));
	echo label(array('type'=>'table-start', 'class'=>'listtable'));

	echo '<tr>';
	echo '<th width="5%">'.$alang['common_delete'].'</th>';
	echo '<th width="9%">'.$alang['channel_title_action'].'</th>';
	echo '<th width="21%">'.$alang['channel_show'].'</th>';
	echo '<th width="9%">'.$alang['channel_index'].'</th>';
	echo '<th width="10%">'.$alang['channel_name'].'</th>';
	echo '<th width="17%">'.$alang['channel_url'].'</th>';
	echo '<th width="3%">'.$alang['channel_displayorder'].'</th>';
	echo '<th width="17%">'.$alang['channel_tpl'].'</th>';
	echo '<th width="9%">'.$alang['channel_op'].'</th>';
	echo '</tr>';

	empty($class) ? $class=' class="darkrow"': $class='';
	echo '<tr'.$class.' align="center">';
	echo '<td>-</td>';
	echo '<td>-</td>';
	echo '<td>-</td>';
	echo '<td><input type="radio" name="default" onclick="defaultchennel(this.form, \'index\');" value="index" checked /></td>';
	echo '<td>'.$alang['channel_all_index'].'</td>';
	echo '<td>'.S_URL_ALL.'</td>';
	echo '<td>-</td>';
	echo '<td>-</td>';
	echo '<td><a href="admincp.php?action=tpl&op=edit&filename=index" target="_blank">'.$alang['channel_tpl_edit'].'</a><br><a href="'.S_URL.'" target="_blank">'.$alang['channel_visit'].'</a></td>';
	echo '</tr>';

	foreach ($listarr as $listvalue) {
		$statusarr = array((($listvalue['status'] == 2) ? 1 : $listvalue['status'])=>'checked="checked"');
		empty($class) ? $class=' class="darkrow"': $class='';
		echo '<tr'.$class.' align="center">';
		echo '<td><input type="checkbox" name="delete['.$listvalue['nameid'].']" value="1"'.($listvalue['type']=='user'?'':' disabled').' /></td>';
		echo '<td><strong>'.$listvalue['nameid'].'</strong><input type="hidden" name="nameid['.$listvalue['nameid'].']" value="'.$listvalue['type'].'" /></td>';
		echo '<td><input type="radio" name="show['.$listvalue['nameid'].']" value="1" '.$statusarr[1].($listvalue['status']==2?' disabled="disabled"':'').'/>'.$alang['channel_open'].'<input type="radio" name="show['.$listvalue['nameid'].']" value="-1" '.$statusarr[-1].($listvalue['status']==2?' disabled="disabled"':'').' />'.$alang['channel_close'].'<input type="radio" name="show['.$listvalue['nameid'].']" value="0" '.$statusarr[0].($listvalue['status']==2?' disabled="disabled"':'').' />'.$alang['channel_hide'].'</td>';
		echo '<td><input type="radio" name="default" onclick="defaultchennel(this.form, \''.$listvalue['nameid'].'\');" value="'.$listvalue['nameid'].'"'.($listvalue['status']==2?' checked':'').' /></td>';
		echo '<td><input type="text" name="name['.$listvalue['nameid'].']" size="10" value="'.$listvalue['name'].'" /></td>';
		echo '<td><input type="text" name="url['.$listvalue['nameid'].']" size="20" value="'.$listvalue['url'].'" /></td>';
		echo '<td><input type="text" name="displayorder['.$listvalue['nameid'].']" size="2" value="'.$listvalue['displayorder'].'" /></td>';
		echo '<td><input type="text" name="tpl['.$listvalue['nameid'].']" size="10" value="'.$listvalue['tpl'].'"'.($listvalue['type']=='user'?'':' disabled').' /></td>';
		echo '<td><a href="'.$theurl.'&op=edittpl&nameid='.$listvalue['nameid'].'" target="_blank">'.$alang['channel_tpl_edit'].'</a><br><a href="'.$listvalue['visit'].'" target="_blank">'.$alang['channel_visit'].'</a></td>';
		echo '</tr>';
	}
	echo label(array('type'=>'table-end'));
	
	echo label(array('type'=>'table-start', 'class'=>'btmtable'));
	echo '<tr><th><input type="checkbox" name="chkall" onclick="checkall(this.form, \'delete\')">'.$alang['space_select_all'].' '.$adminmenu.'</th></tr>';	
	echo label(array('type'=>'table-end'));

	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value' => $alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'listreset', 'value' => $alang['common_reset']));
	echo '</div>';
	echo label(array('type'=>'form-end'));
}

//THE VALUE SHOW
if(is_array($thevalue) && $thevalue) {
	
	$usesamplearr = array(
		'1' => $alang['channel_usesample_1'],
		'0' => $alang['channel_usesample_0']
	);
	
	echo '
	<script language="javascript">
	<!--
	function thevalidate(theform) {
		return true;
	}
	//-->
	</script>
	';
	
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action' => $theurl, 'other'=>' onSubmit="return validate(this)"'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'channel_name', 'name'=>'name', 'size'=>30, 'value' => $thevalue['name']));
	echo label(array('type'=>'input', 'alang'=>'channel_title_action_add', 'name'=>'nameid', 'size'=>30, 'value' => $thevalue['nameid']));
	echo label(array('type'=>'radio', 'alang'=>'channel_usesample', 'name'=>'usesample', 'options' => $usesamplearr, 'value' => $thevalue['usesample']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value' => $alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value' => $alang['common_reset']));
	echo '</div>';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));

}

?>