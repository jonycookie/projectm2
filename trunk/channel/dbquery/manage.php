<?php require_once '../global.php';


/**
	添加/编辑碎片
*/
function king_ajax_edt(){
	
	global $king;
	$king->access('dbquery_edt');

	$kid=kc_get('kid',2);

	$sql="kid,kname,ntype,dbhost,dbname,dbfile,dbuser,dbpass,dbcharset";
	$array_sql=explode(',',$sql);

	if($GLOBALS['ismethod'] || empty($kid) || kc_post('reset')==1){//若kid为空，则添加
		$data=$_POST;
		if(!$GLOBALS['ismethod']){//预置项
			$data['ntype']=isset($_POST['ntype']) ? $_POST['ntype'] : 1;
		}
	}else{	//编辑数据，从数据库读出
		if(!$data=$king->db->getRows_one("select $sql from %s_dbquery where kid=$kid limit 1"))
			kc_error($king->lang->get('system/error/notrecord'));
	}
	$data=kc_data($array_sql,$data);

	//ntype
	$array_type=array(
		1=>'MySQL',
		2=>'SQLite',
	);
	$exp=" onClick=\"\$('#ntype').val(\$(this).val());setTimeout('\$.kc_ajax({URL:\'../dbquery/manage.php\',CMD:\'edt\',IS:1,METHOD:\'GET\',kid:\'$kid\',reset:1})',50)\" ";
	$s=$king->htmForm($king->lang->get('dbquery/label/type'),kc_htm_radio('ntype_show',$array_type,$data['ntype'],$exp));
	$s.=kc_htm_hidden(array('ntype'=>$data['ntype']));
	//kname
	$array=array(
		array('kname',0,1,50),
	);
	//验证重复值
	if(empty($kid)){
		$array[]=array('kname',12,$king->lang->get('dbquery/error/name'),$king->db->getRows_one("select kid from %s_dbquery where kname='".$king->db->escape($data['kname'])."'"));
	}else{
		$array[]=array('kname',12,$king->lang->get('dbquery/error/name'),$king->db->getRows_one("select kid from %s_dbquery where kname='".$king->db->escape($data['kname'])."' and kid<>$kid"));
	}

	$s.=$king->htmForm($king->lang->get('dbquery/label/name'),kc_htm_input('kname',$data['kname'],50,200),$array);

	if($data['ntype']==1){//mysql
		
		//dbhost
		$array=array(
			array('dbhost',0,1,50),
		);
		$s.=$king->htmForm($king->lang->get('dbquery/label/dbhost'),kc_htm_input('dbhost',$data['dbhost'],50,400),$array);
		//dbname
		$array=array(
			array('dbname',0,1,50),
		);
		$s.=$king->htmForm($king->lang->get('dbquery/label/dbname'),kc_htm_input('dbname',$data['dbname'],50,400),$array);
		//dbuser
		$array=array(
			array('dbuser',0,1,50),
		);
		$s.=$king->htmForm($king->lang->get('dbquery/label/dbuser'),kc_htm_input('dbuser',$data['dbuser'],50,200),$array);
		//dbpass
		$array=array(
			array('dbpass',0,0,50),
		);
		$s.=$king->htmForm($king->lang->get('dbquery/label/dbpass'),kc_htm_input('dbpass',$data['dbpass'],50,200),$array);
		//dbcharset
		$array=array(
			array('dbcharset',0,0,50),
		);
		$array_charset=array(
			'UTF-8'=>'UTF-8',
			'GBK'=>'GBK',
		);
		$s.=$king->htmForm($king->lang->get('dbquery/label/dbcharset'),kc_htm_select('dbcharset',$array_charset,$data['dbcharset']),$array);

		$height=340;
	}else{
		//dbfile
		$array=array(
			array('dbfile',0,4,50),
			array('dbfile',12,$king->lang->get('dbquery/error/dbfile'),!is_file(ROOT.$data['dbfile'])),
		);
		$s.=$king->htmForm($king->lang->get('dbquery/label/dbfile'),kc_htm_input('dbfile',$data['dbfile'],50,400),$array);

		$height=160;
	}

	if($GLOBALS['ischeck']){
		if($data['ntype']==1){
			$array=array(
				'ntype'=>1,
				'kname'=>$data['kname'],
				'dbhost'=>$data['dbhost'],
				'dbname'=>$data['dbname'],
				'dbuser'=>$data['dbuser'],
				'dbpass'=>$data['dbpass'],
				'dbcharset'=>$data['dbcharset'],
			);
		}else{
			$array=array(
				'ntype'=>2,
				'kname'=>$data['kname'],
				'dbfile'=>$data['dbfile'],
			);
		}
		if(empty($kid)){//insert
			$array['norder']=$king->db->neworder('%s_dbquery');
			$king->db->insert('%s_dbquery',$array);
		}else{
			$king->db->update('%s_dbquery',$array,"kid=$kid");
		}
		$king->cache->del("dbquery/info/{$data['kname']}");
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/'.(empty($kid) ?'add':'edt'))."</p>",1);
	}


	$tit=$king->lang->get('dbquery/title/'.(empty($kid)?'add':'edt'));
	$but=kc_htm_a($king->lang->get("system/common/".(empty($kid)?'add':'edit')),"{URL:'../dbquery/manage.php',CMD:'edt',kid:'$kid',IS:1}");
	kc_ajax($tit,$s,$but,'',440,$height+$GLOBALS['check_num']*15);
}

