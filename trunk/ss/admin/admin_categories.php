<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_categories.php 11194 2009-02-25 04:58:46Z zhaolei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managecategories')) {
	showmessage('no_authority_management_operation');
}

//CHECK GET VAR
$type = 'news';
if(in_array($type, $_SCONFIG['closechannels'])) {
	showmessage('usetype_no_open');
}

$urlplus = '&type='.$type;
$newurl = $theurl.$urlplus;

//INIT RESULT VAR
$listarr = array();
$thevalue = array();
$bbsvalue = array();
$opvalue = array();

//POST METHOD
if(submitcheck('listsubmit')) {
	//LIST UPDATE
	if(is_array($_POST['displayorder']) && $_POST['displayorder']) {
		foreach ($_POST['displayorder'] as $postcatid => $postdisplayorder) {
			updatetable('categories', array('displayorder'=>$postdisplayorder), array('catid' => $postcatid));
		}
	}

	updatecategorycache();
	showmessage('category_update_success', $newurl);

} elseif(submitcheck('valuesubmit')) {

	$_POST['catid'] = intval($_POST['catid']);
	$_POST['name'] = trim($_POST['name']);
	if(strlen($_POST['name']) < 2 || strlen($_POST['name']) > 50) {
		showmessage('category_size_error');
	}

	$subcatidarr = array();
	if(!empty($_POST['subcatid'])) $subcatidarr = $_POST['subcatid'];

	$newfilearr = array('file'=>'', 'thumb'=>'');
	if(!empty($_FILES['icon'])) {
		$filearr = $_FILES['icon'];
		include_once(S_ROOT.'./function/upload.func.php');
		$newfilearr = savelocalfile($filearr);
	}

	$setsqlarr = array(
		'upid' => intval($_POST['upid']),
		'name' => $_POST['name'],
		'note' => trim($_POST['note']),
		'tpl' => trim($_POST['tpl']),
		'viewtpl' => trim($_POST['viewtpl']),
		'displayorder' => intval($_POST['displayorder']),
		'url' => trim($_POST['url']),
		'ischannel' => 0
	);

	if(!empty($newfilearr['file'])) $setsqlarr['image'] = $newfilearr['file'];
	if(!empty($newfilearr['thumb'])) $setsqlarr['thumb'] = $newfilearr['thumb'];

	if($_POST['catid']) {
		//UPDATE
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('categories')." WHERE catid='$_POST[catid]'");
		$thecat = $_SGLOBAL['db']->fetch_array($query);
		if(!empty($_POST['icon_delete'])) {
			$setsqlarr['thumb'] = '';
			$setsqlarr['image'] = '';
			@unlink(A_DIR.'/'.$thecat['thumb']);
			@unlink(A_DIR.'/'.$thecat['image']);
		}
		$subcatidarr[] = $_POST['catid'];
		$subcatidarr = array_unique($subcatidarr);
		$setsqlarr['subcatid'] = implode(',', $subcatidarr);
		updatetable('categories', $setsqlarr, array('catid' => $_POST['catid']));

		//更新缓存
		updatecategorycache();
		showmessage('category_update_success', $newurl);
	} else {
		//ADD
		$setsqlarr['type'] = $type;

		$catid = inserttable('categories', $setsqlarr, 1);
		$subcatidarr[] = $catid;
		$subcatidarr = array_unique($subcatidarr);
		$subcatid = implode(',', $subcatidarr);
		$_SGLOBAL['db']->query('UPDATE '.tname('categories').' SET subcatid=\''.$subcatid.'\' WHERE catid=\''.$catid.'\'');

		updatecategorycache();
		showmessage('category_add_success', $newurl);
	}

} elseif(submitcheck('bbssubmit')) {

	$includetype = 'bbsthread';
	$_POST['catid'] = intval($_POST['catid']);

	$postarr = array();
	foreach ($_POST as $pkey => $pvalue) {
		$postarr[$pkey] = shtmlspecialchars($pvalue);
	}
	$blocktext = addslashes(serialize($postarr));
	include_once(S_ROOT.'./admin/include/admin_blocks_'.$includetype.'_code.inc.php');

	$blockparameter = implode('/', $blockcodearr);

	$setsqlarr = array(
		'bbsmodel' => $_POST['bbsmodel'],
		'blocktext' => $blocktext,
		'blockparameter' => $blockparameter,
		'blockmodel' => $_POST['blockmodel']
	);

	updatetable('categories', $setsqlarr, array('catid'=>$_POST['catid']));

	updatecategorycache();
	showmessage('category_bbs_update_success', $newurl);

} elseif(submitcheck('delvaluesubmit')) {

	$catid = intval($_POST['catid']);
	$newcatid = intval($_POST['newcatid']);
	$getcategoriessunid = getcategoriessunid($catid);
	if(in_array($newcatid,$getcategoriessunid)) {
		showmessage('category_del_catid_error');
	}
	if(empty($newcatid)) {
		$_SGLOBAL['db']->query('DELETE FROM '.tname('categories').' WHERE catid=\''.$catid.'\'');
		$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET catid=\'0\', folder=\'3\' WHERE catid=\''.$catid.'\'');
	} else {
		$_SGLOBAL['db']->query('DELETE FROM '.tname('categories').' WHERE catid=\''.$catid.'\'');
		$_SGLOBAL['db']->query('UPDATE '.tname('categories').' SET upid=\''.$newcatid.'\' WHERE upid=\''.$catid.'\'');
		$_SGLOBAL['db']->query('UPDATE '.tname('spaceitems').' SET catid=\''.$newcatid.'\' WHERE catid=\''.$catid.'\'');
	}
	updatecategorycache();
	showmessage('category_delete_success', $newurl);

} elseif(submitcheck('movesubmit')) {

	$catid = intval($_POST['catid']);
	$upid = intval($_POST['newcatid']);
	$_SGLOBAL['db']->query('UPDATE '.tname('categories').' SET upid=\''.$upid.'\' WHERE catid=\''.$catid.'\'');
	//更新缓存
	updatecategorycache();
	showmessage('category_update_success', $newurl);

} elseif(submitcheck('copysubmit')) {

	$_POST['catid'] = empty($_POST['catid']) ? 0 : intval($_POST['catid']);
	if(empty($_POST['catid'])) {
		showmessage('category_catid_no_exists');
	}
	$optgroups = array ('url', 'subcatid', 'note', 'tpl', 'viewtpl');

	$fids = $comma = '';
	if(is_array($_POST['to']) && count($_POST['to'])) {
		foreach($_POST['to'] as $fid) {
			if(($fid = intval($fid)) && $fid != $source ) {
				$fids .= $comma.$fid;
				$comma = ',';
			}
		}
	}
	if(empty($fids)) {
		showmessage('category_copy_target_invalid');
	}
	
	$categoryoptions = array();
	if(is_array($_POST['options']) && !empty($_POST['options'])) {
		foreach($_POST['options'] as $option) {
			if($option = trim($option)) {
				if(in_array($option, $optgroups)) {
					$categoryoptions[] = $option;
				}
			}
		}
	}

	if(empty($categoryoptions)) {
		showmessage('category_copy_options_invalid');
	}


	$sourcecategory = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query('SELECT '.implode(',', $categoryoptions).' FROM '.tname('categories').' WHERE catid=\''.$_POST['catid'].'\''));
	if(empty($sourcecategory)) {
		showmessage('category_copy_source_invalid');
	}

	$updatequery = 'catid=catid';
	foreach($sourcecategory as $key => $val) {
		$updatequery .= ", $key='".addslashes($val)."'";
	}
	$_SGLOBAL['db']->query('UPDATE '.tname('categories').' SET '.$updatequery.' WHERE catid IN ('.$fids.')');


	updatecategorycache();
	showmessage('category_copy_succeed', CPURL.'?action=categories');
	
}

