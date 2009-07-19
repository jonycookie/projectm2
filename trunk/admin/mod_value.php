<?php
!defined('IN_ADMIN') && die('Forbidden');

/**
 * 来管理内容模型输出输入的默认值选择，比如作者、来源等
 *
 */
class Value{
	function doIt(){
		global $action;
		!$action && $action='show';
		switch ($action){
			case 'show':
				$this->show();
				break;
			case 'add':
				$this->add();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'del':
				$this->del();
				break;
		}
	}

	function show(){
		global $db,$action,$basename;
		$rs = $db->query("SELECT * FROM cms_select");
		require PrintEot('mod_value');
		adminbottom();
	}

	function add(){
		global $db;
		$selectname = GetGP('selectname');
		!$selectname && Showmsg('mod_noselectname');
		$selectname = Char_cv($selectname);
		$db->update("INSERT INTO cms_select SET selectname='$selectname'");
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('select');
		adminmsg('operate_success');
	}

	function edit(){
		global $db,$basename,$action,$selectid;
		$selectid = (int)GetGP('selectid');
		!$selectid && Showmsg('data_error');
		if(!$_POST['step']){
			@extract($db->get_one("SELECT * FROM cms_select WHERE selectid='$selectid'"),EXTR_SKIP);
			$rs = $db->query("SELECT * FROM cms_selectvalue WHERE selectid='$selectid' ORDER BY usetime DESC");
			$value = array();
			while ($v = $db->fetch_array($rs)) {
				$v['usetime'] = get_date($v['usetime']);
				$value[$v['valueid']] = $v;
			}
			$max = 50; //预设值选择器最多可以容纳的值
			$len = $max - count($value);
			for ($i=0;$i<$len;$i++){
				$value['a_'.$i] = array();
			}
			require PrintEot('mod_value');
			adminbottom();
		}elseif ($_POST['step']==2){
			$value = GetGP('value');
			$selectname = GetGP('selectname');
			if(empty($selectname)) Showmsg('mod_noselectname');
			$selectname = Char_cv($selectname);
			foreach ($value as $key=>$val){
				$val = Char_cv($val);
				if(is_numeric($key)){
					if(empty($val)){
						$db->update("DELETE FROM cms_selectvalue WHERE valueid='$key'");
					}else{
						$db->update("UPDATE cms_selectvalue SET value='$val' WHERE valueid='$key'");
					}
				}else{
					if(empty($val)) continue;
					$db->update("INSERT INTO cms_selectvalue SET value='$val',selectid='$selectid',usetime='$GLOBALS[timestamp]'");
				}
			}
			$db->update("UPDATE cms_select SET selectname='$selectname' WHERE selectid='$selectid'");
			require_once(R_P.'require/class_cache.php');
			Cache::writeCache('select');
			adminmsg('operate_success');
		}
	}

	function del(){
		global $db,$basename;
		$selectid = (int)GetGP('selectid');
		!$selectid && Showmsg('data_error');
		$db->update("DELETE FROM cms_select WHERE selectid='$selectid'");
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('select');
		adminmsg('operate_success');
	}
}

$action = GetGP('action');
$value = new Value();
$value->doIt();
?>