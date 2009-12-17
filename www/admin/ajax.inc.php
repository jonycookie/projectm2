<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
$action = $_GET['action'];
if(in_array($action,array('message','comment'))){
    $id =intval($_POST['id']);
	$reply='admin||'.htmlspecialchars($_POST['replytext']);
	$iCMS->db->update($action,compact('reply'),compact('id'))&&exit('1');
}elseif(in_array($action,array('source','author','editor'))){
	include_once(iPATH.'include/default.value.php');
	$ul="";
	if(${$action."s"})foreach(${$action."s"} as $key=>$val){
		$ul.="$val|$key\n";
	}
	echo $ul;
}elseif($action=="getsubcatalog"){
	include_once(iPATH.'include/catalog.class.php');
	$catalog =new catalog();
 	echo "{html:'".addslashes($catalog->row($_POST["cid"],$_POST["level"]+1))."',ids:'".$catalog->AJAXid($_POST["cid"])."'}";
}
?>