//GET METHOD
$addclass = $viewclass = '';
if(empty($_GET['op'])) {

	//LIST VIEW
	$listarr = getcategory($type);
	$viewclass = ' class="active"';

} elseif($_GET['op'] == 'edit') {
	
	$_GET['catid'] = intval($_GET['catid']);
	//ONE VIEW FOR UPDATE
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('categories').' WHERE catid=\''.$_GET['catid'].'\'');
	if($thevalue = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($thevalue['subcatid'])) $thevalue['subcatid'] = $thevalue['catid'];
	} else {
		showmessage('category_catid_no_exists');
	}

} elseif($_GET['op'] == 'add') {

	//ONE ADD
	$thevalue = array(
		'catid' => '0',
		'upid' => 0,
		'name' => '',
		'note' => '',
		'type' => $type,
		'ischannel' => '0',
		'displayorder' => '0',
		'tpl' => '',
		'url' => '',
		'bbsfids' => '',
		'uptype' => '0',
		'subcatid' => 0
	);
	if(!empty($_GET['upid'])) {
		$thevalue['upid'] = intval($_GET['upid']);
	}
	$addclass = ' class="active"';

} elseif($_GET['op'] == 'delete' || $_GET['op'] == 'move') {

	$opvalue['catid'] = intval($_GET['catid']);
	$opvalue['type'] = $type;

} elseif($_GET['op'] == 'bbs') {

	$_GET['catid'] = intval($_GET['catid']);
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('categories').' WHERE catid=\''.$_GET['catid'].'\'');
	if($bbsvalue = $_SGLOBAL['db']->fetch_array($query)) {
		if(empty($bbsvalue['blockmodel'])) $bbsvalue['blockmodel'] = '1';
	}

} elseif($_GET['op'] == 'copy') {

	$listarr = getcategory($type);

}

