<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_settings.php 11948 2009-04-15 06:38:46Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('managesettings')) {
	showmessage('no_authority_management_operation');
}

$listarr = array();
$thevalue = array();

//POST METHOD
if (submitcheck('thevaluesubmit')) {
	
	$replacearr = array();
	unset($_POST['thevaluesubmit']);
	unset($_POST['slrssupdatetime']);
	if(empty($_POST['addfeed'])) $_POST['addfeed'] = array(1=>0, 2=>0);
	foreach ($_POST as $var => $value) {
		if($var == 'checkgrade') {
			$value = implode("\t", $value);
		} elseif($var == 'thumbarray') {
			$value = serialize($value);
		} elseif($var == 'addfeed') {
			$value = bindec(intval($value[2]).intval($value[1]));
			$var = 'customaddfeed';
		}
		$replacearr[] = '(\''.$var.'\', \''.$value.'\')';
	}
	$_SGLOBAL['db']->query('REPLACE INTO '.tname('settings').' (variable, value) VALUES '.implode(',', $replacearr));
	
	//更新发送邮件设置
	sendmailset();
	
	//CACHE
	include_once(S_ROOT.'./function/cache.func.php');
	updatesettingcache();

	//更新论坛配置
	if(discuz_exists()) {
		dbconnect(1);
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('settings', 1).' SET `value` = \''.$_POST['sitename'].'\' WHERE `variable` = \'supe_sitename\'');
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('settings', 1).' SET `value` = \'1\' WHERE `variable` = \'supe_status\'');
		$_SGLOBAL['db_bbs']->query('UPDATE '.tname('settings', 1).' SET `value` = \''.S_URL_ALL.'\' WHERE `variable` = \'supe_siteurl\'');
	}
	
	showmessage('setting_update_success', $theurl.(empty($_GET['subtype'])?'':'#'.$_GET['subtype']));
}

//GET METHOD
if (empty($_GET['op'])) {

	$thevalue = array();
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('settings'));
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$thevalue[$value['variable']] = $value['value'];
	}
	if(empty($thevalue)) $thevalue = $_SCONFIG;

	//缩略图设置
	if(empty($thevalue['thumbarray'])) {
		$thevalue['thumbarray'] = array(
			'news' => array('300','250')
		);
	} else {
		$thevalue['thumbarray'] = unserialize($thevalue['thumbarray']);
	}

	if(!isset($thevalue['viewspace_pernum'])) $thevalue['viewspace_pernum'] = 20;
	if(!isset($thevalue['noseccode'])) $thevalue['noseccode'] = 0;
	
	//资讯干扰码
	if(!isset($thevalue['newsjammer'])) $thevalue['newsjammer'] = 0;
	if(!isset($thevalue['updateview'])) $thevalue['updateview'] = 1;

	//评论时间限制设置
	if(!isset($thevalue['commenttime'])) $thevalue['commenttime'] = 30;

	//首页显示友情链接
	if(empty($thevalue['showindex'])) $thevalue['showindex'] = 0;
	//非html化
	$thevalue = shtmlspecialchars($thevalue);

}

echo '
<table summary="" id="pagehead" cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td><h1>'.$alang['setting_title'].'</h1></td>
		<td class="actions">
		</td>
	</tr>
</table>
';

