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
		$id		= (int)$_GET['fid'];
		$mid	= (int)$_GET['mid'];
		$name	= $_GET['name'];
		if($id){
			$rs	= $iCMS->db->getRow("SELECT * FROM `#iCMS@__field` where id='$id'",ARRAY_A);
			$rs['rules']=unserialize($rs['rules']);
			$mid=$rs['mid'];
			$name=$rs['name'];
		}
		$model=$iCMS->cache('model.cache','include/syscache',0,true);
		include iCMS_admincp_tpl("field.edit");
	break;
	case 'del':
//		$id		= (int)$_GET['fid'];
//		$table	= $_GET['table'].'_content';
//		$table && $iCMS->db->query("TRUNCATE TABLE `#iCMS@__$table`") && 
//		$id && $iCMS->db->query("DELETE FROM `#iCMS@__model` WHERE `id` ='$id'");
//		field_cache();
//		redirect("自定义模型删除成功!",__SELF__."?do=model&operation=manage",'3');
	break;
//	case 'truncate':
//		$table	= $_GET['table'].'_content';
//		$table && $iCMS->db->query("TRUNCATE TABLE `#iCMS@__$table`")&&
//		redirect("内容已清空!",__SELF__."?do=model&operation=manage",'3');
//	break;
	case 'post':
		if($action=='edit'){
		    $id		= (int)$_POST['id'];
		    $name	= $_POST['name'];
		    $field	= $_POST['field'];
		    $mid	= $_POST['mid'];
		    $type	= $_POST['type'];
		    $default= $_POST['default'];
		    $validate= $_POST['validate'];
		    $description= $_POST['description'];
		    $hidden	= $_POST['hidden']=="1"?'1':'0';
		    $rules	= addslashes(serialize($_POST['rules'][$type]));
		    
		    empty($name) && alert('字段名称不能为空！');
		    empty($field)&&$field=pinyin($name);
		    
		    !preg_match("/[a-zA-Z]/",$field{0}) && alert('字段名只能以英文字母开头');
			for($i=0;$i<strlen($field);$i++){
				!preg_match("/[a-zA-Z0-9_\-~]/",$field{$i}) && alert('字段名只能由英文字母或数字组成');
			}
			$SystemField=getSystemField();
			in_array($field,$SystemField) && alert('该字段是系统默认字段!请重新填写');

			if(empty($id)){
				$mid && $iCMS->db->getValue("SELECT `id` FROM `#iCMS@__field` where `field` = '$field' and mid='$mid'") && alert('该字段已经存在!请检查是否重复');
		    	$iCMS->db->insert('field',compact('name','field','description','mid','type','default','validate','hidden','rules'));
		    	field_cache();
		    	redirect("新增字段完成!",__SELF__."?do=field&operation=manage",'3');
			}else{
				$oField=$iCMS->db->getRow("SELECT `mid`,`field`,`type`,`default` FROM `#iCMS@__field` where `id` ='$id'",ARRAY_A);
				if($oField['field']!=$field||$oField['type']!=$type||$oField['default']!=$default){
					//更改所有使用该字段的表
					if($mid=="0"){
						//查出所有自定义模型
						$mArray = $iCMS->db->getArray("SELECT * FROM `#iCMS@__model` order by id DESC");
					}else{
						//查出所在自定义模型
						$mArray[0] = $iCMS->db->getRow("SELECT * FROM `#iCMS@__model` where id='$mid'",ARRAY_A);						
					}
					$_count	= count($mArray);
					for($i=0;$i<$_count;$i++){
						$fArray	= explode(',',$mArray[$i]['field']);
						if(in_array($oField['field'],$fArray)){
							$table	= $mArray[$i]['table'].'_content';
							$sql	= "alter table `#iCMS@__$table` change `{$oField['field']}` `{$field}` ";
							$len	= $type=="number"?$_POST['rules']['number']['maxnum']:$_POST['rules'][$type]['maxlength'];
							$sql.=getSqlType($type,$len,$default);
							
							$fKey			= array_search($oField['field'],$fArray); 
							$fArray[$fKey]	= $field;
							$mField			= implode(',',$fArray);
							$sql && $iCMS->db->query($sql);
							$iCMS->db->query("update `#iCMS@__model` SET `field`='$mField' where id='".$mArray[$i]['id']."'");
						}
					}
				}
				$iCMS->db->update('field',compact('name','field','description','mid','type','default','validate','hidden','rules'),compact('id'));
				field_cache();
				redirect("字段编辑完成!",__SELF__."?do=field&operation=manage",'3');
			}
		}
	break;
	default:
		$id			= (int)$_GET['mid'];
		$SystemField= getSystemField();
		$maxperpage =20;
		$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__field` order by id DESC");
		page($total,$maxperpage,"个字段");
		$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__field` order by id DESC LIMIT {$firstcount},{$maxperpage}");
		$_count=count($rs);
		$model=$iCMS->cache('model.id','include/syscache',0,true);
		include iCMS_admincp_tpl("field.manage");
}
?>