//SHOW HTML
//MENU
echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['common_type_'.$type].$alang['category_type'].'</h1></td>
		<td class="actions">
			<table summary="" cellpadding="0" cellspacing="0" border="0" align="right">
				<tr>
					<td'.$viewclass.'><a href="'.$newurl.'" class="view">'.$alang['category_view_list'].'</a></td>
					<td'.$addclass.'><a href="'.$newurl.'&op=add" class="add">'.$alang['category_add'].'</a></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
';

//LIST SHOW
if(is_array($listarr) && $listarr) {

	if(empty($_GET['op'])) {

		echo label(array('type'=>'form-start', 'name'=>'listform', 'action'=>$newurl));
		echo label(array('type'=>'table-start', 'class'=>'listtable'));
		echo '<tr><th>'.$alang['category_title_name'].'</th>';
		echo '<th width="10%">'.$alang['category_displayorder'].'</th>';
		echo '<th width="50%">'.$alang['space_op'].'</th></tr>';
		foreach ($listarr as $listvalue) {
			empty($class) ? $class=' class="darkrow"': $class='';
			$namecolor = '';
			if($listvalue['bbsmodel']) $namecolor = ' class="red"';
			echo '<tr'.$class.'><td>';
			echo $listvalue['pre'];
			echo '<b>'.$listvalue['name'].'</b></td>';
			echo '<td align="center"><input name="displayorder['.$listvalue['catid'].']" type="text" id="displayorder['.$listvalue['catid'].']" size="5" maxlength="" value="'.$listvalue['displayorder'].'" /></td>';
			echo '<td align="center">';
			echo '[<a href="'.$newurl.'&op=add&upid='.$listvalue['catid'].'">'.$alang['category_add_sub'].'</a>] &nbsp; ';
			echo '[<a href="'.$newurl.'&op=edit&catid='.$listvalue['catid'].'">'.$alang['common_edit'].'</a>] &nbsp; ';
			echo '[<a href="'.$newurl.'&op=copy&catid='.$listvalue['catid'].'">'.$alang['common_copy'].'</a>] &nbsp; ';
			echo '[<a href="'.$newurl.'&op=move&catid='.$listvalue['catid'].'">'.$alang['move_cat'].'</a>] &nbsp; ';
			echo '[<a href="'.$newurl.'&op=delete&catid='.$listvalue['catid'].'">'.$alang['merger_deletion'].'</a>] &nbsp; ';
			echo '[<a href="'.$newurl.'&op=bbs&catid='.$listvalue['catid'].'"'.$namecolor.'>'.$alang['category_edit_bbs'].'</a>]';
			echo '</td>';
			echo '</tr>';
		}
		echo label(array('type'=>'table-end'));
		echo '<div class="buttons">';
		echo label(array('type'=>'button-submit', 'name'=>'listsubmit', 'value'=>$alang['common_submit']));
		echo label(array('type'=>'button-reset', 'name'=>'listreset', 'value'=>$alang['common_reset']));
		echo '</div>';
		echo label(array('type'=>'form-end'));

	} elseif($_GET['op'] == 'copy') {

		echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl));
		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'help', 'text'=>$alang['help_categories_copy']));
		echo label(array('type'=>'table-start'));
		echo label(array('type'=>'text', 'alang'=>'category_source', 'text'=>'<strong>'.$listarr[$_GET['catid']]['name'].'</strong>'));
		echo '<tr id="tr_to">
		<th>'.$alang['category_to'].'</th>
		<td><select name="to[]" size="10" multiple="multiple" style="width:200px;">';
		foreach($listarr as $listvalue) {
			echo '<option value="'.$listvalue['catid'].'">'.$listvalue['name'].'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr id="tr_to">
		<th>'.$alang['category_options_select'].'</th>
		<td><select name="options[]" size="10" multiple="multiple" style="width:200px;">
		<option value="url">'.$alang['category_copy_url'].'</option>
		<option value="subcatid">'.$alang['category_copy_subcatid'].'</option>
		<option value="note">'.$alang['category_title_note'].'</option>
		<option value="tpl">'.$alang['category_copy_tpl'].'</option>
		<option value="viewtpl">'.$alang['category_copy_view_tpl'].'</option>
		</select></td></tr>';
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));
	
		echo label(array('type'=>'eval', 'text'=>'<br><center>'));
		echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
		echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
		echo label(array('type'=>'eval', 'text'=>'</center>'));
		echo '<input name="catid" type="hidden" value="'.$_GET['catid'].'" />';
		echo '<input name="type" type="hidden" value="'.$thevalue['type'].'" />';
		echo '<input name="copysubmit" type="hidden" value="yes" />';
		echo label(array('type'=>'form-end'));

	}
}

