<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: advertisements.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';

cpheader();

if($action == 'adv') {

	if(!submitcheck('advsubmit')) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';

		$advs = $conditions = '';
		$order_by = 'displayorder, advid DESC, targets DESC';
		$page = max(1, intval($page));
		$start_limit = ($page - 1) * 15;

		if(submitcheck('searchsubmit')) {
			$conditions .= $title ? " AND title LIKE '%$title%'" : '';
			$conditions .= $type ? " AND type='$type'" : '';
			$conditions .= $starttime ? " AND starttime>='".($timestamp - $starttime)."'" : '';
			$order_by = $orderby == 'starttime' ? 'starttime' : ($orderby == 'type' ? 'type' : ($orderby == 'displayorder' ? 'displayorder' : 'advid DESC'));
		}

		$advnum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}advertisements WHERE 1 $conditions");

		$query = $db->query("SELECT * FROM {$tablepre}advertisements WHERE 1 $conditions ORDER BY $order_by LIMIT $start_limit, 15");
		while($adv = $db->fetch_array($query)) {
			$adv['type'] = $lang['advertisements_type_'.$adv['type']];

			if($adv['targets'] == '' || $adv['targets'] == 'forum') {
				$adv['targets'] = $lang['all'];
			} else {
				$targetsarray = array();
				foreach(explode("\t", $adv['targets']) as $target) {
					$targetsarray[] = $target == 'register' ? '<a href="'.$regname.'" target="_blank">'.$lang['advertisements_register'].'</a>' :
						($target == 'redirect' ? $lang['advertisements_jump'] :
						($target == 'archiver' ? '<a href="archiver/" target="_blank">Archiver</a>' :
						($target ? '<a href="forumdisplay.php?fid='.$target.'" target="_blank">'.$_DCACHE['forums'][$target]['name'].'</a>' : '<a href="'.$indexname.'" target="_blank">'.$lang['home'].'</a>')));
				}
				$adv['targets'] = implode(', ', $targetsarray);
			}

			$adv['parameters'] = unserialize($adv['parameters']);

			$advs .= "<tr align=\"center\" ".($adv['endtime'] && $adv['endtime'] <= $timestamp ? 'style="text-decoration: line-through"' : '')."><td class=\"altbg1\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$adv[advid]\"></td>".
				"<td class=\"altbg2\"><input class=\"checkbox\" type=\"checkbox\" name=\"availablenew[$adv[advid]]\" value=\"1\" ".($adv['available'] ? 'checked' : '')."></td>".
				"<td class=\"altbg1\"><input type=\"text\" size=\"2\" name=\"displayordernew[$adv[advid]]\" value=\"$adv[displayorder]\"></td>".
				"<td class=\"altbg2\"><input type=\"text\" size=\"15\" name=\"titlenew[$adv[advid]]\" value=\"".dhtmlspecialchars($adv['title'])."\"></td>".
				"<td class=\"altbg1\">$adv[type]</td>".
				"<td class=\"altbg2\">".$lang['advertisements_style_'.$adv['parameters']['style']]."</td>".
				"<td class=\"altbg1\">".($adv['starttime'] ? gmdate($dateformat, $adv['starttime'] + $_DCACHE['settings']['timeoffset'] * 3600) : $lang['unlimited'])."</td>".
				"<td class=\"altbg2\">".($adv['endtime'] ? gmdate($dateformat, $adv['endtime'] + $_DCACHE['settings']['timeoffset'] * 3600) : $lang['unlimited'])."</td>".
				"<td class=\"altbg1\">$adv[targets]</td>".
				"<td class=\"altbg2\"><a href=\"admincp.php?action=advedit&advid=$adv[advid]\">[$lang[detail]]</a></td></tr>";
		}

		$multipage = multi($advnum, 15, $page, "admincp.php?action=adv");
		shownav('menu_misc_advertisements');

		$starttimecheck = array($starttime => 'selected="selected"');
		$typecheck = array($type => 'selected="selected"');
		$orderbycheck = array($orderby => 'selected="selected"');
		$title = isset($title) ? $title : $lang['advertisement_inputtitle'];