function king_ajax_delete(){
	global $king;
	$king->access('dbquery_delete');

	$list=kc_getlist();

	$king->db->query("delete from %s_dbquery where kid in ($list)");

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

}
function king_ajax_updown(){
	global $king;
	$king->access('dbquery_updown');

	$kid=kc_get('kid',2,1);
	$king->db->updown('%s_dbquery',$kid);

}
/**
菜单调用
*/
function inc_menu(){
	global $king;
	$left=array(
		''=>array(
			'href'=>'manage.php',
			'ico'=>'p5',
			'title'=>$king->lang->get('system/common/list'),
		),
		'edt'=>array(
			'href'=>'javascript:;',
			'ico'=>'c2',
			'title'=>$king->lang->get('system/common/add'),
			'rel'=>"{CMD:'edt',METHOD:'GET'}",
			'class'=>"k_ajax",
		),
	);
	return array($left,array());

}

function king_def(){
	global $king;
	$king->access('dbquery');

	$_cmd=array(
		'delete'=>$king->lang->get('system/common/del'),
	);
	$manage="'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'edt\',kid:'+K[0]+',METHOD:\'GET\'}\">'+\$.kc_icon('b2','".addslashes($king->lang->get('system/common/edit'))."')+'</a>'";
	$manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete\',list:'+K[0]+'}\">'+\$.kc_icon('d2','".addslashes($king->lang->get('system/common/del'))."')+'</a>'";
	$manage.="+\$.kc_updown(K[0])";

	$_js=array(
		"\$.kc_list(K[0],K[1],'{CMD:\'edt\',kid:'+K[0]+',METHOD:\'GET\'}')",
		$manage,
		"array_type[K[2]]",
		"'{king:dbquery name=\''+K[1]+'\' sql=\'[SQL语句]\' /}'",
	);

	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?pid=PID&rn=RN',$king->db->getRows_number('%s_dbquery',"bid=0 and ntype=0")));

	$_sql="select kid,kname,ntype from %s_dbquery order by norder desc,kid desc";
	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	$array_type=array();
	for($i=1;$i<=2;$i++){
		$array_type[$i]=$king->lang->get("dbquery/type/t$i");
	}
	$s.=kc_js2array('array_type',$array_type);

	$s.="ll('".$king->lang->get('dbquery/list/name')."','manage','".$king->lang->get('dbquery/list/type')."','".$king->lang->get('dbquery/list/tag')."',1);";

	foreach($res as $rs){
		$s.="ll({$rs['kid']},'".addslashes($rs['kname'])."',".$rs['ntype'].",0);";
	}


	$s.=$king->closeList();

	list($left,$right)=inc_menu();
	$king->skin->output($king->lang->get('dbquery/title/center'),$left,$right,$s);
}


?>