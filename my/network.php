<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network.php 12766 2009-07-20 04:26:21Z liguode $
*/

include_once('./common.php');

//�Ƿ�ر�վ��
checkclose();

//�ռ䱻����
if($_SGLOBAL['supe_uid']) {
	$space = getspace($_SGLOBAL['supe_uid']);
	
	if($space['flag'] == -1) {
		showmessage('space_has_been_locked');
	}
	
	//��ֹ����
	if(checkperm('banvisit')) {
		showmessage('you_do_not_have_permission_to_visit');
	}
}
	
include_once(S_ROOT.'./source/network.php');

?>