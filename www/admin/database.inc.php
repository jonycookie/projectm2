<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case ($operation=='backup'||$operation=='repair'):
	$Admin->MP(array("menu_database_backup","menu_database_repair"));
	include(iPATH.'admin/table.array.php');
	include iCMS_admincp_tpl("database.backup");
break;
case 'savebackup':
	$bak="# iCMS Backup SQL File\n# Version:".Version."\n# Time: ".get_date('',"Y-m-d H:i:s")."\n# iCMS: http://www.iDreamSoft.CN\n# --------------------------------------------------------\n\n\n";
	$iCMS->db->query("SET SQL_QUOTE_SHOW_CREATE = 0");
	
	$tabledb	= $_POST['tabledb'];
	$sizelimit	= isset($_POST['sizelimit'])?(int)$_POST['sizelimit']:(int)$_GET['sizelimit'];
	$start		= (int)$_GET['start'];
	$tableid	= (int)$_GET['tableid'];
	$step		= (int)$_GET['step'];
	$tablesel	= $_GET['tablesel'];
	$aaa		= $_GET['aaa'];
	$rows		= $_GET['rows'];
		
	!$tabledb && !$tablesel && alert('没有选择操作对象');
	!$tabledb && $tabledb=explode("|",$tablesel);
	!$step && $sizelimit/=2;
	
	$bakupdata=bakupdata($tabledb,$start);

	if(!$step){
		!$tabledb && alert('没有选择操作对象');
		$tablesel=implode("|",$tabledb);
		$step=1;
		$aaa=num_rand(10);
		$start=0;
		$bakuptable=bakuptable($tabledb);
	}
	$f_num=ceil($step/2);
	$filename='iCMS_'.get_date('',"md").'_'.$aaa.'_'.$f_num.'.sql';
	$step++;
	$writedata=$bakuptable ? $bakuptable.$bakupdata : $bakupdata;

	$t_name	= $tabledb[$tableid-1];
	$c_n	= $startfrom;
	if($stop==1){
		$files=$step-1;
		trim($writedata) && writefile(iPATH.'admin/data/'.$filename,$bak.$writedata,true,'ab');
		redirect("正在备份数据库表{$t_name}: 共{$rows}条记录<br>已经备份至{$c_n}条记录,已生成{$f_num}个备份文件，<br>程序将自动备份余下部分",__SELF__."?do=database&operation=savebackup&start={$startfrom}&tableid={$tableid}&sizelimit={$sizelimit}&step={$step}&aaa={$aaa}&tablesel={$tablesel}&rows={$rows}",3);
	} else{
		trim($writedata) && writefile(iPATH.'admin/data/'.$filename,$bak.$writedata,true,'ab');		
		if($step>1){
			for($i=1;$i<=$f_num;$i++){
				$temp=substr($filename,0,19).$i.".sql";
				if(file_exists("data/$temp")){
					$bakfile.='<a href="'."data/$temp".'">'.$temp.'</a><br>';
				}
			}
		}
		redirect("已全部备份完成,备份文件保存在data目录下",__SELF__."?do=database&operation=recover");
	}
break;
case 'recover':
	$Admin->MP("menu_database_recover");
	include(iPATH.'admin/table.array.php');
	$filedb=array();
	$handle=opendir(iPATH.'admin/data');
	while($file = readdir($handle)){
		if(eregi("^iCMS_",$file) && eregi("\.sql$",$file)){
			$strlen=eregi("^iCMS_",$file) ? 16 + strlen("iCMS_") : 19;
			$fp=fopen(iPATH."admin/data/$file",'rb');
			$bakinfo=fread($fp,200);
			fclose($fp);
			$detail=explode("\n",$bakinfo);
	 		$bk['name']=$file;
			$bk['version']=substr($detail[1],10);
			$bk['time']=substr($detail[2],8);
			$bk['pre']=substr($file,0,$strlen);
			$bk['num']=substr($file,$strlen,strrpos($file,'.')-$strlen);
			$filedb[]=$bk;
		}
	}
	include iCMS_admincp_tpl("database.recover");