?>
<form method="post" action="admincp.php?action=advadd">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td><?=$lang['advertisements_add']?></td></tr>
<tr><td class="category">
<?=$lang['advertisements_edit_title']?> <input style="vertical-align: middle" type="text" name="title" value="" size="25" maxlength="50"> &nbsp;&nbsp; <?=$lang['advertisements_edit_style']?> <select style="vertical-align: middle" name="style"><option value="code" <?=$styleselect['code']?>> <?=$lang['advertisements_style_code']?></option><option value="text" <?=$styleselect['text']?>> <?=$lang['advertisements_style_text']?></option><option value="image" <?=$styleselect['image']?>> <?=$lang['advertisements_style_image']?></option><option value="flash" <?=$styleselect['flash']?>> <?=$lang['advertisements_style_flash']?></option></select> &nbsp;&nbsp;
<select onchange="if(this.options[this.selectedIndex].value) {this.form.submit()}" style="vertical-align: middle" name="type"><option value=""> <?=$lang['advertisements_type']?></option><option value="headerbanner"> <?=$lang['advertisements_type_headerbanner']?></option><option value="footerbanner"> <?=$lang['advertisements_type_footerbanner']?></option><option value="text"> <?=$lang['advertisements_type_text']?></option><option value="thread"> <?=$lang['advertisements_type_thread']?></option><option value="interthread"> <?=$lang['advertisements_type_interthread']?></option><option value="float"> <?=$lang['advertisements_type_float']?></option><option value="couplebanner"> <?=$lang['advertisements_type_couplebanner']?></option><option value="intercat"> <?=$lang['advertisements_type_intercat']?></option></select>
</td></tr></table><br />
</form>

<form method="post" action="admincp.php?action=adv" onsubmit="if(this.title.value=='<?=$lang[advertisement_inputtitle]?>') this.title.value=''">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td><?=$multipage?></td>

<td style="text-align: right;"><input style="vertical-align: middle" type="text" name="title" value="<?=$title?>" size="15" onclick="this.value=''"> &nbsp;&nbsp;
<select style="vertical-align: middle" name="starttime"><option value=""> <?=$lang['start_time']?></option><option value="0" <?=$starttimecheck['0']?>> <?=$lang['all']?></option><option value="86400" <?=$starttimecheck['86400']?>> <?=$lang['1_day']?></option><option value="604800" <?=$starttimecheck['604800']?>> <?=$lang['7_day']?></option><option value="2592000" <?=$starttimecheck['2592000']?>> <?=$lang['30_day']?></option><option value="7776000" <?=$starttimecheck['7776000']?>> <?=$lang['90_day']?></option><option value="15552000" <?=$starttimecheck['15552000']?>> <?=$lang['180_day']?></option><option value="31536000" <?=$starttimecheck['31536000']?>> <?=$lang['365_day']?></option></select> &nbsp;&nbsp;
<select style="vertical-align: middle" name="type"><option value=""> <?=$lang['advertisements_type']?></option><option value="0" <?=$typecheck['0']?>> <?=$lang['all']?></option><option value="headerbanner" <?=$typecheck['headerbanner']?>> <?=$lang['advertisements_type_headerbanner']?></option><option value="footerbanner" <?=$typecheck['footerbanner']?>> <?=$lang['advertisements_type_footerbanner']?></option><option value="text" <?=$typecheck['text']?>> <?=$lang['advertisements_type_text']?></option><option value="thread" <?=$typecheck['thread']?>> <?=$lang['advertisements_type_thread']?></option><option value="interthread" <?=$typecheck['interthread']?>> <?=$lang['advertisements_type_interthread']?></option><option value="float" <?=$typecheck['float']?>> <?=$lang['advertisements_type_float']?></option><option value="couplebanner" <?=$typecheck['couplebanner']?>> <?=$lang['advertisements_type_couplebanner']?></option><option value="intercat" <?=$typecheck['intercat']?>> <?=$lang['advertisements_type_intercat']?></option></select>
<select style="vertical-align: middle" name="orderby"><option value=""> <?=$lang[advertisement_orderby]?></option><option value="starttime" <?=$orderbycheck['starttime']?>> <?=$lang[advertisement_addtime]?></option><option value="type" <?=$orderbycheck['type']?>> <?=$lang['advertisements_type']?></option><option value="displayorder" <?=$orderbycheck['displayorder']?>> <?=$lang['display_order']?></option></select> &nbsp;&nbsp; <input class="button" type="submit" name="searchsubmit" value="<?=$lang[search]?>" style="vertical-align: middle">

