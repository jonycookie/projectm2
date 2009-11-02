<?php

/*
	[UCenter] (C)2001-2008 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: note.php 12189 2008-01-17 07:29:56Z heyond $
*/

!defined('IN_UC') && exit('Access Denied');

define('UC_NOTE_REPEAT', 5);	//note 通知重复次数
define('UC_NOTE_TIMEOUT', 15);	//note 通知超时时间(秒)
define('UC_NOTE_GC', 10000);	//note 过期通知的回收概率，该值越大，概率越低

class notemodel {

	var $db;
	var $base;
	var $apps;
	var $operations = array();

	function notemodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
		$_CACHE = $this->base->cache('apps');
		$this->apps = $_CACHE['apps'];
		/** note
		 * 1. 操作的名称，如：删除用户，测试连通，删除好友，取TAG数据，更新客户端缓存
		 * 2. 调用的应用的接口参数，拼接规则为 APP_URL/api/uc.php?action=test&ids=1,2,3
		 * 3. 回调的模块名称
		 * 4. 回调的模块方法（$appid, $content）
		 */
		$this->operations = array(
			'test'=>array('', 'action=test'),
			'deleteuser'=>array('', 'action=deleteuser'),
			'renameuser'=>array('', 'action=renameuser'),
			'deletefriend'=>array('', 'action=deletefriend'),
			'gettag'=>array('', 'action=gettag', 'tag', 'updatedata'),
			'getcreditsettings'=>array('', 'action=getcreditsettings'),
			'updatecreditsettings'=>array('', 'action=updatecreditsettings'),
			'updateclient'=>array('', 'action=updateclient'),
			'updatepw'=>array('', 'action=updatepw'),
			'updatebadwords'=>array('', 'action=updatebadwords'),
			'updatehosts'=>array('', 'action=updatehosts'),
			'updateapps'=>array('', 'action=updateapps'),
			'updatecredit'=>array('', 'action=updatecredit'),
		);
	}

	/**
	 * 添加通知列表
	 *
	 * @param string 操作
	 * @param string getdata
	 * @param string postdata
	 * @param array appids 指定通知的 APPID
	 * @param int pri 优先级，值越大表示越高
	 * @return int 插入的ID
	 */
	function add($operation, $getdata='', $postdata='', $appids=array(), $pri = 0) {
		$extra = '';
		if($appids) {
			$appadd = array();
			foreach((array)$this->apps as $appid=>$app) {
				$appid = $app['appid'];
				if(!in_array($appid, $appids)) {
					$appadd[] = 'app'.$appid."='1'";
				}
			}
			if($appadd) {
				$extra = ','.implode(',', $appadd);
			}
		}
		$getdata = addslashes($getdata);
		$postdata = addslashes($postdata);
		$this->db->query("INSERT INTO ".UC_DBTABLEPRE."notelist SET getdata='$getdata', operation='$operation', pri='$pri', postdata='$postdata'$extra");
		$insert_id = $this->db->insert_id();
		$insert_id && $this->db->query("REPLACE INTO ".UC_DBTABLEPRE."vars SET name='noteexists', value='1'");
		return $insert_id;
	}

	function send() {
		register_shutdown_function(array($this, '_send'));
	}

	function _send() {

		//note 如果内存表记录不存在，那么可能 mysql 被重启，需要再次判断通知是否存在

		//note 查看是否有通知
		$note = $this->_get_note();
		if(empty($note)) {
			//note 标示为不需要通知
			$this->db->query("REPLACE INTO ".UC_DBTABLEPRE."vars SET name='noteexists', value='0'");
			return NULL;
		}

		//note 遍历所有应用标识, 看哪个需要再次发送通知, 通知成功, 标识为1 否则-1
		$closenote = TRUE;
		foreach((array)$this->apps as $appid=>$app) {
			//note 只循环一轮，一个一个的发。
			$appnotes = $note['app'.$appid];
			if($app['recvnote'] && $appnotes != 1 && $appnotes > -UC_NOTE_REPEAT) {
				$this->sendone($appid, 0, $note);
				$closenote = FALSE;
				break;
			}
		}
		if($closenote) {
			$this->db->query("UPDATE ".UC_DBTABLEPRE."notelist SET closed='1' WHERE noteid='$note[noteid]'");
		}

		//note 垃圾清理
		$this->_gc();
	}

	function sendone($appid, $noteid = 0, $note = '') {
		$return = FALSE;
		$app = $this->apps[$appid];
		if($noteid) {
			$note = $this->_get_note_by_id($noteid);
		}
		$this->base->load('misc');
		$url = $this->get_url_code($note['operation'], $note['getdata'], $appid);
		$getcontent = trim(uc_fopen2($url, 500000, $note['postdata'], '', 1, $app['ip'], UC_NOTE_TIMEOUT));

		$returnsucceed = $getcontent != '' && $getcontent != '-1';

		$closedsqladd = $this->_close_note($note, $this->apps, $returnsucceed) ? ",closed='1'" : '';//

		if($returnsucceed) {
			if($this->operations[$note['operation']][2]) {
				$this->base->load($this->operations[$note['operation']][2]);
				$func = $this->operations[$note['operation']][3];
				$_ENV[$this->operations[$note['operation']][2]]->$func($appid, $getcontent);
			}
			$this->db->query("UPDATE ".UC_DBTABLEPRE."notelist SET app$appid='1', totalnum=totalnum+1, succeednum=succeednum+1, dateline='{$this->base->time}' $closedsqladd WHERE noteid='$note[noteid]'", 'SILENT');
			$return = TRUE;
		} else {
			$this->db->query("UPDATE ".UC_DBTABLEPRE."notelist SET app$appid = app$appid-'1', totalnum=totalnum+1, dateline='{$this->base->time}' $closedsqladd WHERE noteid='$note[noteid]'", 'SILENT');
			$return = FALSE;
		}
		return $return;
	}

	function _get_note() {
		//note 得到需要通知的总数
		$data = $this->db->fetch_first("SELECT * FROM ".UC_DBTABLEPRE."notelist WHERE closed='0' ORDER BY pri DESC, noteid ASC LIMIT 1");
		return $data;
	}

	function _gc() {
		rand(0, UC_NOTE_GC) == 0 && $this->db->query("DELETE FROM ".UC_DBTABLEPRE."notelist WHERE closed='1'");
	}

	//note 判断是否需要关闭通知
	function _close_note($note, $apps, $returnsucceed) {
		$note['app'.$appid] = $returnsucceed ? 1 : $note['app'.$appid] - 1;
		$appcount = count($apps);
		foreach($apps as $key => $app) {
			$appstatus = $note['app'.$app['appid']];
			if(!$app['recvnote'] || $appstatus == 1 || $appstatus <= -UC_NOTE_REPEAT) {
				$appcount--;
			}
		}
		if($appcount < 1) {
			return TRUE;
			//$closedsqladd = ",closed='1'";
		}
	}

	function _get_note_by_id($noteid) {
		$data = $this->db->fetch_first("SELECT * FROM ".UC_DBTABLEPRE."notelist WHERE noteid='$noteid'");
		return $data;
	}

	function get_url_code($operation, $getdata, $appid) {
		$app = $this->apps[$appid];
		$authkey = $this->db->result_first("SELECT authkey FROM ".UC_DBTABLEPRE."applications WHERE appid='$appid'");
		$url = $app['url'];
		$action = $this->operations[$operation][1];
		$code = urlencode(uc_authcode("$action&".($getdata ? "$getdata&" : '')."time=".$this->base->time, 'ENCODE', $authkey));
		return $url."/api/uc.php?code=$code";
	}

}

?>