break;
case 'bakincheck':
	include iCMS_admincp_tpl("database.bakincheck");
break;
case 'replace':
	include iCMS_admincp_tpl("database.replace");
break;
case 'post':
	if($action == 'repair'){
		empty($_POST['tabledb']) && alert('请选择表');
		$table = implode(',',$_POST['tabledb']);
		$rs = $iCMS->db->getArray("REPAIR TABLE $table");
		$_count=count($rs);
		for ($i=0;$i<$_count;$i++){
			$rs[$i]['Table']  = substr(strrchr($rs[$i]['Table'] ,'.'),1);
		}
		foreach($rs as $k=>$v){
			$t.='<ul style="clear:both;width:100%;text-align:left;font-size:12px;color:#333;font-weight: normal;"><li style="float:left;width:200px;">表：'.$v['Table'].'</li> <li style="float:left;width:120px;">操作：'.$v['Op'].'</li> <li style="float:left;width:320px;">状态：'.$v['Msg_text'].'</li> </ul>';
		}
		redirect("{$t}<br />修复表完成",__SELF__."?do=database&operation=repair");
	}
	if($action == 'optimize'){
		empty($_POST['tabledb']) &&alert('请选择表');
		$table = implode(',',$_POST['tabledb']);
		$rs = $iCMS->db->getArray("OPTIMIZE TABLE $table");
		$_count=count($rs);
		for ($i=0;$i<$_count;$i++){
			$rs[$i]['Table']  = substr(strrchr($rs[$i]['Table'] ,'.'),1);
		}
		foreach($rs as $k=>$v){
			$t.='<ul style="clear:both;width:100%;text-align:left;font-size:12px;color:#333;font-weight: normal;"><li style="float:left;width:200px;">表：'.$v['Table'].'</li> <li style="float:left;width:120px;">操作：'.$v['Op'].'</li> <li style="float:left;width:320px;">状态：'.$v['Msg_text'].'</li> </ul>';
		}
		redirect("{$t}<br />优化表完成",__SELF__."?do=database&operation=repair");
	}
	if($action == 'replace'){
	    $field		= $_POST["field"];
	    $pattern	= $_POST["pattern"];
	    $replacement= $_POST["replacement"];
	    $where		= $_POST["where"];
	    empty($pattern) && alert("查找项不能为空~!");
	    if($field=="body"){
	    	$iCMS->db->query("UPDATE `#iCMS@__articledata` SET `body` = REPLACE(`body`, '$pattern', '$replacement') {$where}");
	    }else{
	    	if($field=="tkd"){
		    	$iCMS->db->query("UPDATE `#iCMS@__article` SET `title` = REPLACE(`title`, '$pattern', '$replacement'),
		    	`keywords` = REPLACE(`keywords`, '$pattern', '$replacement'),
		    	`description` = REPLACE(`description`, '$pattern', '$replacement'){$where}");
	    	}else{
		    	$iCMS->db->query("UPDATE `#iCMS@__article` SET `$field` = REPLACE(`$field`, '$pattern', '$replacement'){$where}");
	    	}
	    }
	    redirect($iCMS->db->rows_affected."条记录被替换<br />操作完成!!",__SELF__."?do=database&operation=replace");
	}
	if(isset($_POST['delete'])){
		foreach($_POST['delete'] as $key => $value){
			if(eregi("\.sql$",$value)){
				DelFile(iPATH.'admin/data/'.$value);
			}
		}
		redirect("备份文件已删除!!",__SELF__."?do=database&operation=recover");
	}
break;
case 'bakin':
	$step	=$_GET['step'];
	$count	=$_GET['count'];
	$pre	=$_GET['pre'];

	if(!$count){
		$count=0;
		$handle=opendir(iPATH.'admin/data');
		while($file = readdir($handle)){
			if(eregi("^$pre",$file) && eregi("\.sql$",$file)){
				$count++;
			}
		}
	}
	!$step && $step=1;
	bakindata(iPATH.'admin/data/'.$pre.$step.'.sql');
	$i=$step;
	$step++;
	if($count > 1 && $step <= $count){
		redirect("正在导入第{$i}卷备份文件，程序将自动导入余下备份文件...",__SELF__."?do=database&operation=bakin&step=$step&count=$count&pre=$pre",3);
	}else{
		redirect("导入成功!",__SELF__."?do=database&operation=recover");
	}
break;
}
function num_rand($lenth){
	mt_srand((double)microtime() * 1000000);
	for($i=0;$i<$lenth;$i++){
		$randval.= mt_rand(0,9);
	}
	$randval=substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
	return $randval;
}