</td>

</tr></table></form>

<form method="post" action="admincp.php?action=adv">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td width="3%" nowrap><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form,'delete')"><?=$lang['del']?></td>
<td width="5%" nowrap><?=$lang['available']?></td>
<td width="8%" nowrap><?=$lang['display_order']?></td>
<td width="15%" nowrap><?=$lang['subject']?></td>
<td width="12%" nowrap><?=$lang['type']?></td>
<td width="5%" nowrap><?=$lang['advertisements_style']?></td>
<td width="10%" nowrap><?=$lang['start_time']?></td>
<td width="10%" nowrap><?=$lang['end_time']?></td>
<td width="20%" nowrap><?=$lang['advertisements_targets']?></td>
<td width="6%" nowrap><?=$lang['edit']?></td></tr>
<?=$advs?>
</table>
<?=$multipage?>
<br /><center><input class="button" type="submit" name="advsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

	} else {

		if($advids = implodeids($delete)) {
			$db->query("DELETE FROM {$tablepre}advertisements WHERE advid IN ($advids)");
		}

		if(is_array($titlenew)) {
			foreach($titlenew as $advid => $title) {
				$db->query("UPDATE {$tablepre}advertisements SET available='$availablenew[$advid]', displayorder='$displayordernew[$advid]', title='".cutstr($titlenew[$advid], 50)."' WHERE advid='$advid'", 'UNBUFFERED');
			}
		}

		updatecache(array('settings', 'advs_archiver', 'advs_register', 'advs_index', 'advs_forumdisplay', 'advs_viewthread'));

		cpmsg('advertisements_update_succeed', 'admincp.php?action=adv');

	}

} elseif($action == 'advadd' && in_array($type, array('headerbanner', 'footerbanner', 'text', 'thread', 'interthread', 'float', 'couplebanner', 'intercat')) || ($action == 'advedit' && $advid)) {

	if(!submitcheck('advsubmit')) {

		require_once DISCUZ_ROOT.'./include/forum.func.php';

		shownav('menu_misc_advertisements');

		if($action == 'advedit') {
			$adv = $db->fetch_first("SELECT * FROM {$tablepre}advertisements WHERE advid='$advid'");
			if(!$adv) {
				cpmsg('undefined_action');
			}
			$adv['parameters'] = unserialize($adv['parameters']);
			if(in_array($adv['type'], array('footerbanner', 'thread'))) {
				$adv['parameters']['position'] = isset($adv['parameters']['position']) && in_array($adv['parameters']['position'], array(2, 3)) ? $adv['parameters']['position'] : 1;
				$positionchecked = array($adv['parameters']['position'] => 'checked');
				if($adv['type'] == 'thread') {
					$dispchecked = array();
					foreach((isset($adv['parameters']['displayorder']) ? explode("\t", $adv['parameters']['displayorder']) : array('0')) AS $postcount) {
						$dispchecked[$postcount] = ' selected="selected"';
					}
				}
			} elseif($adv['type'] == 'intercat') {
				if(is_array($adv['parameters']['position'])) {
					$positionchecked = array();
					foreach($adv['parameters']['position'] AS $position) {
						$positionchecked[$position] = ' selected="selected"';
					}
				} else {
					$positionchecked = array(0 => ' selected="selected"');
				}
			}
			$type = $adv['type'];
		} else {
		        $title = cutstr($title, 50);
		        $style = in_array($style, array('text', 'image', 'flash')) ? $style : 'code';
			$adv = array('type' => $type, 'title' => $title, 'parameters' => array('style' => $style), 'starttime' => $timestamp);
			$positionchecked = $type == 'intercat' ? array(0 => ' selected="selected"') : array(1 => 'checked');
			$dispchecked = array(0 => ' selected="selected"');
		}

		$adv['targets'] = $adv['targets'] != '' && $adv['targets'] != 'forum' ? explode("\t", $adv['targets']) : array('all');

		if($type == 'intercat') {
			$targetsselect = '<select name="advnew[targets][]" selected="selected"><option value="0">&nbsp;&nbsp;> '.$lang['home'].'</option></select>';
		} else {
			$targetsselect = '<select name="advnew[targets][]" size="10" multiple="multiple"><option value="all">&nbsp;&nbsp;> '.$lang['all'].'</option>'.
				'<option value="">&nbsp;</option>'.
				(in_array($type, array('thread', 'interthread')) ? '' : '<option value="0">&nbsp;&nbsp;> '.$lang['home'].'</option>').
				(in_array($type, array('headerbanner', 'footerbanner')) ? '</option><option value="register">&nbsp;&nbsp;> '.$lang['advertisements_register'].'</option>'.
				'</option><option value="redirect">&nbsp;&nbsp;> '.$lang['advertisements_jump'].'</option>'.
				'</option><option value="archiver">&nbsp;&nbsp;> Archiver</option>' : '').
				'</option>'.forumselect().'</select>';

			foreach($adv['targets'] as $target) {
				$targetsselect = preg_replace("/(\<option value=\"$target\")(\>)/", "\\1 selected=\"selected\" \\2", $targetsselect);
			}
		}
		if($type == 'thread') {
			$dispselect = '<select name="advnew[displayorder][]" size="10" multiple="multiple"><option value="0"'.$dispchecked[0].'>&nbsp;&nbsp;> '.$lang['all'].'</option><option value="0">&nbsp;</option>';
			for($i = 1; $i <= $ppp; $i ++) {
				$dispselect .= '<option value="'.$i.'"'.$dispchecked[$i].'>&nbsp;&nbsp;> #'.$i.'</option>';
			}
			$dispselect .= '</select>';
		} elseif($type == 'intercat') {
			require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
			$positionselect = '<select name="advnew[position][]" size="10" multiple="multiple"><option value="0"'.$positionchecked[0].'>&nbsp;&nbsp;> '.$lang['all'].'</option><option value="">&nbsp;</option>';
			foreach($_DCACHE['forums'] AS $fid => $forum) {
				if($forum['type'] == 'group') {
					$positionselect .= '<option value="'.$fid.'"'.$positionchecked[$fid].'>'.$forum['name'].'</option>';
				}
			}
			$positionselect .= '</select>';
		}

		$adv['starttime'] = $adv['starttime'] ? gmdate('Y-n-j', $adv['starttime'] + $_DCACHE['settings']['timeoffset'] * 3600) : '';
		$adv['endtime'] = $adv['endtime'] ? gmdate('Y-n-j', $adv['endtime'] + $_DCACHE['settings']['timeoffset'] * 3600) : '';

		$styleselect = array($adv['parameters']['style'] => 'selected');

		showtips('advertisements_type_'.$adv['type'].'_tips');

?>
<script type="text/javascript" src="include/javascript/calendar.js"></script>
<form method="post" name="settings" action="admincp.php?action=<?=$action.($action == 'advadd' ? '&type='.$type : '&advid='.$advid)?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?

		if($action == 'advadd') {
			$title = $lang['advertisements_add'].' - '.$lang['advertisements_type_'.$type];
		} else {
			$title = $lang['advertisements_edit'].' - '.$lang['advertisements_type_'.$adv['type']].' - '.$adv['title'];
		}

		showtype($title, 'top');
		showsetting('advertisements_edit_style', '', '', '<select name="advnew[style]" onchange="var styles, key;styles=new Array(\'code\',\'text\',\'image\',\'flash\'); for(key in styles) {var obj=$(\'style_\'+styles[key]); obj.style.display=styles[key]==this.options[this.selectedIndex].value?\'\':\'none\';}"><option value="code" '.$styleselect['code'].'> '.$lang['advertisements_style_code'].'</option><option value="text" '.$styleselect['text'].'> '.$lang['advertisements_style_text'].'</option><option value="image" '.$styleselect['image'].'> '.$lang['advertisements_style_image'].'</option><option value="flash" '.$styleselect['flash'].'> '.$lang['advertisements_style_flash'].'</option></select>');
		showsetting('advertisements_edit_title', 'advnew[title]', $adv['title'], 'text');
		showsetting('advertisements_edit_targets', '', '', $targetsselect);
		if($adv['type'] == 'thread') {
			showsetting('advertisements_edit_position_thread', '', '', '<input type="radio" name="advnew[position]" class="radio" value="1" '.$positionchecked[1].'>'.$lang['advertisement_thread_down'].' &nbsp; <input type="radio" name="advnew[position]" class="radio" value="2" '.$positionchecked[2].'>'.$lang['advertisement_thread_up'].' &nbsp; <input type="radio" name="advnew[position]" class="radio" value="3" '.$positionchecked[3].'>'.$lang['advertisement_thread_right']);
			showsetting('advertisements_edit_display_position', '', '', $dispselect);
		} elseif($adv['type'] == 'footerbanner') {
			showsetting('advertisements_edit_position_footerbanner', '', '', '<input type="radio" name="advnew[position]" class="radio" value="1" '.$positionchecked[1].'>'.$lang['advertisement_up'].' &nbsp; <input type="radio" name="advnew[position]" class="radio" value="2" '.$positionchecked[2].'>'.$lang['advertisement_middle'].' &nbsp; <input type="radio" name="advnew[position]" class="radio" value="3" '.$positionchecked[3].'>'.$lang['advertisement_down']);
		} elseif($adv['type'] == 'intercat') {
			showsetting('advertisements_edit_position_intercat', '', '', $positionselect);
		} elseif($adv['type'] == 'float') {
			showsetting('advertisements_edit_floath', 'advnew[floath]', ($adv['parameters']['floath'] > 0 ? $adv['parameters']['floath'] : 200), 'text');
		}
		showsetting('advertisements_edit_starttime', 'advnew[starttime]', $adv['starttime'], 'calendar');
		showsetting('advertisements_edit_endtime', 'advnew[endtime]', $adv['endtime'], 'calendar');

		echo '<div>';
		showadvtype('code', $adv['parameters']['style']);
		showsetting('advertisements_edit_style_code_html', 'advnew[code][html]', $type == 'float' && $adv['parameters']['sourcecode'] ? $adv['parameters']['sourcecode'] : $adv['parameters']['html'], 'textarea');

		showadvtype('text', $adv['parameters']['style']);
		showsetting('advertisements_edit_style_text_title', 'advnew[text][title]', $adv['parameters']['title'], 'text');
		showsetting('advertisements_edit_style_text_link', 'advnew[text][link]', $adv['parameters']['link'], 'text');
		showsetting('advertisements_edit_style_text_size', 'advnew[text][size]', $adv['parameters']['size'], 'text');

		showadvtype('image', $adv['parameters']['style']);
		showsetting('advertisements_edit_style_image_url', 'advnew[image][url]', $adv['parameters']['url'], 'text');
		showsetting('advertisements_edit_style_image_link', 'advnew[image][link]', $adv['parameters']['link'], 'text');
		showsetting('advertisements_edit_style_image_width', 'advnew[image][width]', $adv['parameters']['width'], 'text');
		showsetting('advertisements_edit_style_image_height', 'advnew[image][height]', $adv['parameters']['height'], 'text');
		showsetting('advertisements_edit_style_image_alt', 'advnew[image][alt]', $adv['parameters']['alt'], 'text');

		showadvtype('flash', $adv['parameters']['style']);
		showsetting('advertisements_edit_style_flash_url', 'advnew[flash][url]', $adv['parameters']['url'], 'text');
		showsetting('advertisements_edit_style_flash_width', 'advnew[flash][width]', $adv['parameters']['width'], 'text');
		showsetting('advertisements_edit_style_flash_height', 'advnew[flash][height]', $adv['parameters']['height'], 'text');

		showtype('', 'bottom');

		echo '</div><center><input class="button" type="submit" name="advsubmit" value="'.$lang['submit'].'"></center></form>';

	} else {

		$advnew['starttime'] = $advnew['starttime'] ? strtotime($advnew['starttime']) : 0;
		$advnew['endtime'] = $advnew['endtime'] ? strtotime($advnew['endtime']) : 0;

		if(!$advnew['title']) {
			cpmsg('advertisements_title_invalid');
		} elseif(strlen($advnew['title']) > 50) {
			cpmsg('advertisements_title_more');
		} elseif($advnew['endtime'] && ($advnew['endtime'] <= $timestamp || $advnew['endtime'] <= $advnew['starttime'])) {
			cpmsg('advertisements_endtime_invalid');
		} elseif(($advnew['style'] == 'code' && !$advnew['code']['html'])
			|| ($advnew['style'] == 'text' && (!$advnew['text']['title'] || !$advnew['text']['link']))
			|| ($advnew['style'] == 'image' && (!$advnew['image']['url'] || !$advnew['image']['link']))
			|| ($advnew['style'] == 'flash' && (!$advnew['flash']['url'] || !$advnew['flash']['width'] || !$advnew['flash']['height']))) {
			cpmsg('advertisements_parameter_invalid');
		}

		if($action == 'advadd') {
			$db->query("INSERT INTO {$tablepre}advertisements (available, type)
				VALUES ('1', '$type')");
			$advid = $db->insert_id();
		} else {
			$type = $db->result_first("SELECT type FROM {$tablepre}advertisements WHERE advid='$advid'");
		}

		foreach($advnew[$advnew['style']] as $key => $val) {
			$advnew[$advnew['style']][$key] = stripslashes($val);
		}

		$targetsarray = array();
		if(is_array($advnew['targets'])) {
			foreach($advnew['targets'] as $target) {
				if($target == 'all') {
					$targetsarray = in_array($type, array('thread', 'interthread')) ? array('forum') : array();
					break;
				} elseif(in_array($target, array('register', 'redirect', 'archiver')) || preg_match("/^\d+$/", $target) && ($target == 0 || in_array($_DCACHE['forums'][$target]['type'], array('forum', 'sub')))) {
					$targetsarray[] = $target;
				}
			}
		}
		$advnew['targets'] = implode("\t", $targetsarray);
		$advnew['displayorder'] = isset($advnew['displayorder']) ? implode("\t", $advnew['displayorder']) : '';
		switch($advnew['style']) {
			case 'code':
				$advnew['code'] = $advnew['code']['html'];
				break;
			case 'text':
				$advnew['code'] = '<a href="'.$advnew['text']['link'].'" target="_blank" '.($advnew['text']['size'] ? 'style="font-size: '.$advnew['text']['size'].'"' : '').'>'.$advnew['text']['title'].'</a>';
				break;
			case 'image':
				$advnew['code'] = '<a href="'.$advnew['image']['link'].'" target="_blank"><img src="'.$advnew['image']['url'].'"'.($advnew['image']['height'] ? ' height="'.$advnew['image']['height'].'"' : '').($advnew['image']['width'] ? ' width="'.$advnew['image']['width'].'"' : '').($advnew['image']['alt'] ? ' alt="'.$advnew['image']['alt'].'"' : '').' border="0"></a>';
				break;
			case 'flash':
				$advnew['code'] = '<embed width="'.$advnew['flash']['width'].'" height="'.$advnew['flash']['height'].'" src="'.$advnew['flash']['url'].'" type="application/x-shockwave-flash"></embed>';
				break;
		}

		if($type == 'float') {
			$sourcecode = $advnew['code'];
			$advnew['floath'] = $advnew['floath'] >= 40 && $advnew['floath'] <= 600 ? intval($advnew['floath']) : 200;
			$advnew['code'] = str_replace(array("\r\n", "\r", "\n"), '<br />', $advnew['code']);
			$advnew['code'] = addslashes($advnew['code'].'<br /><img src="images/common/advclose.gif" onMouseOver="this.style.cursor=\'pointer\'" onClick="closeBanner();">');
			$advnew['code'] = 'theFloaters.addItem(\'floatAdv1\',6,\'document.documentElement.clientHeight-'.$advnew['floath'].'\',\'<div style="position: absolute; left: 6px; top: 6px;">'.$advnew['code'].'</div>\');';
		} elseif($type == 'couplebanner') {
			$advnew['code'] = addslashes($advnew['code'].'<br /><img src="images/common/advclose.gif" onMouseOver="this.style.cursor=\'pointer\'" onClick="closeBanner();">');
			$advnew['code'] = 'theFloaters.addItem(\'coupleBannerL\',6,0,\'<div style="position: absolute; left: 6px; top: 6px;">'.$advnew['code'].'</div>\');theFloaters.addItem(\'coupleBannerR\',\'document.body.clientWidth-6\',0,\'<div style="position: absolute; right: 6px; top: 6px;">'.$advnew['code'].'</div>\');';
		} elseif($type == 'intercat') {
			$advnew['position'] = is_array($advnew['position']) && !in_array('0', $advnew['position']) ? $advnew['position'] : '';
		}

		if($advnew['style'] == 'code') {
			$advnew['parameters'] = addslashes(serialize(array_merge(array('style' => $advnew['style']), array('html' => $advnew['code']), array('position' => $advnew['position']), array('displayorder' => $advnew['displayorder']), ($sourcecode ? array('sourcecode' => $sourcecode) : array()), ($advnew['floath'] ? array('floath' => $advnew['floath']) : array()))));
		} else {
			$advnew['parameters'] = addslashes(serialize(array_merge(array('style' => $advnew['style']), $advnew[$advnew['style']], array('html' => $advnew['code']), array('position' => $advnew['position']), array('displayorder' => $advnew['displayorder']), ($advnew['floath'] ? array('floath' => $advnew['floath']) : array()))));
		}
		$advnew['code'] = addslashes($advnew['code']);

		$query = $db->query("UPDATE {$tablepre}advertisements SET title='$advnew[title]', targets='$advnew[targets]', parameters='$advnew[parameters]', code='$advnew[code]', starttime='$advnew[starttime]', endtime='$advnew[endtime]' WHERE advid='$advid'");

		if($type == 'intercat') {
			updatecache('advs_index');
		} elseif(in_array($type, array('thread', 'interthread'))) {
			updatecache('advs_viewthread');
		} elseif($type == 'text') {
			updatecache(array('advs_index', 'advs_forumdisplay', 'advs_viewthread'));
		} else {
			updatecache(array('settings', 'advs_archiver', 'advs_register', 'advs_index', 'advs_forumdisplay', 'advs_viewthread'));
		}

		cpmsg('advertisements_succeed', "admincp.php?action=adv");

	}

}

function showadvtype($type, $curtype) {
	echo 	'</table><br /></div><div id="style_'.$type.'" style="'.($type != $curtype ? 'display: none' : '').'" class="maintablediv">'.
		'<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
		'<tr class="header"><td colspan="2">'.$GLOBALS['lang']['advertisements_edit_style_'.$type].'</td></tr>';
}

?>