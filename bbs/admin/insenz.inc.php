<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: insenz.inc.php 10611 2007-09-12 10:00:56Z liuqiang $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

define('INSENZ_CHECKIP', TRUE);
define('INSENZ_CHECKFILES', FALSE);
define('INSENZ_SAFEMODE', FALSE);

cpheader();

if(!$isfounder) {
	cpmsg('noaccess_isfounder');
}

require_once DISCUZ_ROOT.'./include/insenz.func.php';
@include_once DISCUZ_ROOT.'./discuz_version.php';
require_once DISCUZ_ROOT.'./admin/insenz.func.php';

echo '</td></tr></table><script type="text/javascript">var charset=\''.$charset.'\'</script><div style="padding: 0px 8px 0px 8px;" id="insenz_body">';

$discuz_chs = $insenz_chs = '';
$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='insenz'");
$insenz = ($insenz = $db->result($query, 0)) ? unserialize($insenz) : array();
$insenz['host'] = empty($insenz['host']) ? 'api.insenz.com' : $insenz['host'];
$insenz['url'] = empty($insenz['url']) ? 'api.insenz.com' : $insenz['url'];

if(empty($insenz['authkey']) && !($operation == 'settings' && $do == 'host')) {

	if(in_array($operation, array('binding', 'register'))) {

		checkip();

		if(empty($agreelicense)) {

			insenz_shownav('insenz_nav_license');
			echo '<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="tableborder">
					<tr class="header"><td>'.$lang['insenz_register_license'].'</td></tr><tr><td><div style="border-style: dotted; border-width: 1px; border-color: #86B9D6; padding: 6px 10px; float: none; overflow: auto; overflow-y: scroll; height:320px; word-break: break-all; background-color: #FFFFFF;" id="license">'.$lang['insenz_loading'].'</div></td></tr>
				</table>
				<br /><div id="licensesubmit" align="center"></div>
				<script type="text/javascript" src="http://'.$insenz['url'].'/misc/license.js" charset="utf-8"></script>
				<script type="text/javascript">
					if(typeof license != \'undefined\') {
						$("license").innerHTML = license;
						$("licensesubmit").innerHTML = \'<input onclick="window.location=\\\'admincp.php?action=insenz&operation='.$operation.'&agreelicense=yes\\\'" type="button" class="button" value="'.$lang['insenz_register_agree'].'"> &nbsp; <input onclick="javascript:history.go(-1);" type="button" class="button" value="'.$lang['insenz_register_disagree'].'">\';
					} else {
						$("license").innerHTML = \''.$lang['insenz_disconnect'].'\';
						$("licensesubmit").innerHTML = \'<input onclick="javascript:history.go(-1);" type="button" class="button" value="'.$lang['return'].'">\';
					}
				</script>';

		} else {

			if($operation == 'register') {

				$step = isset($step) ? intval($step) : (isset($insenz['step']) ? intval($insenz['step']) : 1);

				if($step == 1) {

					$items = array('username', 'password', 'name', 'idcard', 'email1', 'email2', 'qq', 'msn', 'tel1', 'tel2', 'tel3', 'mobile', 'fax1', 'fax2', 'fax3', 'country', 'province', 'city', 'address', 'postcode', 'alipay');

					if(!submitcheck('regsubmit')) {

						$response = insenz_request('<cmd id="checkSite"><s_url>'.urlencode($boardurl).'</s_url><s_key>'.md5($authkey.'Discuz!INSENZ').'</s_key></cmd>');
						if($response['status']) {
							cpmsg($response['data']);
						} else {
							$response = $response['data']['response'][0]['data'][0]['VALUE'];
						}
						if($response == 'site_exists') {
							cpmsg('insenz_forcebinding', 'admincp.php?action=insenz&operation=binding&agreelicense=yes&type=2');
						}

						foreach($items AS $item) {
							$$item = '';
						}
						if(isset($insenz['profile'])) {
							@extract($insenz['profile']);
							foreach($items AS $item) {
								$$item = stripslashes($$item);
							}
						}
						$country = intval($country) ? intval($country) : 0;
						$province = intval($province) ? intval($province) : 0;
						$city = intval($city) ? intval($city) : 0;
						$tel1 = intval($tel1) ? $tel1 : $lang['insenz_register_zone'];
						$tel2 = intval($tel2) ? intval($tel2) : $lang['insenz_register_exchange'];
						$tel3 = intval($tel3) ? intval($tel3) : $lang['insenz_register_extension'];

						insenz_shownav('insenz_register');
						echo '<script type="text/javascript" src="http://'.$insenz['url'].'/misc/city.js" charset="utf-8"></script><script type="text/javascript">
						function clearinput(obj, defaultvalue) {
							if(obj.value == defaultvalue) obj.value = "";
						}
						</script>
						<body onload="list('.$country.','.$province.','.$city.')">';
						echo '<form name="form1" id="insenz_regform" action="admincp.php?action=insenz&operation=register&agreelicense=yes&step=1" method="post" onSubmit="return validate(this);">';
						echo '<input type="hidden" name="formhash" value="'.FORMHASH.'">';
						showtype('insenz_register_step1', 'top');
						echo '<tr class="category"><td colspan="2">'.$lang['insenz_register_profile'].'</td></tr>';
						showsetting('insenz_register_username', 'username', $username, 'text');
						showsetting('insenz_register_password', 'password', $password, 'password');
						showsetting('insenz_register_password2', 'password2', '', 'password');
						showsetting('insenz_register_name', 'name', $name, 'text');
						showsetting('insenz_register_idcard', 'idcard', $idcard, 'text');
						echo '<tr class="category"><td colspan="2">'.$lang['insenz_register_contact'].'</td></tr>';
						showsetting('insenz_register_email1', 'email1', $email1 ? $email1 : $email, 'text');
						showsetting('insenz_register_email2', 'email2', $email2 ? $email2 : $adminemail, 'text');
						showsetting('insenz_register_qq', 'qq', $qq, 'text');
						showsetting('insenz_register_msn', 'msn', $msn, 'text');
						echo '<tr><td class="altbg1" width="45%"><b>'.$lang['insenz_register_tel'].'</b><br />'.$lang['insenz_register_tel_comment'].'</td><td class="altbg2"><input type="text" name="tel1" size="3" value="'.$tel1.'" onmousedown="clearinput(this, \''.$lang['insenz_register_zone'].'\')"> - <input type="text" name="tel2" size="8" value="'.$tel2.'" onmousedown="clearinput(this,\''.$lang['insenz_register_exchange'].'\')"> - <input type="text" name="tel3" size="5" value="'.$tel3.'" onmousedown="clearinput(this, \''.$lang['insenz_register_extension'].'\')"></td></tr>';
						showsetting('insenz_register_mobile', 'mobile', $mobile, 'text');
						echo '<tr><td class="altbg1" width="45%"><b>'.$lang['insenz_register_fax'].'</b><br />'.$lang['insenz_register_fax_comment'].'</td><td class="altbg2"><input type="text" name="fax1" size="3" value="'.$fax1.'"> - <input type="text" name="fax2" size="8" value="'.$fax2.'"> - <input type="text" name="fax3"size="5" value="'.$fax3.'"></td></tr>';
						echo '<tr><td class="altbg1" width="45%"><b>'.$lang['insenz_register_country'].'</b></td><td class="altbg2"><select name="country" onChange="changeseleccountry(this.value)"><option value="0">'.$lang['select'].'</option></select></td></tr>';
						echo '<tr><td class="altbg1" width="45%"><b>'.$lang['insenz_register_province'].'</b></td><td class="altbg2"><select name="province" onChange="changeseleccity(this.value)"><option value="0">'.$lang['select'].'</option></select> &nbsp;&nbsp; </td></tr>';
						echo '<tr><td class="altbg1" width="45%"><b>'.$lang['insenz_register_city'].'</b></td><td class="altbg2"><select name="city"><option value="0">'.$lang['select'].'</option></select> &nbsp;&nbsp; </td></tr>';
						showsetting('insenz_register_address', 'address', $address, 'text');
						showsetting('insenz_register_postcode', 'postcode', $postcode, 'text');
						echo '<tr class="category"><td colspan="2">'.$lang['insenz_register_account'].'</td></tr>';
						showsetting('insenz_register_alipay', 'alipay', $alipay, 'text');
						showtype('', 'bottom');
						echo '<br /><center><input type="submit" class="button" name="regsubmit" value="'.$lang['submit'].'" onclick="this.form.target=\'register\';"><iframe name="register" style="display: none"></iframe> &nbsp; <input type="button" class="button" value="'.$lang['cancel'].'" onclick="window.location=\'admincp.php?action=insenz\'"></center></form>
						<script type="text/javascript" src="./include/javascript/insenz_reg.js"></script>';

					} else {

						$username = checkusername($username);
						$password = checkpassword($password, $password2);
						$name = checkname($name);
						$idcard = checkidcard($idcard);
						$email1 = checkemail($email1, 'email1');
						$email2 = $email2 ? checkemail($email2, 'email2') : '';
						$qq = checkqq($qq);
						$msn = $msn ? checkemail($msn, 'msn') : '';
						$tel3 = $tel3 != $lang['insenz_register_extension'] ? intval($tel3) : '';
						$tel = checktel($tel1, $tel2, $tel3, 'tel');
						$fax = $fax2 ? checktel($fax1, $fax2, $fax3, 'fax') : '';
						$mobile = checkmobile($mobile);
						$cpc = checkcpc($country, $province, $city);
						$country = $cpc[0];
						$province = $cpc[1];
						$city = $cpc[2];
						$address = checkaddress($address);
						$postcode = checkpostcode($postcode);
						$alipay = checkemail($alipay, $lang['insenz_register_alipay']);

						$response = insenz_request('<cmd id="checkHandle"><handle>'.$username.'</handle></cmd>');
						if($response['status']) {
							insenz_alert($response['data']);
						} else {
							$response = $response['data']['response'][0]['data'][0]['VALUE'];
						}

						if($response == 'handle_exists') {
							insenz_alert('insenz_usernameexists', 'username');
						}

						foreach($items AS $item) {
							$insenz['profile'][$item] = $$item;
						}

						$insenz['step'] = 2;

						$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");

						insenz_cpmsg('insenz_regstep2', '&operation=register&agreelicense=yes&step=2');

					}

				} else {

					if(!submitcheck('regsubmit')) {

						echo '<form name="form1" action="admincp.php?action=insenz&operation=register&agreelicense=yes&step=3" method="post"><input type="hidden" name="formhash" value="'.FORMHASH.'">';
						insenz_shownav('insenz_register');
						showtype('insenz_register_step2', 'top');
						insenz_showsettings();
						showtype('', 'bottom');
						echo '<br /><center><input type="button" class="button" value="'.$lang['menu_back_to_last_step'].'" onclick="window.location=\'admincp.php?action=insenz&operation=register&agreelicense=yes&step=1\'"> &nbsp; <input type="submit" class="button" name="regsubmit" value="'.$lang['submit'].'" onclick="this.form.target=\'register\';"><iframe name="register" style="display: none"></iframe></center></form>';

					} else {

						$softadstatus = $softadstatus1 ? ($softadstatus2 ? 2 : 1) : 0;
						$softadstatus && checkmasks();
						$hardadstatus = is_array($hardadstatus) ? array_keys($hardadstatus) : array();
						$msgtoid = 0;
						if($softadstatus && is_array($notify) && $notify[1]) {
							if(empty($msgto)) {
								insenz_alert('insenz_msgtonone', 'msgto');
							} else {
								$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$msgto'");
								if(!$msgtoid = $db->result($query, 0)) {
									insenz_alert('insenz_msgtonone', 'msgto');
								}
							}

						}
						foreach(array('softadstatus', 'hardadstatus', 'relatedadstatus', 'notify', 'msgtoid', 'autoextend', 'virtualforumstatus') AS $item) {
							$insenz[$item] = $$item;
						}
						insenz_register('1');

					}
				}

			} elseif($operation == 'binding') {

				if(!submitcheck('bindingsubmit')) {

					echo '<form name="form1" action="admincp.php?action=insenz&operation=binding&agreelicense=yes" method="post"><input type="hidden" name="formhash" value="'.FORMHASH.'">';
					insenz_shownav('insenz_binding');
					showtype('insenz_binding_top', 'top');
					echo '<tr class="category"><td colspan="2"><b>'.$lang['insenz_binding_verify'].'</b></td></tr>';
					showsetting('insenz_binding_username', 'username', '', 'text');
					showsetting('insenz_binding_password', 'password', '', 'password');
					insenz_showsettings();
					showtype('', 'bottom');
					echo '<br /><center><input type="submit" class="button" name="bindingsubmit" value="'.$lang['insenz_binding_top'].'" onclick="this.form.target=\'binding\';"><iframe name="binding" style="display: none"></iframe> &nbsp; <input type="button" class="button" value="'.$lang['cancel'].'" onclick="window.location=\'admincp.php?action=insenz\'"></center></form>';

				} else {

					$insenz['profile']['username'] = htmlspecialchars($username);
					$insenz['profile']['password'] = htmlspecialchars($password);

					$softadstatus = $softadstatus1 ? ($softadstatus2 ? 2 : 1) : 0;
					$softadstatus && checkmasks();
					$hardadstatus = is_array($hardadstatus) ? array_keys($hardadstatus) : array();
					$msgtoid = 0;
					if($softadstatus && is_array($notify) && $notify[1]) {
						if(empty($msgto)) {
							insenz_alert('insenz_msgtonone', 'msgto');
						} else {
							$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$msgto'");
							if(!$msgtoid = $db->result($query, 0)) {
								insenz_alert('insenz_msgtonone', 'msgto');
							}
						}

					}
					foreach(array('softadstatus', 'hardadstatus', 'relatedadstatus', 'notify', 'msgtoid', 'autoextend', 'virtualforumstatus')AS $item) {
						$insenz[$item] = $$item;
					}

					insenz_register(!empty($type) && $type == 2 ? '2' : '3');

				}
			}
		}

	} else {

		insenz_shownav('insenz_nav_regorbind');
		//showtips('insenz_tips_register');
		echo '<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="tableborder">
			<tr class="header"><td colspan="2">'.$lang['insenz_nav_regorbind'].'</td></tr>
			<tr class="altbg1"><td>'.$lang['insenz_register_description'].'</td></tr>
			</table>
			<br /><center><input type="button" class="button" value="'.$lang['insenz_register'].'" onclick="window.location=\'admincp.php?action=insenz&operation=register\'"> &nbsp; <input type="button" class="button" value="'.$lang['insenz_binding'].'" onclick="window.location=\'admincp.php?action=insenz&operation=binding\'"></center>';

	}

} elseif(empty($operation) || $operation == 'campaignlist') {

	insenz_shownav('insenz_nav_softad');
	showtips('insenz_tips_softad');

	$baseurl = 'admincp.php?action=insenz&operation=campaignlist';
	$collapsed = 0;
	$c_status = isset($c_status) && in_array($c_status, array(0, 2, 6, 7)) ? $c_status : ($insenz['softadstatus'] == 2 ? 6 : 2);
	$page = isset($page) ? max(1, intval($page)) : 1;

	if($c_status == 6) {
		$onlineids = 0;
		$query = $db->query("SELECT id FROM {$tablepre}campaigns WHERE type<>'4' AND status='2'");
		while($c = $db->fetch_array($query)) {
			$onlineids .= ','.$c['id'];
		}
	}

	$campaignslist = array(0 => $lang['insenz_campaign_all'], 2 => $lang['insenz_campaign_new'], 6 => $lang['insenz_campaign_playing'], 7 => $lang['insenz_campaign_over']);

	echo 	'<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="tableborder">
		<tr class="header"><td colspan="9">'.$campaignslist[$c_status].'</td></tr><tr class="category"><td>'.$lang['insenz_campaign_id'].'</td><td>'.$lang['insenz_campaign_name'].'</td><td>'.$lang['insenz_campaign_class'].'</td><td>'.$lang['insenz_campaign_forum'].'</td><td>'.$lang['insenz_campaign_starttime'].'</td><td>'.$lang['insenz_campaign_endtime'].'</td><td>'.$lang['insenz_campaign_price'].'</td>'.(in_array($c_status, array(0, 6)) ? '<td>'.$lang['insenz_campaign_status'].'</td>' : '').'<td>'.$lang['insenz_detail'].'</td></tr>
		<tbody id="campaignlist"><tr id="campaignlist_loading"><td colspan="9"><img src="'.IMGDIR.'/loading.gif" border="0"> '.$lang['insenz_loading'].'</td></tr></tbody>
		</table><div id="multi"></div>';

?>

	<script src="http://<?=$insenz[url]?>/campaignlist.php?id=<?=$insenz[siteid]?>&t=<?=$timestamp?>&k=<?=md5($insenz[authkey].$insenz[siteid].$timestamp.'Discuz!')?>&insenz_version=<?=INSENZ_VERSION?>&discuz_version=<?=DISCUZ_VERSION.' - '.DISCUZ_RELEASE?>&c_status=<?=$c_status?>&page=<?=$page?>&random=<?=random(4)?>" type="text/javascript" charset="UTF-8"></script>

	<script type="text/javascript">
		if(typeof Campaigns != 'undefined' && error_msg != '') {
			alert(error_msg);
		}
		var c_status = parseInt(<?=$c_status?>);
		var c_statuss = {1:'<font color="red"><?=$lang['insenz_campaign_status_new']?></font>', 2:'<font color="red"><?=$lang['insenz_campaign_status_new']?></font>', 3:'<?=$lang['insenz_campaign_status_send']?>', 6:'<?=$lang['insenz_campaign_status_playing']?>', 7:'<?=$lang['insenz_campaign_status_end']?>'};
		var c_types = {1 : '<?=$lang['insenz_campaign_type_normal']?>', 2 : '<?=$lang['insenz_campaign_type_top']?>', 3 : '<?=$lang['insenz_campaign_type_float']?>'};
		if(c_status == 6) {
			var onlineids = [<?=$onlineids?>];
		}
		var s = '';
		if(typeof Campaigns == 'undefined') {
			s += '<tr><td class="altbg1" colspan="9"><?=$lang['insenz_disconnect']?></td></tr>';
		} else if(!Campaigns.length) {
			s += '<tr><td class="altbg1" colspan="9"><?=$lang['insenz_campaign_none']?></td></tr>';
		} else {
			for(var i in Campaigns) {
				s += '<tr>'
					+ '<td class="altbg1">' + Campaigns[i].c_id + '</td>'
					+ '<td class="altbg1"><a href="admincp.php?action=insenz&operation=campaigndetails&c_id=' + Campaigns[i].c_id + '&c_status=' + Campaigns[i].c_status + '">' + Campaigns[i].c_name + '</a></td>'
					+ '<td class="altbg2">' + c_types[Campaigns[i].c_type] + (Campaigns[i].c_auto ? '(<?=$lang['insenz_campaign_auto_push']?>)' : '') + '</td>'
					+ '<td class="altbg1"><a href="' + (Campaigns[i].b_type == 'group' ? 'index.php?gid=' : 'forumdisplay.php?fid=') + Campaigns[i].b_id + '" target="_blank">' + Campaigns[i].b_name + '</a></td>'
					+ '<td class="altbg2">' + Campaigns[i].c_begindate + '</td>'
					+ '<td class="altbg1">' + (Campaigns[i].c_type == 1 ? '----' : Campaigns[i].c_enddate) + '</td>'
					+ '<td class="altbg2">' + Campaigns[i].c_price + ' <?=$lang['rmb_yuan']?></td>'
					+ (c_status != 2 && c_status != 6 && c_status != 7 ? '<td class="altbg1">' + c_statuss[Campaigns[i].c_status] + '</td>' : (c_status == 6 ? '<td class="altbg1">' + (in_array(Campaigns[i].c_id, onlineids) ? '<?=$lang['insenz_campaign_status_online']?>' : '<?=$lang['insenz_campaign_status_waiting']?>') + '</td>' : ''))
					+ '<td class="altbg2"><a href="admincp.php?action=insenz&operation=campaigndetails&c_id=' + Campaigns[i].c_id + '&c_status=' + Campaigns[i].c_status + '"><?=$lang['insenz_campaign_detail']?></a></td></tr>';
			}
		}
		document.write('<table id="campaignlist_none" style="display: none">' + s + '</table>');
		var trs = $('campaignlist_none').getElementsByTagName('tr');
		var len = trs.length;
		for(var i = 0; i < len; i++) {
			$('campaignlist').appendChild(trs[0]);
		}
		$('campaignlist').removeChild($('campaignlist_loading'));
		$('campaignlist_none').parentNode.removeChild($('campaignlist_none'));
		if(typeof c_nums != 'undefined' && c_nums > 10) {
			$('multi').innerHTML = multi();
		}
		function multi() {
			var page = parseInt(<?=$page?>);
			var pages = Math.ceil(c_nums / 10);
			page = page < pages ? page : pages;
			var multi = '<div class="pages"><em>&nbsp;' + c_nums + '&nbsp;</em>';
			for(var i = 1; i <= pages; i++) {
				multi += page == i ? '<strong>' + page + '</strong>' : '<a href=<?=$baseurl?>&c_status=<?=$c_status?>&page=' + i + '>' + i + '</a>';
			}
			multi += '</div>';
			return multi;
		}
	</script>

<?php

} elseif($operation == 'campaigndetails') {

	insenz_shownav('insenz_nav_softad');

	$c_id = intval($c_id);
	$c_status = intval($c_status);
	$campaign = array();

	if($c_status == 3) {
		$query = $db->query("SELECT c.id, t.tid, t.displayorder FROM {$tablepre}campaigns c LEFT JOIN {$tablepre}threads t ON t.tid=c.tid WHERE c.id='$c_id' AND c.type<>4");
		if(!($campaign = $db->fetch_array($query)) || empty($campaign['tid'])) {
			echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">'.
			'<tr class="header"><td><div style="float:left; margin-left:0px; padding-top:8px">'.$lang['insenz_note'].'</div>'.
			'</td></tr><tr><td><font color="red">'.$lang['insenz_nomatchedcampdata'].'</font></td></tr></table><br />';
		}
	}

?>

<form method="post" action="admincp.php?action=insenz&operation=admin&c_id=<?=$c_id?>">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="tableborder">
	<tr class="header"><td colspan="2"><div style="float:left; margin-left:0px; padding-top:8px"><a href="###"onclick="collapse_change('tip')"><?=$lang['insenz_campaign_detail']?></a></div></td></tr>
	<tbody id="campaigndetails"><tr id="campaigndetails_loading"><td colspan="2"><img src="<?=IMGDIR?>/loading.gif" border="0"> <?=$lang['insenz_loading']?></td></tr></tbody>
</table><br />
<center><span id="campaigndetails_submit" style="display: none"><input class="button" type="submit" onclick="return confirmmessage('<?=$lang['insenz_push_message_tips']?>');" name="pushsubmit" value="<?=$lang['insenz_campaign_pass']?>"> &nbsp; <input class="button" type="submit" name="ignoresubmit" value="<?=$lang['insenz_campaign_ignore']?>"> &nbsp; </span><span id="campaigndetails_drop" style="display: none"><input class="button" type="submit" <?if($c_status == 3) {?>onclick="return confirmmessage('<?=$lang['insenz_delete_message_tips']?>');"<?}?> name="dropsubmit" value="<?=$lang['insenz_delete']?>"> &nbsp; </span><input class="button" type="button" onClick="history.go(-1)" value="<?=$lang['insenz_back']?>"></center>
</form>

<script src="http://<?=$insenz[url]?>/campaigndetails.php?id=<?=$insenz[siteid]?>&t=<?=$timestamp?>&k=<?=md5($insenz[authkey].$insenz[siteid].$timestamp.'Discuz!')?>&c_id=<?=$c_id?>&insenz_version=<?=INSENZ_VERSION?>&discuz_version=<?=DISCUZ_VERSION.' - '.DISCUZ_RELEASE?>&random=<?=random(4)?>" type="text/javascript" charset="UTF-8"></script>

<script src="./include/javascript/bbcode.js" type="text/javascript"></script>

<script type="text/javascript">

	if(typeof error_msg != 'undefined' && error_msg != '') {
		alert(error_msg);
	}

	var s = '';
	if(typeof Campaigndetails == 'undefined') {
		s += '<tr><td class="altbg1" colspan="8"><?=$lang['insenz_disconnect']?></td></tr>';
	} else if(Campaigndetails == '') {
		s += '<tr><td class="altbg1" colspan="8"><?=$lang['insenz_campaign_deleted']?></td></tr>';
	} else {

		var allowbbcode = 1;
		var forumallowhtml = 1;
		var allowsmilies = 0;
		var allowimgcode = 1;
		var c_statuss = {1:'<font color="red"><?=$lang['insenz_campaign_new']?></font>', 2:'<font color="red"><?=$lang['insenz_campaign_new']?></font>', 3:'<?=$lang['insenz_campaign_send']?>', 6:'<?=$lang['insenz_campaign_playing']?>', 7:'<?=$lang['insenz_campaign_over']?>'};

		var t_style = '';
		t_style += Campaigndetails.t_bold ? 'font-weight: bold;' : '';
		t_style += Campaigndetails.t_italic ? 'font-style: italic;' : '';
		t_style += Campaigndetails.t_underline ? 'text-decoration: underline;' : '';
		t_style += Campaigndetails.t_color ? 'color: ' + Campaigndetails.t_color : '';

		var t_title = parseInt('<?=$c_status?>') == 3 && parseInt('<?=$campaign['tid']?>') && parseInt('<?=$campaign['displayorder']?>') >= 0  ? '<a href="viewthread.php?tid=<?=$campaign['tid']?>" target="_blank"><span style="' + t_style + '">' + Campaigndetails.t_title + '</span></a>' : '<span style="' + t_style + '">' + Campaigndetails.t_title + '</span>';

		var t_type = '<?=$lang['insenz_campaign_normal']?>';
		if(Campaigndetails.c_type == 2) {
			t_type = Campaigndetails.b_type == 'group' ? '<?=$lang['insenz_campaign_forum_top']?>' : '<?=$lang['insenz_campaign_currentforum_top']?>';
		} else if(Campaigndetails.c_type == 3) {
			t_type = Campaigndetails.b_type == 'group' ? '<?=$lang['insenz_campaign_forum_float']?>' : '<?=$lang['insenz_campaign_currentforum_float']?>';
		}

		s += '<tr><td><?=$lang['insenz_campaign_name']?>£º' + Campaigndetails.c_name + '</td><td><?=$lang['insenz_campaign_push_forum']?>£º<a href="' + (Campaigndetails.b_type == 'group' ? 'index.php?gid=' : 'forumdisplay.php?fid=') + Campaigndetails.b_id + '" target="_blank">' + Campaigndetails.b_name + '</a></td></tr>'
		+ '<tr><td><?=$lang['insenz_campaign_starttime']?>£º' + Campaigndetails.c_begindate + '</td><td><?=$lang['insenz_campaign_endtime']?>£º' + (Campaigndetails.c_type == 1 ? '----' : Campaigndetails.c_enddate) + '</td></tr>'
		+ '<tr><td><?=$lang['insenz_campaign_price']?>£º<font color="red">' + Campaigndetails.c_price + '</font> <?=$lang['rmb_yuan']?></td><td><?=$lang['insenz_campaign_status']?>£º' + c_statuss[Campaigndetails.c_status] + '</td></tr>'
		+ '<tr><td colspan="2"><?=$lang['insenz_campaign_note']?>£º' + bbcode2html(Campaigndetails.c_notes) + '</td></tr>'
		+ '<tr><td><?=$lang['insenz_campaign_post_subject']?>£º' + t_title + ' (' + t_type + ')</td><td><?=$lang['insenz_campaign_post_username']?>£º' + (Campaigndetails.t_authortype == 1? '<?=$lang['insenz_campaign_post_admin']?>' : '<?=$lang['insenz_campaign_post_normal_user']?>') + '</td></tr>'
		+ '<tr><td colspan="2"><?=$lang['insenz_campaign_post_message']?>£º</td></tr>'
		+ '<tr><td colspan="2" class="altbg1"><div style="float: none; overflow: auto; overflow-y: scroll; height: 300px; word-break: break-all" id="t_content"></div>'
		+ '<input type="hidden" name="c_id" value="' + parseInt(Campaigndetails.c_id) + '">'
		+ '<input type="hidden" name="subject" value="' + htmlspecialchars(Campaigndetails.t_title)+ '">'
		+ '<input type="hidden" name="message" value="' + htmlspecialchars(Campaigndetails.t_content)+ '">'
		+ '<input type="hidden" name="authortype" value="' + parseInt(Campaigndetails.t_authortype) + '">'
		+ '<input type="hidden" name="b_id" value="' + parseInt(Campaigndetails.b_id) + '">'
		+ '<input type="hidden" name="begintime" value="' + parseInt(Campaigndetails.c_begintime) + '">'
		+ '<input type="hidden" name="endtime" value="' + parseInt(Campaigndetails.c_endtime) + '">'
		+ '<input type="hidden" name="c_type" value="' + parseInt(Campaigndetails.c_type) + '">'
		+ '<input type="hidden" name="highlight" value="' + Campaigndetails.t_highlight + '">'
		+ '</td></tr>'

	}

	document.write('<table id="campaigndetails_none" style="display: none">' + s + '</table>');
	var trs = $('campaigndetails_none').getElementsByTagName('tr');
	var len = trs.length;
	for(var i = 0; i < len; i++) {
		$('campaigndetails').appendChild(trs[0]);
	}
	$('t_content').innerHTML = bbcode2html(Campaigndetails.t_content);
	$('campaigndetails').removeChild($('campaigndetails_loading'));
	$('campaigndetails_none').parentNode.removeChild($('campaigndetails_none'));

	if(typeof Campaigndetails != 'undefined' && Campaigndetails != '') {
		if(Campaigndetails.c_status < 3) {
			$('campaigndetails_submit').style.display = '';
		} else if(Campaigndetails.c_status == 3 || Campaigndetails.c_status == 6) {
			var currenttime = parseInt('<?=$timestamp?>');
			if(currenttime - Campaigndetails.c_begintime < 172800) {
				$('campaigndetails_drop').style.display = '';
			}
		}
	}

	function confirmmessage(msg) {
		return confirm(msg);
	}

</script>

<?php

	if($c_status < 2) {
		$data = '<cmd id="markread">'.
			'<c_id>'.$c_id.'</c_id>'.
			'</cmd>';
		insenz_request($data, false);
	}

} elseif($operation == 'admin') {

	insenz_checkfiles();

	if(submitcheck('pushsubmit')) {

		$fid = intval($b_id);
		$query = $db->query("SELECT f.type, f.status, f.simple, ff.redirect FROM {$tablepre}forums f LEFT JOIN {$tablepre}forumfields ff ON ff.fid=f.fid WHERE f.fid='$fid'");
		if(!$forum = $db->fetch_array($query)) {
			cpmsg('insenz_invalidforum');
		} elseif($forum['type'] == 'group') {
			$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE fup='$fid' AND status>0 ORDER BY posts DESC LIMIT 1");
			if(!$fid = $db->result($query, 0)) {
				cpmsg('insenz_invalidgroup');
			} elseif(!$globalstick) {
				cpmsg('insenz_globalstickoff');
			}
		} elseif($forum['status'] == '0' || $forum['simple'] == '1' || !empty($forum['redirect'])) {
			cpmsg('insenz_invalidforums');
		}

		if(!$fp = @fsockopen($insenz['host'], 80)) {
			cpmsg('insenz_disconnect');
		}

		$c_id = intval($c_id);
		$c_type = intval($c_type);
		$subject = dhtmlspecialchars(trim($subject));
		$query = $db->query("SELECT id FROM {$tablepre}campaigns WHERE id='$c_id' AND type='$c_type'");
		if($db->result($query, 0)) {
			cpmsg('insenz_campaign_dumplicate');
		}

		$top = $c_type == 2 ? 1 : ($c_type == 3 ? 4 : 0);
		if($forum['type'] == 'group' && $top) {
			$top += 1;
		}
		$displayorder = -10 - $top;
		$highlight = intval($highlight);
		$masks = $authortype == 1 ? $insenz['admin_masks'] : $insenz['member_masks'];
		$authorid = array_rand($masks);
		$author = addslashes($masks[$authorid]);
		$dateline = intval($begintime);
		$endtime = intval($endtime);
		$expiration = $endtime + 60*86400;
		$lastpost = $dateline;
		$lastposter = $author;
		$moderated = in_array($displayorder, array(1, 2)) ? 1 : 0;

		$db->query("INSERT INTO {$tablepre}threads (fid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, highlight, moderated)
			VALUES ('$fid', '$author', '$authorid', '$subject', '$dateline', '$lastpost', '$lastposter', '$displayorder', '-1', '$highlight', '$moderated')");
		$tid = $db->insert_id();

		$data = '<cmd id="acceptCampaign">'.
			'<c_id>'.$c_id.'</c_id>'.
			'<topic_id>'.$tid.'</topic_id>'.
			'</cmd>';
		$response = insenz_request($data, TRUE, $fp);

		if($response['status']) {
			$db->query("DELETE FROM {$tablepre}threads WHERE tid='$tid'");
			cpmsg($response['data']);
		} else {
			$response = $response['data'];
			if($response['response'][0]['status'][0]['VALUE'] == 1) {
				$db->query("DELETE FROM {$tablepre}threads WHERE tid='$tid'");
				cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
			}
		}

		$db->query("INSERT INTO {$tablepre}posts (fid, tid, first, author, authorid, subject, dateline, message, useip,invisible, anonymous, usesig, htmlon, bbcodeoff, smileyoff, parseurloff)
			VALUES ('$fid', '$tid', '1', '$author', '$authorid', '$subject', '$dateline', '$message', '', '0', '0', '1', '1', '0', '0', '0')");

		$db->query("INSERT INTO {$tablepre}campaigns (id, type, fid, tid, status, begintime, endtime, expiration, nextrun)
			VALUES ('$c_id', '$c_type', '$fid', '$tid', '1', '$dateline', '$endtime', '$expiration', '$dateline')");

		insenz_cronnextrun($dateline);

		cpmsg('insenz_campaign_pushed', 'admincp.php?action=insenz');

	} elseif(submitcheck('ignoresubmit')) {

		if(!$confirmed) {

		echo '<br /><br /><br /><br />
				<form method="post" action="admincp.php?action=insenz&operation=admin&confirmed=yes">
				<input type="hidden" name="formhash" value="'.FORMHASH.'">
				<input type="hidden" name="c_id" value="'.$c_id.'">
				<table width="500" border="0" cellpadding="0" cellspacing="0" align="center" class="tableborder">
				<tr class="header"><td colspan="2">'.$lang['insenz_campaign_note'].'</td></tr>
				<tr><td class="altbg2" align="center" colspan="2">'.$lang['insenz_campaign_input_ignore_reson'].'£º</td></tr>
				<tr><td class="altbg2" style="text-align: right;">
				<input type="radio" class="radio" name="reason" value="1"></td><td>'.$lang['insenz_campaign_ignore_reson_more_threads'].'</td></tr>
				<tr><td class="altbg2" style="text-align: right;">
				<input type="radio" class="radio" name="reason" value="2"></td><td>'.$lang['insenz_campaign_reson_price'].'</td></tr>
				<tr><td class="altbg2" style="text-align: right;">
				<input type="radio" class="radio" name="reason" value="3"></td><td>'.$lang['insenz_campaign_reson_content_unsuitable'].'</td></tr>
				<tr><td class="altbg2" style="text-align: right;">
				<input type="radio" class="radio" name="reason" value="4"></td><td>'.$lang['insenz_campaign_reson_subject_notmathched'].'</td></tr>
				</table><div align="center">
				<input class="button" type="submit" name="ignoresubmit" onclick="return checkform(this.form);" value="'.$lang['insenz_campaign_confirmed'].'"> &nbsp;
				<input class="button" type="button" value="'.$lang['insenz_campaign_cancel'].'" onClick="history.go(-1)"><br /></div>
				<br /><br /></form><script type="text/javascript">
				function checkform(theform) {
					for(var i = 0; i < 4; i++) {
						if(theform.reason[i].checked) return true;
					}
					alert(\''.$lang['insenz_campaign_input_reason'].'\');
					return false;
				}
				</script>';
			cpfooter();
			dexit();

		} else {

			if(!$reason = intval($reason)) {
				cpmsg('insenz_campaign_input_reason');
			}
			$data = '<cmd id="ignoreCampaign">'.
				'<c_id>'.$c_id.'</c_id>'.
				'<reason>'.$reason.'</reason>'.
				'</cmd>';
			$response = insenz_request($data);
			if($response['status']) {
				cpmsg($response['data']);
			} else {
				$response = $response['data'];
				if($response['response'][0]['status'][0]['VALUE'] == 1) {
					cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
				}
			}
			cpmsg('insenz_campaign_specified_ignored', 'admincp.php?action=insenz');

		}

	} elseif(submitcheck('dropsubmit')) {

		if(!$confirmed) {

		echo '<br /><br /><br /><br />
				<form method="post" action="admincp.php?action=insenz&operation=admin&confirmed=yes">
				<input type="hidden" name="formhash" value="'.FORMHASH.'">
				<input type="hidden" name="c_id" value="'.$c_id.'">
				<input type="hidden" name="c_type" value="'.$c_type.'">
				<table width="500" border="0" cellpadding="0" cellspacing="0" align="center" class="tableborder">
				<tr class="header"><td colspan="2">'.$lang['insenz_campaign_note'].'</td></tr>
				<tr><td class="altbg2" align="center" colspan="2">'.$lang['insenz_campaign_input_delete_reason'].'£º</td></tr>
				<tr><td class="altbg2" style="text-align: center;">
				<textarea name="reason" rows="6" cols="50"></textarea></tr>
				</table><div align="center">
				<input class="button" type="submit" name="dropsubmit" onclick="return checkform(this.form);" value="'.$lang['insenz_campaign_confirmed'].'"> &nbsp;
				<input class="button" type="button" value="'.$lang['insenz_campaign_cancel'].'" onClick="history.go(-1)"><br /></div>
				<br /><br /></form><script type="text/javascript">
				function checkform(theform) {
					if(trim(theform.reason.value) == \'\') {
						alert(\''.$lang['insenz_campaign_input_reason'].'\');
						return false;
					} else if(theform.reason.value.length > 255) {
						alert(\''.$lang['insenz_campaign_reson_words_too_many'].'\');
						return false;
					}
				}
				</script>';
			cpfooter();
			dexit();

		} else {

			if(!$reason = trim($reason)) {
				cpmsg('insenz_campaign_input_reason');
			} elseif(strlen($reason) > 255) {
				cpmsg('insenz_campaign_reson_words_too_many');
			}

			$query = $db->query("SELECT tid, begintime FROM {$tablepre}campaigns WHERE id='$c_id' AND type='$c_type'");
			if(!$c = $db->fetch_array($query)) {
				cpmsg('insenz_campaign_deleted');
			} elseif($timestamp - $c['begintime'] > 172800) {
				cpmsg('insenz_campaign_cant_delete_after_2_days');
			}

			$data = '<cmd id="dropCampaign">'.
				'<c_id>'.$c_id.'</c_id>'.
				'<reason>'.insenz_convert($reason).'</reason>'.
				'</cmd>';
			$response = insenz_request($data);
			if($response['status']) {
				cpmsg($response['data']);
			} else {
				$response = $response['data'];
				if($response['response'][0]['status'][0]['VALUE'] == 1) {
					cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
				}
			}
			$db->query("DELETE FROM {$tablepre}threads WHERE tid='$c[tid]'");
			$db->query("DELETE FROM {$tablepre}posts WHERE tid='$c[tid]'");
			$db->query("DELETE FROM {$tablepre}campaigns WHERE id='$c_id' AND type='$c_type'");
			cpmsg('insenz_campaign_specified_deleted', 'admincp.php?action=insenz');

		}

	}

} elseif($operation == 'settings') {

	insenz_checkfiles();

	$baseurl = 'admincp.php?action=insenz&operation=settings';
	if(!submitcheck('settingssubmit')) {
		insenz_shownav('insenz_nav_settings');
	}

	if($do == 'basic') {

		if(!submitcheck('settingssubmit')) {

			echo '<form name="form" action="'.$baseurl.'&do=basic" method="post">
				<input type="hidden" name="formhash" value="'.FORMHASH.'">';
			insenz_showsettings($do);
			echo '</table><br /><center><input type="submit" class="button" name="settingssubmit" value="'.$lang['insenz_campaign_submit'].'"></center></form>';

		} else {

			/*if($host && $insenz['host'] != $host && (preg_match("/\w{1,8}\.insenz\.com/i", $host) || strcmp(long2ip(sprintf('%u', ip2long($host))), $host) == 0)) {
				$insenz['host'] = $host;
			}*/

			$msgtoid = 0;
			if(is_array($notify) && $notify[1]) {
				if(empty($msgto)) {
					cpmsg('insenz_campaign_message_user_not_exists');
				} else {
					$query = $db->query("SELECT uid FROM {$tablepre}members WHERE username='$msgto'");
					if(!$msgtoid = $db->result($query, 0)) {
						cpmsg('insenz_campaign_message_user_not_exists');
					}
				}
			}

			$notify = is_array($notify) ? $notify : array(2 => 1);
			if($insenz['notify'] != $notify) {
				$data = '<cmd id="editbasicsettings">'.
					'<notify>'.implode(',', $notify).'</notify>'.
					'<s_key>'.md5($authkey.'Discuz!INSENZ').'</s_key>'.
					'</cmd>';
				$response = insenz_request($data);
				if($response['status']) {
					cpmsg($response['data']);
				} else {
					$response = $response['data'];
					if($response['response'][0]['status'][0]['VALUE'] == 1) {
						cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
					}
				}
				insenz_updatesettings();
			}
			foreach(array('notify', 'msgtoid') AS $item) {
				$insenz[$item] = $$item;
			}
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
			require_once DISCUZ_ROOT.'./include/cache.func.php';
			updatecache('settings');
			cpmsg('insenz_settings_updated_succeed', $baseurl.'&do='.$do);
		}

	} elseif($do == 'softad') {

		if(!submitcheck('settingssubmit')) {

			showtips('insenz_tips_softadsetting');
			echo '<form name="form" action="'.$baseurl.'&do=softad" method="post">
				<input type="hidden" name="formhash" value="'.FORMHASH.'">';
			insenz_showsettings($do);
			echo '</table><br /><center><input type="submit" class="button" name="settingssubmit" value="'.$lang['insenz_campaign_submit'].'"></center></form>';

		} else {

			$softadstatus = $softadstatus1 ? ($softadstatus2 ? 2 : 1) : 0;
			if($softadstatus && (empty($insenz['lastmodified']) || $timestamp - $insenz['lastmodified'] > 14 * 86400)) {
				if(checkmasks(TRUE)) {
					$insenz['lastmodified'] = $timestamp;
				}
			}

			if($insenz['softadstatus'] != $softadstatus) {
				$data = '<cmd id="editsoftadstatus">'.
					'<softadstatus>'.$softadstatus.'</softadstatus>'.
					'<autoextend>'.intval($autoextend).'</autoextend>'.
					'<s_key>'.md5($authkey.'Discuz!INSENZ').'</s_key>'.
					'</cmd>';
				$response = insenz_request($data);
				if($response['status']) {
					cpmsg($response['data']);
				} else {
					$response = $response['data'];
					if($response['response'][0]['status'][0]['VALUE'] == 1) {
						cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
					}
				}
				insenz_updatesettings();
			}
			foreach(array('softadstatus', 'autoextend') AS $item) {
				$insenz[$item] = $$item;
			}
			$insenz['uid'] = $discuz_uid;
			$insenz['username'] = $discuz_userss;
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
			require_once DISCUZ_ROOT.'./include/cache.func.php';
			updatecache('settings');
			cpmsg('insenz_settings_updated_succeed', $baseurl.'&do='.$do);
		}

	} elseif($do == 'hardad') {

		if(!submitcheck('settingssubmit')) {

			showtips('insenz_tips_hardadsetting');
			echo '<form name="form" action="'.$baseurl.'&do=hardad" method="post">
				<input type="hidden" name="formhash" value="'.FORMHASH.'">';
			insenz_showsettings($do);
			echo '</table><br /><center><input type="submit" class="button" name="settingssubmit" value="'.$lang['insenz_campaign_submit'].'"></center></form>';

		} else {

			$hardadstatus = is_array($hardadstatus) ? array_keys($hardadstatus) : array();
			if($insenz['hardadstatus'] != $hardadstatus) {
				$insenz['hardadstatus'] = $hardadstatus;
				$data = '<cmd id="edithardadstatus">'.
					'<hardadstatus>'.implode(',', (array)$hardadstatus).'</hardadstatus>'.
					'<s_key>'.md5($authkey.'Discuz!INSENZ').'</s_key>'.
					'</cmd>';
				$response = insenz_request($data);
				if($response['status']) {
					cpmsg($response['data']);
				} else {
					$response = $response['data'];
					if($response['response'][0]['status'][0]['VALUE'] == 1) {
						cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
					}
				}
				insenz_updatesettings();
				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
				require_once DISCUZ_ROOT.'./include/cache.func.php';
				updatecache('settings');

			}
			cpmsg('insenz_settings_updated_succeed', $baseurl.'&do='.$do);
		}

	} elseif($do == 'relatedad') {

		if(!submitcheck('settingssubmit')) {

			showtips('insenz_tips_relatedadsetting');
			echo '<form name="form" action="'.$baseurl.'&do=relatedad" method="post">
				<input type="hidden" name="formhash" value="'.FORMHASH.'">';
			insenz_showsettings($do);
			echo '</table><br /><center><input type="submit" class="button" name="settingssubmit" value="'.$lang['insenz_campaign_submit'].'"></center></form>';

		} else {
			$relatedadstatus = in_array($relatedadstatus, array(0, 1)) ? $relatedadstatus : 1;
			if($insenz['relatedadstatus'] != $relatedadstatus) {
				$insenz['relatedadstatus'] = $relatedadstatus;
				$data ='<cmd id="editrelatedadstatus">'.
					'<relatedadstatus>'.$relatedadstatus.'</relatedadstatus>'.
					'<s_key>'.md5($authkey.'Discuz!INSENZ').'</s_key>'.
					'</cmd>';
				$response = insenz_request($data);
				if($response['status']) {
					cpmsg($response['data']);
				} else {
					$response = $response['data'];
					if($response['response'][0]['status'][0]['VALUE'] == 1) {
						cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
					}
				}
				insenz_updatesettings();
				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
				require_once DISCUZ_ROOT.'./include/cache.func.php';
				updatecache('settings');
			}
			cpmsg('insenz_settings_updated_succeed', $baseurl.'&do='.$do);
		}

	} elseif($do == 'virtualforum') {

		if(!submitcheck('settingssubmit')) {

			showtips('insenz_tips_virtualforumsetting');
			echo '<form name="form" action="'.$baseurl.'&do=virtualforum" method="post">
				<input type="hidden" name="formhash" value="'.FORMHASH.'">';
			insenz_showsettings($do);
			echo '</table><br /><center><input type="submit" class="button" name="settingssubmit" value="'.$lang['insenz_campaign_submit'].'"></center></form>';

		} else {
			$virtualforumstatus = in_array($virtualforumstatus, array(0, 1)) ? $virtualforumstatus : 1;
			if($insenz['virtualforumstatus'] != $virtualforumstatus) {
				$insenz['virtualforumstatus'] = $virtualforumstatus;
				$data ='<cmd id="editvirtualforumstatus">'.
					'<virtualforumstatus>'.$virtualforumstatus.'</virtualforumstatus>'.
					'<s_key>'.md5($authkey.'Discuz!INSENZ').'</s_key>'.
					'</cmd>';
				$response = insenz_request($data);
				if($response['status']) {
					cpmsg($response['data']);
				} else {
					$response = $response['data'];
					if($response['response'][0]['status'][0]['VALUE'] == 1) {
						cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
					}
				}
				insenz_updatesettings();
				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
				require_once DISCUZ_ROOT.'./include/cache.func.php';
				updatecache('settings');
			}
			cpmsg('insenz_settings_updated_succeed', $baseurl.'&do='.$do);
		}
	} elseif($do == 'host') {
		if(!submitcheck('settingssubmit')) {
			if(!function_exists('fsockopen')) {
				cpmsg('insenz_fsockopen_notavailable');
			}
			echo '<form name="form" action="'.$baseurl.'&do=host" method="post">
				<input type="hidden" name="formhash" value="'.FORMHASH.'">
				<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="tableborder">';
			showtype('insenz_settings_host', 'top');
			showsetting('insenz_settings_domain', 'host', $insenz['host'], 'text');
			echo '</table><br /><center><input type="submit" class="button" name="settingssubmit" value="'.$lang['submit'].'"></center></form>';
		} else {
			if($host && $insenz['host'] != $host && (preg_match("/\w{1,8}\.insenz\.com/i", $host) || strcmp(long2ip(sprintf('%u', ip2long($host))), $host) == 0)) {
				$insenz['host'] = $host;
				$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
			}
			cpmsg('insenz_settings_updated_succeed', $baseurl.'&do='.$do);
		}
	}

} elseif($operation == 'virtualforum') {

	insenz_shownav('insenz_nav_virtualforum');

	if(submitcheck('acceptsubmit')) {

		insenz_checkfiles();

		$c_id = intval($c_id);
		$subject = dhtmlspecialchars(trim($c_name));
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}campaigns WHERE id='$c_id' AND type='4'");
		if($db->result($query, 0)) {
			cpmsg('insenz_campaign_dumplicate');
		}

		if($insenz['gid']) {
			$query = $db->query("SELECT COUNT(*) FROM {$tablepre}forums WHERE fid='$insenz[gid]' AND type='group'");
			if(!$db->result($query, 0)) {
				unset($insenz['gid']);
			} else {
				$query = $db->query("UPDATE {$tablepre}forums SET displayorder='-127' WHERE fid='$insenz[gid]'");
			}
		}

		if(empty($insenz['gid'])) {
			$db->query("INSERT INTO {$tablepre}forums(fup, type, name, status, displayorder) VALUES ('0', 'group', '".$lang['insenz_virtualforum_init_forumname']."', '1', '-127')");
			$insenz['gid'] = $db->insert_id();
			$db->query("INSERT INTO {$tablepre}forumfields(fid, description, redirect) values ('$insenz[gid]','', '')");
			$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
		}

		$orderby = array('threads', 'posts', 'todayposts', 'fid');
		$query = $db->query("SELECT threads, posts, lastpost FROM {$tablepre}forums WHERE type='forum' ORDER BY ".$orderby[rand(0, 3)]." DESC LIMIT 1");
		$forum = $db->fetch_array($query);
		
		$db->query("INSERT INTO {$tablepre}forums(fup, type, name, status, displayorder, threads, posts, lastpost) VALUES ('$insenz[gid]', 'forum', '$c_forumname', '0', '-1', '$forum[threads]', '$forum[posts]', '".addslashes($forum['lastpost'])."')");
		$fid = $db->insert_id();
		$c_forumlink = urlencode($c_forumlink);
		$db->query("INSERT INTO {$tablepre}forumfields(fid, description, redirect, icon) values ('$fid','$c_forumnote', 'misc.php?action=virtualforum&fid=$fid&forumlink=$c_forumlink', '$icon')");

		$data = '<cmd id="acceptVirtualForum">'.
			'<c_id>'.$c_id.'</c_id>'.
			'<boardid>'.$fid.'</boardid>'.
			'</cmd>';
		$response = insenz_request($data, TRUE, $fp);

		if($response['status']) {
			$db->query("DELETE FROM {$tablepre}forums WHERE fid='$fid'");
			$db->query("DELETE FROM {$tablepre}forumfields WHERE fid='$fid'");
			cpmsg($response['data']);
		} else {
			$response = $response['data'];
			if($response['response'][0]['status'][0]['VALUE'] == 1) {
				$db->query("DELETE FROM {$tablepre}forums WHERE fid='$fid'");
				$db->query("DELETE FROM {$tablepre}forumfields WHERE fid='$fid'");
				cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
			}
		}

		$query = $db->query("REPLACE INTO {$tablepre}campaigns(id, fid, tid, type, status, begintime, starttime, endtime, expiration, nextrun)
			VALUES ('$c_id', '$fid', '0', '4', '1', '$c_begintime', '$c_starttime', '$c_endtime', '0', '$c_begintime')");

		$insenz['virtualforums'][$fid]['link'] = strpos($c_forumlink, '?') !== FALSE ? $c_forumlink.'&' : $c_forumlink.'?';
		$insenz['virtualforums'][$fid]['height'] = $c_forumheight;

		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");

		insenz_cronnextrun($c_begintime);

		cpmsg('insenz_virtualforum_send', 'admincp.php?action=insenz&amp;operation=virtualforum&amp;do='.$do);

	} elseif(submitcheck('ignoresubmit')) {

		insenz_checkfiles();

		$c_id = intval($c_id);
		$subject = dhtmlspecialchars(trim($c_name));
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}campaigns WHERE id='$c_id' AND type='4'");
		if($db->result($query, 0)) {
			cpmsg('insenz_campaign_dumplicate');
		}

		if(!$confirmed) {
			echo '<br /><br /><br /><br />
				<form method="post" action="admincp.php?action=insenz&operation=virtualforum&confirmed=yes">
				<input type="hidden" name="formhash" value="'.FORMHASH.'">
				<input type="hidden" name="c_id" value="'.$c_id.'">
				<table width="500" border="0" cellpadding="0" cellspacing="0" align="center" class="tableborder">
				<tr class="header"><td colspan="2">'.$lang['insenz_campaign_note'].'</td></tr>
				<tr><td class="altbg2" align="center" colspan="2">'.$lang['insenz_campaign_input_ignore_reson'].'</td></tr>
				<tr><td class="altbg2" style="text-align: right;">
				<input type="radio" class="radio" name="reason" value="1"></td><td>'.$lang['insenz_virtualforum_ignore_reson_more_threads'].'</td></tr>
				<tr><td class="altbg2" style="text-align: right;">
				<input type="radio" class="radio" name="reason" value="2"></td><td>'.$lang['insenz_virtualforum_reson_price'].'</td></tr>
				<tr><td class="altbg2" style="text-align: right;">
				<input type="radio" class="radio" name="reason" value="3"></td><td>'.$lang['insenz_virtualforum_reson_content_unsuitable'].'</td></tr>
				<tr><td class="altbg2" style="text-align: right;">
				<input type="radio" class="radio" name="reason" value="4"></td><td>'.$lang['insenz_virtualforum_reson_subject_notmathched'].'</td></tr>
				</table><div align="center">
				<input class="button" type="submit" name="ignoresubmit" onclick="return checkform(this.form);" value="'.$lang['insenz_campaign_confirmed'].'"> &nbsp;
				<input class="button" type="button" value="'.$lang['insenz_campaign_cancel'].'" onClick="history.go(-1)"><br /></div>
				<br /><br /></form><script type="text/javascript">
				function checkform(theform) {
					for(var i = 0; i < 4; i++) {
						if(theform.reason[i].checked) return true;
					}
					alert(\''.$lang['insenz_campaign_input_reason'].'\');
					return false;
				}
				</script>';
			cpfooter();
			dexit();

		} else {

			if(!$reason = intval($reason)) {
				cpmsg('insenz_campaign_input_reason');
			}
			$data = '<cmd id="ignoreVirtualForum">'.
				'<c_id>'.$c_id.'</c_id>'.
				'<reason>'.$reason.'</reason>'.
				'</cmd>';
			$response = insenz_request($data);
			if($response['status']) {
				cpmsg($response['data']);
			} else {
				$response = $response['data'];
				if($response['response'][0]['status'][0]['VALUE'] == 1) {
					cpmsg(insenz_convert($response['response'][0]['reason'][0]['VALUE'], 0));
				}
			}
			cpmsg('insenz_campaign_specified_ignored', 'admincp.php?action=insenz');

		}

	} else {
		if($c_status == 6) {
			$onlineids = 0;
			$query = $db->query("SELECT id FROM {$tablepre}campaigns WHERE type='4' AND status='2'");
			while($c = $db->fetch_array($query)) {
				$onlineids .= ','.$c['id'];
			}
		}
		$statuslist = array('2'=>$lang['insenz_campaign_new'], '6'=>$lang['insenz_campaign_playing'], '7'=>$lang['insenz_campaign_over']);
		echo '	<script src="./include/javascript/bbcode.js" type="text/javascript"></script>
			<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" class="tableborder">
			<tr class="header"><td colspan="8">'.$statuslist[$c_status].'</td></tr>
			<tr class="category" align="center"><td>'.$lang['insenz_campaign_id'].'</td><td>'.$lang['insenz_campaign_name'].'</td>
			<td>'.$lang['insenz_campaign_price'].'</td>
			<td>'.$lang['insenz_campaign_starttime'].'</td>
			<td>'.$lang['insenz_campaign_endtime'].'</td>
			<td>'.$lang['insenz_virtualforum_name'].'</td>
			'.($c_status == 6 ? '<td>'.$lang['insenz_campaign_status'].'</td>' : '').'
			<td>'.$lang['insenz_detail'].'</td></tr>
			<tbody id="tbody1"><tr>
			<td colspan="8" id="loading"><img src="'.IMGDIR.'/loading.gif" border="0"> '.$lang['insenz_loading'].'</td></tr></tbody>
			</table>
			<script src="http://'.$insenz['url'].'/virtualforum.php?action=list&c_status='.$c_status.'&id='.$insenz['siteid'].'&t='.$timestamp.'&k='.md5($insenz['authkey'].$insenz['siteid'].$timestamp.'Discuz!').'&insenz_version='.INSENZ_VERSION.'&discuz_version='.DISCUZ_VERSION.'-'.DISCUZ_RELEASE.'&c_status='.$c_status.'&page='.$page.'&random='.random(4).'" type="text/javascript" charset="UTF-8"></script>
			<script type="text/javascript">
			if(typeof error_msg != "undefined" && error_msg) {
				$("loading").innerHTML = error_msg;
				alert(error_msg);
			} else {
				var s = "";
				for(k in Camps) {
					var camp = Camps[k];
					s += "<tr align=\"center\"><td class=\"altbg1\" align=\"left\">"+camp.c_id+"</td>";
					s += "<td class=\"altbg2\" align=\"left\">"+camp.c_name+"</td>";
					s += "<td class=\"altbg1\">"+camp.c_price+"</td>";
					s += "<td class=\"altbg2\">"+camp.c_begindate+"</td>";
					s += "<td class=\"altbg1\">"+camp.c_enddate+"</td>";
					s += "<td class=\"altbg2\">"+camp.c_forumname+"</td>";
					'.($c_status == 6 ? 's += "<td class=\"altbg2\">"+(in_array(camp.c_id, ['.$onlineids.']) ? "'.$lang[insenz_campaign_status_online].'" : "'.$lang[insenz_campaign_status_waiting].'")+"</td>";' : '').'
					s += "<td class=\"altbg1\"><a href=\"javascript:showdetail("+camp.c_id+")\">'.$lang['insenz_detail'].'</a></td></tr>";
					s += "<tr><td colspan=\"8\" id=\"detail_"+camp.c_id+"\" style=\"display: none;\"></td></tr>";
				}
				$("loading").style.display = "none";
				ajaxinnerhtml($("tbody1"), s);
			}
			function showdetail(id) {
				var camp = Camps[id];
				var obj = $("detail_" + id);
				obj.style.display = obj.style.display == "" ? "none" : "";
				obj.style.padding = "10px";

				s = "<b>'.$lang['insenz_virtualforum_note'].'£º</b><br>" + camp.c_forumnote;
				s += "<br><b>'.$lang['insenz_virtualforum_camp_note'].'£º</b><br>" + camp.c_note;
				s += "<form name=\"form\" action=\"admincp.php?action=insenz&operation=virtualforum\" method=\"post\">";
				s += "<input type=\"hidden\" name=\"formhash\" value=\"'.FORMHASH.'\">";
				s += "<input type=\"hidden\" name=\"c_id\" value=\""+parseInt(camp.c_id)+"\">";
				s += "<input type=\"hidden\" name=\"c_name\" value=\""+htmlspecialchars(camp.c_name)+"\">";
				s += "<input type=\"hidden\" name=\"c_note\" value=\""+htmlspecialchars(camp.c_note)+"\">";
				s += "<input type=\"hidden\" name=\"c_price\" value=\""+parseInt(camp.c_price)+"\">";
				s += "<input type=\"hidden\" name=\"c_begintime\" value=\""+parseInt(camp.c_begintime)+"\">";
				s += "<input type=\"hidden\" name=\"c_endtime\" value=\""+parseInt(camp.c_endtime)+"\">";
				s += "<input type=\"hidden\" name=\"c_forumname\" value=\""+htmlspecialchars(camp.c_forumname)+"\">";
				s += "<input type=\"hidden\" name=\"c_forumlink\" value=\""+htmlspecialchars(camp.c_forumlink)+"\">";
				s += "<input type=\"hidden\" name=\"c_forumnote\" value=\""+htmlspecialchars(camp.c_forumnote)+"\">";
				s += "<input type=\"hidden\" name=\"c_forumheight\" value=\""+parseInt(camp.c_forumheight)+"\">";
				s += "<input type=\"hidden\" name=\"icon\" value=\""+htmlspecialchars(camp.c_icon)+"\">";
				s += '.($c_status == 2 ? "'<center><input type=\"submit\" class=\"button\" name=\"acceptsubmit\" value=\"".$lang['insenz_campaign_pass']."\"> &nbsp; <input type=\"submit\" class=\"button\" name=\"ignoresubmit\" value=\"".$lang['insenz_campaign_ignore']."\"></center>'" : "''").';
				obj.innerHTML = s;

			}
			</script>';

	}

} else {

	cpmsg('noaccess');

}

echo '</div><table><tr><td>';

?>