function bakupdata($tabledb,$start=0){
	global $iCMS,$sizelimit,$tableid,$startfrom,$stop,$rows;
	$tableid=$tableid?$tableid-1:0;
	$stop=0;
	$t_count=count($tabledb);
	for($i=$tableid;$i<$t_count;$i++){
		$ts=$iCMS->db->getRow("SHOW TABLE STATUS LIKE '$tabledb[$i]'");
		$rows=$ts->Rows;

		$limitadd="LIMIT $start,100000";
		$query = mysql_query("SELECT * FROM $tabledb[$i] $limitadd");
		$num_F = mysql_num_fields($query);

		while ($datadb = mysql_fetch_row($query)){
			$start++;
			$table=str_replace(DB_PREFIX,'iCMS_',$tabledb[$i]);
			$bakupdata .= "INSERT INTO $table VALUES("."'".addslashes($datadb[0])."'";
			$tempdb='';
			for($j=1;$j<$num_F;$j++){
				$tempdb.=",'".addslashes($datadb[$j])."'";
			}
			$bakupdata .=$tempdb. ");\n";
			if($sizelimit && strlen($bakupdata)>$sizelimit*1000){
				break;
			}
		}
		mysql_free_result($query);
		if($start>=$rows){
			$start=0;
			$rows=0;
		}

		$bakupdata .="\n";
		if($sizelimit && strlen($bakupdata)>$sizelimit*1000){
			$start==0 && $i++;
			$stop=1;
			break;
		}
		$start=0;
	}
	if($stop==1){
		$i++;
		$tableid=$i;
		$startfrom=$start;
		$start=0;
	}
	return $bakupdata;
}
function bakuptable($tabledb){
	global $iCMS;
	foreach($tabledb as $table){
		$creattable.= "DROP TABLE IF EXISTS $table;\n";
		$CreatTable = $iCMS->db->getRow("SHOW CREATE TABLE $table",ARRAY_A);
		$CreatTable['Create Table']=str_replace($CreatTable['Table'],$table,$CreatTable['Create Table']);
		$creattable.=$CreatTable['Create Table'].";\n\n";
		$creattable=str_replace(DB_PREFIX,'iCMS_',$creattable);
	}
	return $creattable;
}
function bakindata($filename) {
	global $iCMS;
	$sql=file($filename);
	$query='';
	$num=0;
	foreach($sql as $key => $value){
		$value=trim($value);
		if(!$value || $value[0]=='#') continue;
		if(eregi("\;$",$value)){
			$query.=$value;
			if(eregi("^CREATE",$query)){
				$extra = substr(strrchr($query,')'),1);
				$tabtype = substr(strchr($extra,'='),1);
				$tabtype = substr($tabtype, 0, strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
				$query = str_replace($extra,'',$query);
				if(version_compare(mysql_get_server_info(), '4.1.0', '>=')){
					$extra = DB_CHARSET ? "ENGINE=$tabtype DEFAULT CHARSET=".DB_CHARSET.";" : "ENGINE=$tabtype;";
				}else{
					$extra = "TYPE=$tabtype;";
				}
				$query .= $extra;
			}elseif(eregi("^INSERT",$query)){
				$query='REPLACE '.substr($query,6);
			}
			$iCMS->db->query(str_replace('iCMS_',DB_PREFIX,$query));
			$query='';
		} else{
			$query.=$value;
		}

	}
}
?>