if(!empty($opvalue)) {

	$catlistarr = getcategory($type, $space='|----', $opvalue['catid']);
	if($_GET['op'] == 'delete') {
		$langstr = 'category_title_newcatid';
		$catlistarr[0] = array('catid' => 0, 'upid' => 0, 'name' => $alang['category_delete'], 'pre'=>'');
	} elseif($_GET['op'] == 'move') {
		$langstr = 'category_move_title';
		$catlistarr[0] = array('catid' => 0, 'upid' => 0, 'name' => $alang['category_root'], 'pre'=>'');
	}

	echo label(array('type'=>'form-start', 'name'=>'delvalueform', 'action'=>$theurl));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'select-div', 'alang'=>$langstr, 'name'=>'newcatid', 'radio'=>'1', 'options'=>$catlistarr, 'value'=>'0'));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo label(array('type'=>'eval', 'text'=>'<br><center>'));
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo label(array('type'=>'eval', 'text'=>'</center>'));
	echo '<input name="catid" type="hidden" value="'.$opvalue['catid'].'" />';
	echo '<input name="type" type="hidden" value="'.$opvalue['type'].'" />';
	if($_GET['op'] == 'delete') {
		echo '<input name="delvaluesubmit" type="hidden" value="yes" />';
	} elseif($_GET['op'] == 'move') {
		echo '<input name="movesubmit" type="hidden" value="yes" />';
	}
	echo label(array('type'=>'form-end'));

}

