<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'edit':
		$id		= (int)$_GET['mid'];
		$id && $rs	= $iCMS->db->getRow("SELECT * FROM `#iCMS@__model` where id='$id'",ARRAY_A);
		include iCMS_admincp_tpl("model.edit");
	break;
	case 'field':
		$id			= (int)$_GET['mid'];
		$SystemField= getSystemField();
		$rs		= getmodel($id);
		$fArray	= explode(',',$rs['field']);
		$_count	= count($fArray);
		$table	= $rs['table'];
		$field	= $iCMS->cache('field.model','include/syscache',0,true);
		//取得索引 show index from `iCMS`.`utf31_test_content`
		$index	= $iCMS->db->getCol("show index from `#iCMS@__$table`",4);
		include iCMS_admincp_tpl("model.field");
	break;	
	case 'editfield':
		$id		= (int)$_GET['mid'];
		if($rs	= getmodel($id)){
			$fArray	= explode(',',$rs['field']);
			$_count	= count($fArray);
			$fRs	= $iCMS->db->getArray("SELECT * FROM `#iCMS@__field` where mid='0'or mid='$id' order by id DESC");
			$_fcount= count($rs);
			$field	= $iCMS->cache('field.model','include/syscache',0,true);
			$model	= $iCMS->cache('model.id','include/syscache',0,true);
			$SystemField=getSystemField();
		}
		include iCMS_admincp_tpl("model.editfield");
	break;
	case 'delfield':
		$id		= (int)$_GET['mid'];
		$field	= $_GET['field'];
		if($rs	= getmodel($id)){
			$table	= $rs['table'];
			$fArray	= explode(',',$rs['field']);
			$fKey	= array_search($field,$fArray); 
			unset($fArray[$fKey]);
			$mField	= implode(',',$fArray);
			$iCMS->db->query("update `#iCMS@__model` SET `field`='$mField' where id='$id'");
			//删除数据库字段
			$iCMS->db->query("ALTER TABLE `#iCMS@__$table` DROP `$field`");
			model_cache();
		}
		redirect("字段删除成功!",__SELF__."?do=field&operation=manage&mid=".$id,'3');
	break;
	case 'addindex':
		$id		= (int)$_GET['mid'];
		$field	= $_GET['field'];
		if($rs	= getmodel($id)){
			$table	= $rs['table'];
			$iCMS->db->query("ALTER TABLE `#iCMS@__$table` ADD INDEX ( `$field` ) ");
		}
		redirect("索引设置成功!",__SELF__."?do=field&operation=manage&mid=".$id,'3');
	break;
	case 'delindex':
		$id		= (int)$_GET['mid'];
		$field	= $_GET['field'];
		if($rs	= getmodel($id)){
			$table	= $rs['table'];
			$iCMS->db->query("ALTER TABLE `#iCMS@__$table` DROP INDEX `$field` ");
		}
		redirect("索引删除成功!",__SELF__."?do=field&operation=manage&mid=".$id,'3');
	break;	
	case 'del':
		$id		= (int)$_GET['mid'];
		$table	= $_GET['table'].'_content';
		$table && $iCMS->db->query("DROP TABLE `#iCMS@__$table`");
		$id && $iCMS->db->query("DELETE FROM `#iCMS@__model` WHERE `id` ='$id'");
		model_cache();
		redirect("自定义模型删除成功!",__SELF__."?do=model&operation=manage",'3');
	break;
	case 'repair':
		$table	= $_GET['table'].'_content';
		$rs = $iCMS->db->getArray("REPAIR TABLE `#iCMS@__$table`");
		$_count=count($rs);
		for ($i=0;$i<$_count;$i++){
			$rs[$i]['Table']  = substr(strrchr($rs[$i]['Table'] ,'.'),1);
		}
		foreach($rs as $k=>$v){
			$t.='<ul style="clear:both;width:100%;text-align:left;font-size:12px;color:#333;font-weight: normal;"><li style="float:left;width:200px;">表：'.$v['Table'].'</li> <li style="float:left;width:120px;">操作：'.$v['Op'].'</li> <li style="float:left;width:320px;">状态：'.$v['Msg_text'].'</li> </ul>';
		}
		redirect("{$t}<br />修复表完成",__SELF__."?do=model&operation=manage");
	break;
	case 'optimize':
		$table	= $_GET['table'].'_content';
		$rs = $iCMS->db->getArray("OPTIMIZE TABLE `#iCMS@__$table`");
		$_count=count($rs);
		for ($i=0;$i<$_count;$i++){
			$rs[$i]['Table']  = substr(strrchr($rs[$i]['Table'] ,'.'),1);
		}
		foreach($rs as $k=>$v){
			$t.='<ul style="clear:both;width:100%;text-align:left;font-size:12px;color:#333;font-weight: normal;"><li style="float:left;width:200px;">表：'.$v['Table'].'</li> <li style="float:left;width:120px;">操作：'.$v['Op'].'</li> <li style="float:left;width:320px;">状态：'.$v['Msg_text'].'</li> </ul>';
		}
		redirect("{$t}<br />优化表完成",__SELF__."?do=model&operation=manage");
	break;
	case 'truncate':
		$table	= $_GET['table'].'_content';
		$table && $iCMS->db->query("TRUNCATE TABLE `#iCMS@__$table`");
		redirect("内容已清空!",__SELF__."?do=model&operation=manage",'3');
	break;
	case 'post':
		if($action=='editfield'){
			$id		= (int)$_POST['id'];
			if($rs	= getmodel($id)){
				$table	= $rs['table'];
				$fArray	= explode(',',$rs['field']);
				$order	= $_POST['order'];
				$diff	= array_diff_values ($order,$fArray);

				if($diff['+'])foreach($diff['+'] AS $field){//新增
					//增加自定义数据库模型中的字段
					$col 	= $iCMS->db->getCol("describe `#iCMS@__$table`");
					$sql	= "ALTER TABLE `#iCMS@__$table`";
					if(in_array($field,$col)){
						$sql.= " CHANGE COLUMN `$field` `$field`";
					}else{
						$sql.= " ADD COLUMN `$field`";
					}
					$type	= $_POST['type'][$field][$id]?$_POST['type'][$field][$id]:$_POST['type'][$field][0];
					$len	= $_POST['len'][$field][$id]?$_POST['len'][$field][$id]:$_POST['len'][$field][0];
					$default= $_POST['default'][$field][$id]?$_POST['default'][$field][$id]:$_POST['default'][$field][0];
					
					$sql.= getSqlType($type,$len,$default);
					$sql.= ' after `'.end($col).'`'; 
					$iCMS->db->query($sql);
				}
				if($diff['-'])foreach($diff['-'] AS $field){//减少
					//删除自定义数据库模型中的字段
					$iCMS->db->query("ALTER TABLE `#iCMS@__$table` DROP `$field`");
				}
				$mField	= implode(',',$order);
				$iCMS->db->query("update `#iCMS@__model` SET `field`='$mField' where id='$id'");
				model_cache();
			}
			redirect("模型字段更新成功!",__SELF__."?do=model&operation=editfield&mid=".$id,'3');
		}
		if($action=='edit'){
		    $id		= (int)$_POST['id'];
		    $name	= $_POST['name'];
		    $table	= $_POST['table'];
		    $description= $_POST['desc'];
		    $listpage=$_POST['listpage'];
		    $showpage=$_POST['showpage'];
		    
		    empty($name) && alert('模型名称不能为空！');
		    empty($table)&&$table=pinyin($name);
		    
		    !preg_match("/[a-zA-Z]/",$table{0}) && alert('模型表名只能以英文字母开头');
			for($i=0;$i<strlen($table);$i++){
				!preg_match("/[a-zA-Z0-9_\-~]/",$table{$i}) && alert('模型表名只能由英文字母或数字组成');
			}

			if(empty($id)){
				$addtime = time();
				$iCMS->db->getValue("SELECT `id` FROM `#iCMS@__model` where `table` = '$table'") && alert('该模型已经存在!请检查是否重复');
//				$field='cid,order,title,customlink,tags,pubdate,hits,digg,comments,type,vlink,top,visible';
				$field='cid,order,title,customlink,editor,tags,pubdate,type,vlink,top';
		    	$iCMS->db->insert('model',compact('name','table','description','field','listpage','showpage','addtime'));
		    	$mid	= $iCMS->db->insert_id;
		    	//创建模型基础表
		    	$sql="CREATE TABLE `".DB_PREFIX.$table."_content` (
                 `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                 `cid` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                 `order` SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0',
                 `title` VARCHAR(255) NOT NULL DEFAULT '',
                 `customlink` VARCHAR(255) NOT NULL DEFAULT '',
                 `editor` VARCHAR(200) NOT NULL DEFAULT '',
                 `userid` INT(10) UNSIGNED NOT NULL DEFAULT '0',   
                 `tags` VARCHAR(255) NOT NULL DEFAULT '',
                 `pubdate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                 `hits` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                 `digg` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                 `comments` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                 `type` VARCHAR(255) NOT NULL DEFAULT '',
                 `vlink` VARCHAR(255) NOT NULL DEFAULT '',
                 `top` SMALLINT(6) NOT NULL DEFAULT '0',
                 `visible` ENUM('0','1') NOT NULL DEFAULT '1',
                 `postype` TINYINT(1) NOT NULL DEFAULT '0',
                 PRIMARY KEY (`id`),
				 KEY `cid` (`visible`,`cid`),
				 KEY `hits` (`visible`,`hits`),
				 KEY `digg` (`visible`,`digg`),
				 KEY `comments` (`visible`,`comments`),
				 KEY `id` (`visible`,`id`),
				 KEY `pubdate` (`visible`,`pubdate`),
				 KEY `customlink` (`visible`,`customlink`)
               ) ENGINE=MYISAM DEFAULT CHARSET=".DB_CHARSET;
                $iCMS->db->query($sql);
                model_cache();
		    	redirect("新增模型完成!",__SELF__."?do=model&operation=manage",'3');
			}else{
				$oTable=$iCMS->db->getValue("SELECT `table` FROM `#iCMS@__model` where `id` ='$id'");
				if($oTable!=$table){
					$iCMS->db->query("RENAME TABLE `".DB_PREFIX.$oTable."_content` TO `".DB_PREFIX.$table."_content`");
				}
				$iCMS->db->update('model',compact('name','table','desc','listpage','showpage'),array('id'=>$id));
				model_cache();
				redirect("模型编辑完成!",__SELF__."?do=model&operation=manage",'3');
			}
		}
		if($action=='order'){
		    $id		= (int)$_POST['id'];
		    $field	= implode(',',$_POST['order']);
			$iCMS->db->update('model',compact('field'),compact('id'));
			model_cache();
			redirect("字段排序完成!",__SELF__."?do=model&operation=field&mid=".$id,'3');
		}
	break;
	default:
		$Admin->MP("menu_model_manage");
		$maxperpage =20;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__model` order by id DESC");
		page($total,$maxperpage,"个模型");
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__model` order by id DESC LIMIT {$firstcount},{$maxperpage}");
		$_count=count($rs);
		include iCMS_admincp_tpl("model.manage");
}
?>