//THE VALUE SHOW
if(is_array($thevalue) && $thevalue) {

	$pconnectarr = array(
		'0' => $alang['setting_pconnect_0'],
		'1' => $alang['setting_pconnect_1']
	);
	$dbreportarr = array(
		'0' => $alang['setting_dbreport_0'],
		'1' => $alang['setting_dbreport_1']	
	);
	
	$timeoffsetarr = array(
		'-12' => '(GMT -12:00) Eniwetok, Kwajalein',
		'-11' => '(GMT -11:00) Midway Island, Samoa',
		'-10' => '(GMT -10:00) Hawaii',
		'-9' => '(GMT -09:00) Alaska',
		'-8' => '(GMT -08:00) Pacific Time (US & Canada), Tijuana',
		'-7' => '(GMT -07:00) Mountain Time (US & Canada), Arizona',
		'-6' => '(GMT -06:00) Central Time (US & Canada), Mexico City',
		'-5' => '(GMT -05:00) Eastern Time (US & Canada), Bogota, Lima, Quito',
		'-4' => '(GMT -04:00) Atlantic Time (Canada), Caracas, La Paz',
		'-3.5' => '(GMT -03:30) Newfoundland',
		'-3' => '(GMT -03:00) Brassila, Buenos Aires, Georgetown, Falkland Is',
		'-2' => '(GMT -02:00) Mid-Atlantic, Ascension Is., St. Helena',
		'-1' => '(GMT -01:00) Azores, Cape Verde Islands',
		'0' => '(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia',
		'1' => '(GMT +01:00) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome',
		'2' => '(GMT +02:00) Cairo, Helsinki, Kaliningrad, South Africa',
		'3' => '(GMT +03:00) Baghdad, Riyadh, Moscow, Nairobi',
		'3.5' => '(GMT +03:30) Tehran',
		'4' => '(GMT +04:00) Abu Dhabi, Baku, Muscat, Tbilisi',
		'4.5' => '(GMT +04:30) Kabul',
		'5' => '(GMT +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent',
		'5.5' => '(GMT +05:30) Bombay, Calcutta, Madras, New Delhi',
		'5.75' => '(GMT +05:45) Katmandu',
		'6' => '(GMT +06:00) Almaty, Colombo, Dhaka, Novosibirsk',
		'6.5' => '(GMT +06:30) Rangoon',
		'7' => '(GMT +07:00) Bangkok, Hanoi, Jakarta',
		'8' => '(GMT +08:00) Beijing, Hong Kong, Perth, Singapore, Taipei',
		'9' => '(GMT +09:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk',
		'9.5' => '(GMT +09:30) Adelaide, Darwin',
		'10' => '(GMT +10:00) Canberra, Guam, Melbourne, Sydney, Vladivostok',
		'11' => '(GMT +11:00) Magadan, New Caledonia, Solomon Islands',
		'12' => '(GMT +12:00) Auckland, Wellington, Fiji, Marshall Island'
	);
	
	$templatearr = sreaddir(S_ROOT.'./templates');
	
	$gzipcompressarr = array(
		'0' => $alang['setting_gzipcompress_0'],
		'1' => $alang['setting_gzipcompress_1']	
	);
	
	$urltypearr = array(
		'1' => $alang['setting_urltype_1'],
		'4' => $alang['setting_urltype_4'],
		'2' => $alang['setting_urltype_2'],
		'5' => $alang['setting_urltype_5'],
		'3' => $alang['setting_urltype_3'],
	);
	
	$bbsurltypearr = array(
		'site' => $alang['setting_bbsurltype_site'],
		'bbs' => $alang['setting_bbsurltype_bbs']
	);
	
	$attachmentdirtypearr = array(
		'all' => $alang['setting_attachmentdirtype_all'],
		'year' => $alang['setting_attachmentdirtype_year'],
		'month' => $alang['setting_attachmentdirtype_month'],
		'day' => $alang['setting_attachmentdirtype_day'],
		'md5' => $alang['setting_attachmentdirtype_md5']
	);
	
	$debugarr = array(
		'0' => $alang['setting_debug_0'],
		'1' => $alang['setting_debug_1']
	);
	
	$watermarkarr = array(
		'0' => $alang['setting_watermark_0'],
		'1' => $alang['setting_watermark_1']
	);
	
	$watermarkstatustext = '';
	$watermarkstatustext .= '<table cellspacing="0" cellpadding="0" class="watermark"><tr align="center">';
	for ($i=0; $i<9; $i++) {
		$watermarkstatustext .= '<td><input type="radio" name="watermarkstatus" value="'.($i+1).'"> #'.($i+1).'</td>';
		if($i%3==2) $watermarkstatustext .= '</tr><tr>';
	}
	$watermarkstatustext .= '</tr></table>';
	$watermarkstatustext = str_replace('value="'.$thevalue['watermarkstatus'].'"', 'value="'.$thevalue['watermarkstatus'].'" checked', $watermarkstatustext);
	
	$thumboptionarr = array(
		'4' => $alang['setting_thumboption_4'],
		'8' => $alang['setting_thumboption_8'],
		'16' => $alang['setting_thumboption_16']
	);
	
	$thumbcutmodearr = array(
		'0' => $alang['setting_thumbcutmode_0'],
		'1' => $alang['setting_thumbcutmode_1'],
		'2' => $alang['setting_thumbcutmode_2'],
		'3' => $alang['setting_thumbcutmode_3']
	);
	
	$allowguestarr = array(
		'0' => $alang['setting_allowguest_0'],
		'1' => $alang['setting_allowguest_1']
	);
	
	$allowregister = array(
		'0' => $alang['setting_allowguest_0'],
		'1' => $alang['setting_allowguest_1']
	);
	
	$allowcachearr = array(
		'0' => $alang['setting_allowcache_0'],
		'1' => $alang['setting_allowcache_1']
	);

	//tag
	$allowtagshowarr = array(
		'0' => $alang['setting_allowtagshow_0'],
		'1' => $alang['setting_allowtagshow_1']
	);
	
	$needcheckarr = array(
		'0' => $alang['setting_needcheck_0'],
		'1' => $alang['setting_needcheck_1']
	);

	if(!empty($thevalue['checkgrade'])) {
		$checkgrade = explode("\t", $thevalue['checkgrade']);
	} else {
		$checkgrade = array();
	}
	$checkgradestr = '<table>';
	for($i=1; $i<6; $i++) {
		if(empty($checkgrade[$i-1])) $checkgrade[$i-1] = '';
		$checkgradestr .= '<tr><td>'.$alang['check_grade_'.$i].'</td><td><input type="text" name="checkgrade[]" value="'.$checkgrade[$i-1].'" size="20"></td></tr>';
	}
	$checkgradestr .= '</table>';

	$imagemodearr = array(
		'item' => $alang['setting_imagemode_item'],
		'image' => $alang['setting_imagemode_image']
	);
	
	$htmltimearr = array(
		'' => '------',
		'300' => $alang['setting_htmltime_5minute'],
		'600' => $alang['setting_htmltime_10minute'],
		'900' => $alang['setting_htmltime_15minute'],
		'1200' => $alang['setting_htmltime_20minute'],
		'1500' => $alang['setting_htmltime_25minute'],
		'1800' => $alang['setting_htmltime_30minute'],
		'3600' => $alang['setting_htmltime_1hour'],
		'7200' => $alang['setting_htmltime_2hour'],
		'10800' => $alang['setting_htmltime_3hour'],
		'14400' => $alang['setting_htmltime_4hour'],
		'18000' => $alang['setting_htmltime_5hour'],
		'21600' => $alang['setting_htmltime_6hour'],
		'43200' => $alang['setting_htmltime_12hour'],//12
		'86400' => $alang['setting_htmltime_1day'],//24 h
		'172800' => $alang['setting_htmltime_2day'],//2 day
		'259200' => $alang['setting_htmltime_3day'],//3
		'604800' => $alang['setting_htmltime_1week'],//1 week
		'1209600' => $alang['setting_htmltime_2week'],//2
		'1814400' => $alang['setting_htmltime_3week'],//3
		'2592000' => $alang['setting_htmltime_1month'],//1 month
		'15520000' => $alang['setting_htmltime_6month'],//6
		'31536000' => $alang['setting_htmltime_1year']//1 year
	);
	
	$closesitearr = array(
		'0' => $alang['setting_closesite_0'],
		'1' => $alang['setting_closesite_1']
	);
	
	$cachemodearr = array(
		'database' => $alang['setting_cachemode_database'],
		'file' => $alang['setting_cachemode_file']
	);
	
	$jammerarr = array(
			'0' => $alang['not_to_create_watermark'],
			'1' => $alang['create_watermark']
	);

	$updateviewarr = array(
			'0' => $alang['not_view_update'],
			'1' => $alang['view_update']
	);
	
	echo label(array('type'=>'form-start', 'name'=>'thevalueform', 'action'=>CPURL.'?action=settings'));
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'help', 'text'=>$alang['help_settings']));
	echo label(array('type'=>'div-end'));
	echo '<a name="base"></a>';
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>$alang['admincp_site_set']));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'setting_title_sitename', 'name'=>'sitename', 'value'=>$thevalue['sitename']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_closesite', 'name'=>'closesite', 'options'=>$closesitearr, 'value'=>$thevalue['closesite']));
	echo label(array('type'=>'textarea', 'alang'=>'setting_closemessage', 'name'=>'closemessage', 'rows'=>3, 'value'=>$thevalue['closemessage']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_template', 'name'=>'template', 'options'=>$templatearr, 'value'=>$thevalue['template']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_attachmentdirtype', 'name'=>'attachmentdirtype', 'options'=>$attachmentdirtypearr, 'value'=>$thevalue['attachmentdirtype']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_register', 'name'=>'allowregister', 'options'=>$allowregister, 'value'=>$thevalue['allowregister']));
	echo label(array('type'=>'textarea', 'alang'=>'setting_registerrule', 'name'=>'registerrule', 'rows'=>3, 'value'=>$thevalue['registerrule']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_allowcache', 'name'=>'allowcache', 'options'=>$allowcachearr, 'value'=>$thevalue['allowcache']));
	//缓存模式
	echo label(array('type'=>'select', 'alang'=>'setting_title_cachemode', 'name'=>'cachemode', 'options'=>$cachemodearr, 'value'=>$thevalue['cachemode']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_allowguest', 'name'=>'allowguest', 'options'=>$allowguestarr, 'value'=>$thevalue['allowguest']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_allowguestsearch', 'name'=>'allowguestsearch', 'options'=>$allowguestarr, 'value'=>$thevalue['allowguestsearch']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_allowguestdownload', 'name'=>'allowguestdownload', 'options'=>$allowguestarr, 'value'=>$thevalue['allowguestdownload']));
	//tag show
	echo label(array('type'=>'select', 'alang'=>'setting_title_allowtagshow', 'name'=>'allowtagshow', 'options'=>$allowtagshowarr, 'value'=>$thevalue['allowtagshow']));
	echo label(array('type'=>'input', 'alang'=>'setting_title_miibeian', 'name'=>'miibeian', 'value'=>$thevalue['miibeian']));	
	echo label(array('type'=>'input', 'alang'=>'setting_title_search_wait', 'name'=>'searchinterval', 'size'=>'10', 'value'=>$thevalue['searchinterval']));
	if(discuz_exists()) echo label(array('type'=>'select', 'alang'=>'setting_title_bbsurltype', 'name'=>'bbsurltype', 'options'=>$bbsurltypearr, 'value'=>$thevalue['bbsurltype']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	print<<<EOF
	<div class="buttons">
		<input type="submit" name="thevaluesubmit" value="$alang[common_submit]" class="submit" onclick="this.form.action+='&subtype=base';">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
EOF;
	
	echo '<a name="dir"></a>';
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'setting_title_dir'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'setting_title_attachmentdir', 'name'=>'attachmentdir', 'value'=>$thevalue['attachmentdir']));
	echo label(array('type'=>'input', 'alang'=>'setting_title_attachmenturl', 'name'=>'attachmenturl', 'value'=>$thevalue['attachmenturl']));
	echo label(array('type'=>'input', 'alang'=>'setting_title_htmldir', 'name'=>'htmldir', 'value'=>$thevalue['htmldir']));
	echo label(array('type'=>'input', 'alang'=>'setting_title_htmlurl', 'name'=>'htmlurl', 'value'=>$thevalue['htmlurl']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	print<<<EOF
	<div class="buttons">
		<input type="submit" name="thevaluesubmit" value="$alang[common_submit]" class="submit" onclick="this.form.action+='&subtype=dir';">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
EOF;

	//缩略图设置
	echo '<a name="thumb"></a>';
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'setting_title_thumb'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'setting_title_thumbbgcolor', 'name'=>'thumbbgcolor', 'value'=>$thevalue['thumbbgcolor']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_thumboption', 'name'=>'thumboption', 'options'=>$thumboptionarr, 'value'=>$thevalue['thumboption']));
	echo label(array('type'=>'select', 'alang'=>'setting_title_thumbcutmode', 'name'=>'thumbcutmode', 'options'=>$thumbcutmodearr, 'value'=>$thevalue['thumbcutmode']));
	echo label(array('type'=>'input', 'alang'=>'setting_title_thumbcutstartx', 'name'=>'thumbcutstartx', 'value'=>$thevalue['thumbcutstartx']));
	echo label(array('type'=>'input', 'alang'=>'setting_title_thumbcutstarty', 'name'=>'thumbcutstarty', 'value'=>$thevalue['thumbcutstarty']));
	print<<<END
	<tr><th>$alang[thumbnail_specifications_news]</th><td>$alang[width] <input type="text" name="thumbarray[news][0]" value="{$thevalue['thumbarray']['news'][0]}" size="5"> $alang[pixel], $alang[height] <input type="text" name="thumbarray[news][1]" value="{$thevalue['thumbarray']['news'][1]}" size="5"> $alang[pixel]</td></tr>
END;
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	print<<<EOF
	<div class="buttons">
		<input type="submit" name="thevaluesubmit" value="$alang[common_submit]" class="submit" onclick="this.form.action+='&subtype=thumb';">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
EOF;
	
	echo '<a name="watermark"></a>';
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'setting_title_swatermark'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'radio', 'alang'=>'setting_title_watermark', 'name'=>'watermark', 'options'=>$watermarkarr, 'value'=>$thevalue['watermark']));
	echo label(array('type'=>'input', 'alang'=>'setting_title_watermarkfile', 'name'=>'watermarkfile', 'value'=>$thevalue['watermarkfile']));
	echo label(array('type'=>'text', 'alang'=>'setting_title_watermarkstatus', 'text'=>$watermarkstatustext));
	echo label(array('type'=>'input', 'alang'=>'setting_title_watermarktrans', 'name'=>'watermarktrans', 'value'=>$thevalue['watermarktrans']));
	echo label(array('type'=>'input', 'alang'=>'setting_title_watermarkjpgquality', 'name'=>'watermarkjpgquality', 'value'=>$thevalue['watermarkjpgquality']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	print<<<EOF
	<div class="buttons">
		<input type="submit" name="thevaluesubmit" value="$alang[common_submit]" class="submit" onclick="this.form.action+='&subtype=watermark';">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
EOF;

	//RSS设置
	echo '<a name="rss"></a>';
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'setting_title_rss'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'input', 'alang'=>'setting_title_rssnum', 'name'=>'rssnum', 'value'=>$thevalue['rssnum']));
	echo label(array('type'=>'select-input', 'alang'=>'setting_title_rssupdatetime', 'name'=>'rssupdatetime', 'size'=>10, 'options'=>$htmltimearr, 'value'=>$thevalue['rssupdatetime']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));

	print<<<EOF
	<div class="buttons">
		<input type="submit" name="thevaluesubmit" value="$alang[common_submit]" class="submit" onclick="this.form.action+='&subtype=rss';">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
EOF;
	
	//搜索引擎优化
	echo '<a name="seo"></a>';
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>'search_engine_optimization'));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'select', 'alang'=>'setting_title_urltype', 'name'=>'urltype', 'options'=>$urltypearr, 'value'=>$thevalue['urltype']));
	echo label(array('type'=>'input', 'alang'=>'setting_title_pagepostfix', 'name'=>'pagepostfix', 'value'=>$thevalue['pagepostfix']));

	echo label(array('type'=>'input', 'alang'=>'additional_title_character', 'name'=>'seotitle', 'value'=>$thevalue['seotitle']));
	echo label(array('type'=>'input', 'alang'=>'Meta Keywords', 'name'=>'seokeywords', 'value'=>$thevalue['seokeywords']));
	echo label(array('type'=>'input', 'alang'=>'Meta Description', 'name'=>'seodescription', 'value'=>$thevalue['seodescription']));
	echo label(array('type'=>'textarea', 'alang'=>'other_information_head', 'name'=>'seohead', 'cols'=>80, 'rows'=>6, 'value'=>$thevalue['seohead']));
	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	print<<<EOF
	<div class="buttons">
		<input type="submit" name="thevaluesubmit" value="$alang[common_submit]" class="submit" onclick="this.form.action+='&subtype=seo';">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
EOF;
	
	//其他设置
	$noseccode = array('', '');
	$noseccode[$thevalue['noseccode']] = ' checked';
	$usejammer = array('', '');
	$usejammer[$thevalue['newsjammer']] = ' checked';

	echo '<a name="other"></a>';
	echo label(array('type'=>'div-start'));
	echo label(array('type'=>'title', 'alang'=>$alang['admincp_header_else']));
	echo label(array('type'=>'table-start'));
	echo label(array('type'=>'radio', 'alang'=>'setting_title_gzipcompress', 'name'=>'gzipcompress', 'options'=>$gzipcompressarr, 'value'=>$thevalue['gzipcompress']));
	echo label(array('type'=>'radio', 'alang'=>'setting_title_debug', 'name'=>'debug', 'options'=>$debugarr, 'value'=>$thevalue['debug']));
	echo label(array('type'=>'radio', 'alang'=>'setting_title_needcheck', 'name'=>'needcheck', 'options'=>$needcheckarr, 'value'=>$thevalue['needcheck']));
	echo label(array('type'=>'radio', 'alang'=>'setting_title_updateview', 'name'=>'updateview', 'options'=>$updateviewarr, 'value'=>$thevalue['updateview']));
	echo label(array('type'=>'text', 'alang'=>'setting_title_checkgrade', 'text' => $checkgradestr));
	echo label(array('type'=>'select', 'alang'=>'setting_title_timeoffset', 'name'=>'timeoffset', 'options'=>$timeoffsetarr, 'value'=>$thevalue['timeoffset']));
	print<<<END
	<tr><th>$alang[functional_verification_codes_prohibit]</th><td><input type="radio" name="noseccode" value="0"$noseccode[0]> $alang[verification_codes_1] <input type="radio" name="noseccode" value="1"$noseccode[1]> $alang[verification_codes_0] </td></tr>
	<tr><th>$alang[watermark_article]</th><td><input type="radio" name="newsjammer" value="1"$usejammer[1]> $alang[create_watermark] <input type="radio" name="newsjammer" value="0"$usejammer[0]> $alang[not_to_create_watermark] </td></tr>
	<tr><th>$alang[show_linknum]</th><td><input type="text" name="showindex" value="$thevalue[showindex]" size=10></td></tr>
	<tr><th>$alang[commenttime_help]</th><td><input type="text" name="commenttime" value="$thevalue[commenttime]" size=10></td></tr>
	<tr><th>$alang[comments_and_the_number_of_shows]</th><td><input type="text" name="viewspace_pernum" value="$thevalue[viewspace_pernum]" size="10"></td></tr>
END;

	$allowfeedchecks = empty($thevalue['allowfeed']) ? array('checked="checked"', '') : array('', 'checked="checked"');
	$feedchecks = array();
	$customaddfeed = intval($thevalue['customaddfeed']);
	$feedchecks[1] = ($customaddfeed & 1) ? 'checked="checked"' : '';
	$feedchecks[2] = ($customaddfeed & 2) ? 'checked="checked"' : '';
	print<<<END
	<tr><th>$alang[setting_allowfeed]</th><td><input type="radio" name="allowfeed" value="1" $allowfeedchecks[1]> $alang[setting_needcheck_1] <input type="radio" name="allowfeed" value="0" $allowfeedchecks[0]> $alang[setting_needcheck_0]</td></tr>
	<tr><th>$alang[setting_customaddfeed]</th><td><input type="checkbox" name="addfeed[1]" value="1" $feedchecks[1]> $alang[setting_customaddfeed_1] <input type="checkbox" name="addfeed[2]" value="1" $feedchecks[2]> $alang[setting_customaddfeed_2]</td></tr>
END;

	echo label(array('type'=>'table-end'));
	echo label(array('type'=>'div-end'));
	
	$mailarr = array($_SC['mailsend'] => 'checked="checked"');
	$smtparr = ($_SC['mailsend'] == 2) ? array() : (($_SC['mailsend'] == 1) ? array(2=> 'style="display:none;"', 1=> 'style="display:none;"') : array(2=> 'style="display:none;"') );
	$maillimitarr = array($mailcfg['maildelimiter']=>'checked="checked"');
	$autharr = $mailcfg['auth'] ? array(1=>'checked="checked"') : array(0=>'checked="checked"');
	$mailuarr = $mailcfg['mailusername'] ? array(1=>'checked="checked"') : array(0=>'checked="checked"');

	print<<<EOF
	<div class="buttons">
		<input type="submit" name="thevaluesubmit" value="$alang[common_submit]" class="submit" onclick="this.form.action+='&subtype=other';">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
EOF;
	
	print <<<EOF
	<div class="colorarea02">
	<h2>$alang[mail_set]</h2>
	<table cellspacing="0" cellpadding="0" width="100%" class="maintable">
	<tbody><tr>
	<th style="width: 15em;">$alang[mail_send_mode]</th>
	<td><input type="radio" onclick="$('tb_smtp1').style.display = 'none';$('tb_smtp2').style.display = 'none';" $mailarr[1] value="1" name="mail[mailsend]" class="radio"/> $alang[mail_mode_php_sendmail]<br/>
	<input type="radio" onclick="$('tb_smtp1').style.display = '';$('tb_smtp2').style.display = '';" $mailarr[2] value="2" name="mail[mailsend]" class="radio"/> $alang[mail_send_socket_esmtp]<br/>
	<input type="radio" onclick="$('tb_smtp1').style.display = '';$('tb_smtp2').style.display = 'none';" $mailarr[3] value="3" name="mail[mailsend]" class="radio"/> $alang[mail_mode_php_smtp]
	</td>
	</tr><tr>
	<th>$alang[mail_header_sign]</th>
	<td><input type="radio" value="0" name="mail[maildelimiter]" $maillimitarr[0] class="radio"/> $alang[mail_header_lf]<br/>
	<input type="radio" value="1" name="mail[maildelimiter]" $maillimitarr[1] class="radio"/> $alang[mail_header_crlf]<br/>
	<input type="radio" value="2" name="mail[maildelimiter]" $maillimitarr[2] class="radio"/> $alang[mail_header_cr]
	</td>
	</tr><tr>
	<th>$alang[accept_username]</th>
	<td><input type="radio" checked="" value="1" name="mail[mailusername]" $mailuarr[1] class="radio"/> $alang[space_yes]    
	<input type="radio" value="0" name="mail[mailusername]" $mailuarr[0] class="radio"/> $alang[space_no]    
	</td>
	</tr>
	
	</tbody>
	<tbody id="tb_smtp1" $smtparr[1] >
	<tr><th> </th>
	<td>
	<table>
	<tbody><tr>
	<td width="120">$alang[smtp_server]</td>
	<td><input type="text" value="$mailcfg[server]" name="mail[server]"/></td>
	</tr><tr>
	<td>$alang[smtp_port]</td>
	<td><input type="text" size="5" value="$mailcfg[port]" name="mail[port]"/>$alang[smtp_default_port]</td>
	</tr>
	</tbody>
	
	<tbody id="tb_smtp2" $smtparr[2] >
	<tr>
	<td>$alang[require_validate]</td>
	<td><input type="radio" checked="" value="1" name="mail[auth]" $autharr[1] class="radio"/> $alang[space_yes]     
	<input type="radio" value="0" name="mail[auth]" $autharr[0] class="radio"/> $alang[space_no]</td>
	</tr><tr>
	<td>$alang[send_mail_email]</td>
	<td><input type="text" value="$mailcfg[from]" name="mail[from]"/><br/>$alang[email_format]</td>
	</tr><tr>
	<td>$alang[smtp_user]</td>
	<td><input type="text" value="$mailcfg[auth_username]" name="mail[auth_username]"/></td>
	</tr><tr>
	<td>$alang[smtp_password]</td>
	<td><input type="text" value="$mailcfg[auth_password]" name="mail[auth_password]"/></td>
	</tr>
	</tbody>
	</table>
	</td></tr>
	</tbody>
	</table>
	</div>
EOF;
	
	print<<<EOF
	<div class="buttons">
		<input type="submit" name="thevaluesubmit" value="$alang[common_submit]" class="submit" onclick="this.form.action+='&subtype=other';">
		<input type="reset" name="thevaluereset" value="$alang[common_reset]">
	</div>
EOF;
	
	echo label(array('type'=>'form-end'));
}

//邮件发送设置函数
function sendmailset() {
	
	$_POST['mail']['mailsend'] = intval($_POST['mail']['mailsend']) ? intval($_POST['mail']['mailsend']) : 1;
	$_POST['mail']['maildelimiter'] = intval($_POST['mail']['maildelimiter']);
	$_POST['mail']['mailusername'] = intval($_POST['mail']['mailusername']);
	
	$configcontent = sreadfile(S_ROOT.'./config.php');
	$configcontent = preg_replace("/[$]\_SC\[\'mailsend\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['mailsend']\\1= '".$_POST['mail']['mailsend']."'", $configcontent);
	$configcontent = preg_replace("/[$]mailcfg\[\'maildelimiter\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['maildelimiter']\\1= '".$_POST['mail']['maildelimiter']."'", $configcontent);
	$configcontent = preg_replace("/[$]mailcfg\[\'mailusername\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['mailusername']\\1= '".$_POST['mail']['mailusername']."'", $configcontent);
	if ($_POST['mail']['mailsend'] == 1) {
		//
	} else {
		$_POST['mail']['auth'] = intval($_POST['mail']['auth']);
		$_POST['mail']['server'] = trim($_POST['mail']['server']);
		$configcontent = preg_replace("/[$]mailcfg\[\'auth\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['auth']\\1= '".$_POST['mail']['auth']."'", $configcontent);
		$configcontent = preg_replace("/[$]mailcfg\[\'server\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['server']\\1= '".$_POST['mail']['server']."'", $configcontent);		
		
		if ($_POST['mail']['mailsend'] == 2) {
			
			$_POST['mail']['port'] = intval($_POST['mail']['port']) ? intval($_POST['mail']['port']) : 25;
			$_POST['mail']['from'] = trim($_POST['mail']['from']);
			$_POST['mail']['auth_username'] = trim($_POST['mail']['auth_username']);
			$_POST['mail']['auth_password'] = trim($_POST['mail']['auth_password']);
			$configcontent = preg_replace("/[$]mailcfg\[\'port\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['port']\\1= '".$_POST['mail']['port']."'", $configcontent);
			$configcontent = preg_replace("/[$]mailcfg\[\'from\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['from']\\1= '".$_POST['mail']['from']."'", $configcontent);
			$configcontent = preg_replace("/[$]mailcfg\[\'auth_username\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['auth_username']\\1= '".$_POST['mail']['auth_username']."'", $configcontent);
			$configcontent = preg_replace("/[$]mailcfg\[\'auth_password\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$mailcfg['auth_password']\\1= '".$_POST['mail']['auth_password']."'", $configcontent);	
		}
	}
	if($fp = fopen(S_ROOT.'./config.php', 'w')) {
		fwrite($fp, trim($configcontent));
		fclose($fp);
	}
}

?>