//THE VALUE SHOW
if(is_array($thevalue) && $thevalue) {

	$stylearr = array();
	$catlistarr = getcategory($type);
	$upcatname = '';
	if(!empty($thevalue['upid']) && !empty($catlistarr[$thevalue['upid']]['name'])) {
		$upcatname = $catlistarr[$thevalue['upid']]['name'];
	} else {
		$thevalue['upid'] = 0;
		$upcatname = $alang['category_root'];
	}

	if(empty($thevalue['thumb'])) {
		$thevalue['thumb'] = S_URL.'/images/base/nopic.gif';
	} else {
		$thevalue['thumb'] = A_URL.'/'.$thevalue['thumb'];
	}
	if(empty($thevalue['image'])) {
		$thevalue['image'] = S_URL.'/images/base/nopic.gif';
	} else {
		$thevalue['image'] = A_URL.'/'.$thevalue['image'];
	}

	echo '
	<script language="javascript">
	<!--
	function jsfunction(value) {
		if(value == 0) {
			document.getElementById("tr_upid").style.display="none";
		} else {
			document.getElementById("tr_upid").style.display="";
		}
	}
	function thevalidate(theform) {
		theform.thevaluesubmit.disabled = true;
		return true;
	}
	//-->
	</script>
	';
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$theurl, 'other'=>' onSubmit="return thevalidate(this)"'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'help', 'text'=>$alang['help_categories_add']));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'text', 'alang'=>'category_title_upid', 'text'=>'<strong>'.$upcatname.'</strong>'));
	echo label(array('type'=>'input', 'alang'=>'category_title_name', 'name'=>'name', 'size'=>'30', 'width'=>'30%', 'value'=>$thevalue['name']));

	echo label(array('type'=>'input', 'alang'=>'category_title_url', 'name'=>'url', 'size'=>'30', 'value'=>$thevalue['url']));
	echo label(array('type'=>'select-div', 'alang'=>'category_title_subcatid', 'name'=>'subcatid', 'options'=>$catlistarr, 'value'=>$thevalue['subcatid']));
	echo label(array('type'=>'table-end'));

	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'uploadpic', 'alang'=>'category_title_icon', 'name'=>'icon', 'thumb'=>$thevalue['thumb']));
	echo label(array('type'=>'checkbox', 'alang'=>'category_title_icon_delete', 'name'=>'icon_delete', 'options'=>array('1'=>$alang['ad_delete'])));
	echo label(array('type'=>'textarea', 'alang'=>'category_title_note', 'name'=>'note', 'cols'=>'80', 'rows'=>'5', 'value'=>shtmlspecialchars($thevalue['note'])));
	echo label(array('type'=>'text', 'alang'=>'category_title_tpl', 'text'=>$alang['style_dir'].': templates/'.$_SCONFIG['template'].'/<br>'.$alang['style_file'].': <input name="tpl" size="30" value="'.$thevalue['tpl'].'" />.html.php'));
	echo label(array('type'=>'text', 'alang'=>'category_title_view_tpl', 'text'=>$alang['style_dir'].': templates/'.$_SCONFIG['template'].'/<br>'.$alang['style_file'].': <input name="viewtpl" size="30" value="'.$thevalue['viewtpl'].'" />.html.php'));
	echo label(array('type'=>'table-end'));

	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'category_title_displayorder_title', 'name'=>'displayorder', 'size'=>'10', 'value'=>$thevalue['displayorder']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo label(array('type'=>'eval', 'text'=>'<br><center>'));
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo label(array('type'=>'eval', 'text'=>'</center>'));
	echo '<input name="catid" type="hidden" value="'.$thevalue['catid'].'" />';
	echo '<input name="upid" type="hidden" value="'.$thevalue['upid'].'" />';
	echo '<input name="type" type="hidden" value="'.$thevalue['type'].'" />';
	echo '<input name="valuesubmit" type="hidden" value="yes" />';
	echo label(array('type'=>'form-end'));

}

if(is_array($bbsvalue) && $bbsvalue) {

	$blocktype = 'bbsthread';
	$stylearr = getstyle($blocktype);

	if(!empty($bbsvalue['blocktext'])) {
		$theblcokvalue = unserialize($bbsvalue['blocktext']);
	} else {
		$theblcokvalue = array('cachetime'=>'900',
			'start'=>'0',
			'limit'=>'10',
			'bbsurltype' =>'site'
		);
	}

	$cachetimearr = array(
		'' => $alang['block_cachetime_null'],
		'900' => $alang['block_cachetime_900'],
		'1800' => $alang['block_cachetime_1800'],
		'3600' => $alang['block_cachetime_3600'],
		'7200' => $alang['block_cachetime_7200'],
		'43200' => $alang['block_cachetime_43200'],
		'86400' => $alang['block_cachetime_86400']
	);

	include_once(S_ROOT.'./admin/include/admin_blocks_'.$blocktype.'.inc.php');

	//COMMON
	$blockarr['cache'] = array(
		'cachetime' => array(
			'type' => 'select-input',
			'alang' => 'block_title_cachetime',
			'options' => $cachetimearr,
			'size' => '10',
			'width' => '30%'
		)
	);

	$blockarr['template'] = array(
		'tpl' => array(
			'type' => 'select-div-preview',
			'alang' => 'block_title_skinid',
			'options' => $stylearr
		)
	);

	$blockmodelarr = array(
		'1' => $alang['block_model_1'],
		'2' => $alang['block_model_2']
	);

	$bbsmodelarr = array(
		'0' => $alang['category_bbsmode_0'],
		'1' => $alang['category_bbsmode_1']
	);

	echo '
	<script language="javascript">
	<!--
	function jssettid(value) {
		document.getElementById(\'divsettid1\').style.display = \'none\';
		document.getElementById(\'divsettid2\').style.display = \'none\';
		if(value == \'1\') {
			document.getElementById(\'divsettid1\').style.display = \'\';
		} else {
			document.getElementById(\'divsettid2\').style.display = \'\';
		}

	}
	function jsshowdetail(value) {
		document.getElementById(\'divshowdetail\').style.display = \'none\';
		if(value == \'1\') {
			document.getElementById(\'divshowdetail\').style.display = \'\';
		}

	}
	function jsblockmodel(value) {
		document.getElementById(\'divblockmodel1\').style.display = \'none\';
		document.getElementById(\'divblockmodel11\').style.display = \'none\';
		document.getElementById(\'divblockmodel12\').style.display = \'none\';
		document.getElementById(\'divblockmodel2\').style.display = \'none\';
		if(value == \'1\') {
			document.getElementById(\'divblockmodel1\').style.display = \'\';
			document.getElementById(\'divblockmodel11\').style.display = \'\';
			document.getElementById(\'divblockmodel12\').style.display = \'\';
		} else {
			document.getElementById(\'divblockmodel2\').style.display = \'\';
		}
	}
	function jsshowmulti(value) {
		var showmulti1 = document.getElementById(\'divshowmulti1\');
		var showmulti2 = document.getElementById(\'divshowmulti2\');
		showmulti1.style.display = \'none\';
		showmulti2.style.display = \'none\';
		if(value == \'1\') {
			showmulti2.style.display = \'\';
		} else {
			showmulti1.style.display = \'\';
		}
	}
	//-->
	</script>
	';

	if($bbsvalue['blockmodel'] == '1') {
		$divblockmodel1display = '';
		$divblockmodel2display = 'none';
	} else {
		$divblockmodel1display = 'none';
		$divblockmodel2display = '';
	}
	if(empty($theblcokvalue['sql'])) $theblcokvalue['sql'] = '';

	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>$newurl));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'help', 'text'=>$alang['help_categories_bbs']));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'text', 'alang'=>'category_title_name', 'text'=>'<strong>'.$bbsvalue['name'].'</strong>'));
	echo label(array('type'=>'radio', 'alang'=>'category_title_bbsmodel', 'name'=>'bbsmodel', 'options'=>$bbsmodelarr, 'width'=>'30%', 'value'=>$bbsvalue['bbsmodel']));
	echo label(array('type'=>'radio', 'alang'=>'block_title_blockmodel', 'name'=>'blockmodel', 'options'=>$blockmodelarr, 'width'=>'30%', 'value'=>$bbsvalue['blockmodel'], 'other'=>' onclick="jsblockmodel(this.value)"'));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'block_filter'));
	echo '<table cellspacing="0" cellpadding="0" id="divblockmodel1" style="display:'.$divblockmodel1display.'" class="maintable">';
	echolabel($blockarr['where'], $theblcokvalue);
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'block_order'));
	echo '<table cellspacing="0" cellpadding="0" id="divblockmodel11" style="display:'.$divblockmodel1display.'" class="maintable">';
	echolabel($blockarr['order'], $theblcokvalue);
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	echo '<table cellspacing="0" cellpadding="0" id="divblockmodel12" style="display:'.$divblockmodel1display.'" class="maintable">';
	echo label(array('type'=>'table-end'));

	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'block_model_2'));
	echo '<table cellspacing="0" cellpadding="0" id="divblockmodel2" style="display:'.$divblockmodel2display.'" class="maintable">';
	echo label(array('type'=>'textarea', 'alang'=>'block_title_sql', 'name'=>'sql', 'cols'=>104, 'rows'=>10, 'width'=>'30%', 'value'=>$theblcokvalue['sql']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	if(!empty($blockarr['batch'])) {
		unset($blockarr['batch']['messagelen']);
		unset($blockarr['batch']['messagedot']);
		unset($blockarr['batch']['showdetail']);

		echo label(array('type'=>'div-start'));
		echo label(array('type'=>'title', 'alang'=>'block_batch'));
		echo label(array('type'=>'table-start'));
		echolabel($blockarr['batch'], $theblcokvalue);
		echo label(array('type'=>'table-end'));
		echo label(array('type'=>'div-end'));
	}

	foreach ($blockarr as $bkey => $bvalue) {
		if(!isset($bvalue['type'])) $bvalue['type'] = 'eval';
		if(!isset($bvalue['alang'])) $bvalue['alang'] = '';
		if(!isset($bvalue['options'])) $bvalue['options'] = array();
		if(!isset($bvalue['other'])) $bvalue['other'] = '';
		if(!isset($bvalue['text'])) $bvalue['text'] = '';
		if(!isset($bvalue['check'])) $bvalue['check'] = '';
		if(!isset($bvalue['radio'])) $bvalue['radio'] = '';
		if(!isset($bvalue['size'])) $bvalue['size'] = '';
		if(!isset($theblcokvalue[$bkey])) $theblcokvalue[$bkey] = '';
		if(!isset($bvalue['width'])) $bvalue['width'] = '';
		$labelarr = array('type'=>$bvalue['type'], 'alang'=>$bvalue['alang'], 'name'=>$bkey, 'size'=>$bvalue['size'], 'text'=>$bvalue['text'], 'check'=>$bvalue['check'], 'radio'=>$bvalue['radio'], 'options'=>$bvalue['options'], 'other'=>$bvalue['other'], 'width'=>$bvalue['width'], 'value'=>$theblcokvalue[$bkey]);
		if($bkey == 'order') {
			if(!isset($theblcokvalue['order'])) $theblcokvalue['order'] = '';
			if(!isset($theblcokvalue['sc'])) $theblcokvalue['sc'] = '';
			$labelarr['order'] = $theblcokvalue['order'];
			$labelarr['sc'] = $theblcokvalue['sc'];
		}
		echo label($labelarr);
	}

	echo '<div class="buttons">';
	echo label(array('type'=>'button-submit', 'name'=>'thevaluesubmit', 'value'=>$alang['common_submit']));
	echo label(array('type'=>'button-reset', 'name'=>'thevaluereset', 'value'=>$alang['common_reset']));
	echo '</div>';
	echo '<input name="bbssubmit" type="hidden" value="yes" />';
	echo '<input name="catid" type="hidden" value="'.$bbsvalue['catid'].'" />';
	echo '<input name="type" type="hidden" value="'.$bbsvalue['type'].'" />';
	echo '<input name="start" type="hidden" value="0" />';
	echo '<input name="limit" type="hidden" value="10" />';
	echo '<input name="cachename" type="hidden" value="'.$_SGLOBAL['timestamp'].'" />';
	echo '<input name="tplname" type="hidden" value="data" />';
	echo label(array('type'=>'form-end'));

}

function getcategoriessunid($categoriesid) {
	global $_SGLOBAL;
	$return = $suncategories = array();
	$categoriesid = intval($categoriesid);
	if(empty($categoriesid)) return false;
	$query = $_SGLOBAL['db']->query("SELECT catid FROM ".tname('categories')." WHERE upid ='$categoriesid'");
	while($row = $_SGLOBAL['db']->fetch_array($query)) {
		$suncategories = getcategoriessunid(intval($row['catid']));
		$return = array_merge(array(intval($row['catid'])),$suncategories);
	}
	$return[] = $categoriesid;
	return array_unique($return);
}

?>