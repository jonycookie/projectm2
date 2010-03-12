<?php !defined('INC') && exit('No direct script access allowed');

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

class portal_class{// implements KingCMS_module

private $path='portal';	//当前模块目录
private $dbver=103;	//当前模块的数据库版本
public  $lang;
public  $ntype=array(99,1,2,3,4,5,6,7,8,9,10,11,12,13,14);//模型类型总数量，从0开始计算，所以多出一个
public  $isHtm=False;
public  $holdmodel=array('list','pagelist','nav','page','tag','model','menu','menu1','menu2','menu3','menu4','menu5','skin','data');//保留对象
public  $array_varchar=array(1,4,5,6,7,8,10,12,14);//varchar类型的字段


/**
	构造函数，主要是用做版本判断
*/
public function __construct(){
	global $king;

	$dbver=(int)$king->getModuleVer($this->path);
	if($this->dbver > $dbver){
		if($this->install_update($dbver)){//成功则返回True，则更新数据库中的版本信息
			$array=array(
				'ndbver'=>$this->dbver,
			);
			$king->db->update('%s_module',$array,"kpath='{$this->path}'");
		}
	}
}


/* ------>>> 功能部分 <<<---------------------------- */

/**
	输出预设的格式化路径
	@param array $info
	@return string
*/
public function depathMode($info){
	global $king;
	$path=str_replace('TIME',time(),$info['kpathmode']);
	if(false!==strpos($path,'ID')){//如果有ID
		$model=$this->infoModel($info['modelid']);
		$res=$king->db->getRows_one("select max(kid) from %s__{$model['modeltable']}");
		$res[0]
			? $path=str_replace('ID',$res[0]+1,$path)
			: $path=str_replace('ID',1,$path);
	}
	$date=explode(',',kc_formatdate(time(),'Y,m,d,G,i,s,M'));

	$path=str_replace('yyyy',$date[0],$path);
	$path=str_replace('yy',substr($date[0],2),$path);
	$path=str_replace('dd',$date[2],$path);
	$path=str_replace('hh',$date[3],$path);
	$path=str_replace('mm',$date[4],$path);
	$path=str_replace('ss',$date[5],$path);
	$path=str_replace('MMM',$date[6],$path);
	$path=str_replace('MM',$date[1],$path);
	while(False!==strpos($path,'RND')){
		$path=preg_replace('/RND/',kc_random(3),$path,1);
	}

	while($king->db->getRows_one("select kpath from %s__{$model['modeltable']} where kpath='".$king->db->escape($path)."';")){
		$ext=kc_f_ext($path);//扩展名
		$path=substr($path,0,strlen($path)-strlen($ext)-1).$king->config('rewriteline').kc_random(6).'.'.$ext;
	}


	return $path;
}
/**
	帮助提示
	@paran int   $kid  %s_field的对应id
	@param $content 传递内容
**/
public function help($kid,$content,$width=320,$height=160){
	global $king;
	$s=isset($content{0}) ? "<a class=\"k_help\" href=\"javascript:;\" rel=\"{CMD:'help',kid:$kid,ID:'k_help_Fly',URL:'../portal/manage.php',IS:2}\" title=\"".$king->lang->get('system/common/help')."\"><img class=\"g6 os\" src=\"../system/images/white.gif\"/></a>" : '';
	return $s;
}
/**
	解析表单项目
	@param array  $rs    当前对象的数组
	@param array  $data  表单值
	@param array  $info  模型及字段相关信息，由listid值获取
	@param int    $is    默认为1后台，0的时候是前台调用
	@param int    $at    默认为1首页，2的时候是次页调用
	@param int    $group 默认为1非组，0的时候是[组]调用
*/
public function formDeCode($rs,$data,$info,$is=1,$at=1,$group=1){/*
$sql='ktitle,kfield,ntype,nvalidate,nsizemin,nsizemax,kdefault,koption,nstylewidth,nstyleheight,nupfile,issearch,isshow';
*/
	global $king;
	$model=$this->infoModel($info['modelid']);
	$s='';$h='';
	$c=array();
	$f=$rs['kfield'];
	switch($rs['ntype']){

		case 0://系统字段
			switch($f){

				case 'ktitle':
					if($at==1){//首页必填
						$c[]=array($f,0,1,100);
					}else{//次页可选择不填
						$c[]=array($f,0,0,100);
					}
					$s='<input type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in w400" maxlength="100" />';
					$h='<a id="cktitle" class="k_ajax" rel="{URL:\'manage.content.php\',CMD:\'ckre\',obj:\'ktitle\',listid:'.$info['listid'].',kid:\''.$data['kid'].'\',ID:\'cktitle\',IS:2,ktitle:$(\'#ktitle\').val()}" >';
					$h.=kc_icon('a7',$king->lang->get('system/common/ckre')).'</a>';
				break;

				case 'ksubtitle':
					$c=$data[$f]!=''?array(array($f,0,4,20)):array();
					$s='<input type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in w300" maxlength="20" />';
				break;

				case 'kkeywords':
					$c[]=array($f,0,0,100);
					$s='<input type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in w400" maxlength="100" />';
					$h='<a class="k_ajax" rel="{URL:\'manage.content.php\',CMD:\'keywords\',VAL:\''.$f.',ktitle,listid\'}" >';
					$h.=kc_icon('d7',$king->lang->get('portal/label/insert')).'</a>'.kc_help('portal/help/comma',350,100);
				break;

				case 'ktag':
					$c[]=array($f,0,0,100);
					$s='<input type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in w400" maxlength="100" />';
					$h='<a class="k_ajax" rel="{URL:\'manage.content.php\',CMD:\'tag\',VAL:\''.$f.',ktitle,listid\'}" >';
					$h.=kc_icon('d7',$king->lang->get('portal/label/insert')).'</a>';
					$h.='<a href="manage.tag.php" target="_blank">'.kc_icon('e7',$king->lang->get('system/common/manage')).'</a>'.kc_help('portal/help/comma',350,100);
				break;

				case 'kimage':
					$c[]=array($f,0,0,255);
					$c[]=array($f,15,null,$king->config('upimg'));
					$s='<input type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in w400" maxlength="100" />';
					$h=kc_f_brow('kimage',$king->config('uppath').'/image/',0);
				break;

				case 'kpath':
					if($info['npage']==0&&$king->admin['adminmode']!=0){//如果生成静态，则
						$c[]=array($f,0,1,255);
						$c[]=array($f,15);
						if($data['kid']){
							$c[]=array($f,12,$king->lang->get('portal/tip/isexist1'),$king->db->getRows_one("select kpath from %s__{$model['modeltable']} where kpath='".$king->db->escape($data['kpath'])."' and kid<>{$data['kid']};"));
						}else{
							$c[]=array($f,12,$king->lang->get('portal/tip/isexist1'),$king->db->getRows_one("select kpath from %s__{$model['modeltable']} where kpath='".$king->db->escape($data['kpath'])."';"));
						}

						$s='<input type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in w400" maxlength="100" />';
						$h='<a id="ckpath" class="k_ajax" rel="{URL:\'manage.content.php\',ID:\'ckpath\',CMD:\'ckre\',obj:\'kpath\',listid:'.$info['listid'].',kid:\''.$data['kid'].'\','.$f.':$(\'#'.$f.'\').val()}" >';
						$h.=kc_icon('a7',$king->lang->get('system/common/ckre')).'</a>';
					}else{
						return;
//						$s=kc_htm_hidden(array($f=>htmlspecialchars($data[$f])));
					}
				break;

				case 'nattrib':
					$kid=$rs['kid'];

					$isshow=($is==1)?"isadmin{$at}":"isuser{$at}";

					if(!$res2=$king->db->getRows("select * from %s_field where modelid={$info['modelid']} and {$isshow}=1 and kid1={$kid} order by norder,kid;"))//全部调用
						return;//如果没有可见项，则返回空值

					$s='';
					$c2=array();
					$h2='';//($s,$c,$h)
					foreach($res2 as $rs2){

						list($s2,$c2,$h2)=$this->formdecode($rs2,$data,$info,$is,$at,0);
						$s.='<span class="k_field">';
						if($rs2['istitle'])
							$s.='<label>'.addslashes($rs2['ktitle']).'</label>';
						$s.=$s2.'</span>';

						$c=array_merge($c,$c2);
						$h.=$h2;
					}

				break;

				case 'kcontent':
					$c[]=array($f,0,$rs['nsizemin'],$rs['nsizemax']);
					$c[]=array($f,21);
					if($is){
						$s.='<span><input type="checkbox" id="isgrab" name="isgrab" '.(kc_post('isgrab')?'checked="true"':'').'/><label for="isgrab">'.$king->lang->get('system/common/grab').'</label>';
						$s.='<input type="checkbox" id="isoneimage" name="isoneimage" '.(kc_post('isoneimage')?'checked="true"':'').'/><label for="isoneimage">'.$king->lang->get('portal/remove/oneimage').'</label></span>';
						//$s.='<input type="checkbox" id="isreplacetag" name="isreplacetag" '.(kc_post('isreplacetag')?'checked="true"':'').'/><label for="isreplacetag">'.$king->lang->get('portal/remove/replacetag').'</label></span>';


						$s.='<span><img src="../system/images/white.gif" class="os n4"/>'.$king->lang->get('system/common/filtercode').'[<input type="checkbox" id="isremovea" name="isremovea" '.(kc_post('isremovea')?'checked="true"':'').'/><label for="isremovea">'.$king->lang->get('portal/remove/a').'</label>';
						$s.='<input type="checkbox" id="isremovetable" name="isremovetable" '.(kc_post('isremovetable')?'checked="true"':'').'/><label for="isremovetable">'.$king->lang->get('portal/remove/table').'</label>';
						$s.='<input type="checkbox" id="isremovestyle" name="isremovestyle" '.(kc_post('isremovestyle')?'checked="true"':'').'/><label for="isremovestyle">'.$king->lang->get('portal/remove/style').'</label>';
						$s.='<input type="checkbox" id="isremoveid" name="isremoveid" '.(kc_post('isremoveid')?'checked="true"':'').'/><label for="isremoveid">'.$king->lang->get('portal/remove/id').'</label>';
						$s.='<input type="checkbox" id="isremoveclass" name="isremoveclass" '.(kc_post('isremoveclass')?'checked="true"':'').'/><label for="isremoveclass">'.$king->lang->get('portal/remove/class').'</label>]</span><br/>';
					}
					$s.=kc_htm_editor($f,$data[$f],$rs['nstylewidth'],$rs['nstyleheight']);
				break;

				case 'krelate':
					$c=$data[$f]?array(array($f,3)):array();
					$s='<input type="hidden" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'"/>';
					$s.='<table class="k_side" cellspacing="0"><tr><td id="krelateshow" class="k_in">';
					$s.='Loading...';
					$s.='</td><td>';
					$s.='<a class="k_ajax" rel="{URL:\'manage.content.php\',CMD:\'hrelate\',VAL:\'krelate,listid,kid\'}" >'.kc_icon('b7',$king->lang->get('portal/label/relate1')).'</a>';//手动匹配
					$s.='<a class="k_ajax" rel="{URL:\'manage.content.php\',ID:\'krelateshow\',CMD:\'relate\',VAL:\'ktitle,listid,kid\'}">'.kc_icon('c7',$king->lang->get('portal/label/relate0')).'</a>';//自动匹配
					$s.=kc_help('portal/help/relate');
					$s.='</td></tr></table>';
					$s.="<script>function krelateshow(){\$.kc_ajax('{URL:\'manage.content.php\',CMD:\'relateload\',ID:\'krelateshow\',VAL:\'krelate,listid\'}')};krelateshow();</script>";
				break;

				case 'kdescription':
					$c[]=array($f,0,0,255);
					$s='<textarea rows="4" cols="100" class="k_in w400" name="'.$f.'" maxlength="255" >'.htmlspecialchars($data[$f]).'</textarea>';
				break;

				case 'nprice':
					$c[]=array($f,0,1,11);
					$c[]=array($f,3);
					$s='<input type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in w100" maxlength="11" />';
					$h=kc_htm_setvalue_nl($f,$rs['koption']);
				break;

				case 'nweight':
					$c[]=array($f,0,1,11);
					$c[]=array($f,2);
					$s='<input type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in w100" maxlength="11" />';
					$h=kc_htm_setvalue_nl($f,$rs['koption']).kc_help('portal/help/weight');
				break;

				case 'nnumber':
					$c[]=array($f,0,1,11);
					$c[]=array($f,2);
					$s='<input type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in w100" maxlength="11" />';
					$h=kc_htm_setvalue_nl($f,$rs['koption']);
				break;

				default:

					if(in_array($f,array('nshow','nhead','ncommend','nup','nfocus','nhot'))){
						$c[]=array($f,20,null,array(0,1));
//						kc_error(print_r($data,1));
						$checked=empty($data[$f]) ? '' : ' checked="checked"';
						$s='<input'.$checked.' type="checkbox" id="'.$f.'" name="'.$f.'" value="1" />';
						$s.='<label for="'.$f.'">'.htmlspecialchars($rs['ktitle']).'</label>';
					}
			}

		break;


//value="'.htmlspecialchars($data[$f]).'"
//$rs='ktitle,kfield,ntype,nvalidate,nsizemin,nsizemax,kdefault,koption,nstylewidth,nstyleheight,nupfile,issearch,isshow';
		case 1://单行文本
			$c[]=array($f,0,$rs['nsizemin'],$rs['nsizemax']);
			if($rs['nvalidate']!=0)
				$c[]=array($f,$rs['nvalidate']);
			$s='<input type="text" name="'.$f.'" id="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in" style="width:'.$rs['nstylewidth'].'px;" maxlength="'.$rs['nsizemax'].'" />';
			$h=$this->help($rs['kid'],$rs['khelp']).kc_htm_setvalue_nl($f,$rs['koption']);
		break;

		case 2://多行文本 (不支持编辑器)
			$c[]=array($f,0,$rs['nsizemin'],$rs['nsizemax']);
			$s='<table class="k_side" cellspacing="0"><tr><td><textarea name="'.$f.'" class="k_in" style="width:'.$rs['nstylewidth'].'px;height:'.$rs['nstyleheight'].'px;" >'.htmlspecialchars($data[$f]).'</textarea></td><td>'.$this->help($rs['kid'],$rs['khelp']).'</td></tr></table>';
		break;

		case 3://多行文本 (支持编辑器)
			$c[]=array($f,0);
			$c[]=array($f,21);
			$s='<table class="k_side" cellspacing="0"><tr><td>'.kc_htm_editor($f,$data[$f]).'</td><td>'.$this->help($rs['kid'],$rs['khelp']).'</td></tr></table>';

		break;

		case 4://单选 (下拉列表)
			$c[]=array($f,0,0,255);
			$array_def=explode(NL,$rs['koption']);
			$array_select=array();
			foreach($array_def as $val){
				if(isset($val{0})){//不能为空值
					$array_val=explode('|',$val,2);
					if(count($array_val)>1){//有分割符号
						$array_select[$array_val[0]]=$array_val[1];
					}else{
						$array_select[$val]=$val;
					}
				}
			}
			$c[]=array($f,20,null,array_keys($array_select));
			$s=kc_htm_select($f,$array_select,$data[$f]);
			$h=$this->help($rs['kid'],$rs['khelp']);
		break;

		case 5://单选 (radio)
			$c[]=array($f,0,0,255);
			$array_def=explode(NL,$rs['koption']);
			$array_radio=array();
			foreach($array_def as $val){
				if(isset($val{0})){//不能为空值
					$array_val=explode('|',$val,2);
					if(count($array_val)>1){//有分割符号
						$array_radio[$array_val[0]]=$array_val[1];
					}else{
						$array_radio[$val]=$val;
					}
				}
			}
			$c[]=array($f,20,null,array_keys($array_radio));
			$s=kc_htm_radio($f,$array_radio,$data[$f]);
			$h=$this->help($rs['kid'],$rs['khelp']);
		break;

		case 6://多选 (多选列表)
			$c[]=array($f,0,0,255);
			$array_def=explode(NL,$rs['koption']);
			$array_select=array();
			foreach($array_def as $val){
				if(isset($val{0})){//不能为空值
					$array_val=explode('|',$val,2);
					if(count($array_val)>1){//有分割符号
						$array_select[$array_val[0]]=$array_val[1];
					}else{
						$array_select[$val]=$val;
					}
				}
			}
			$array_keys=array_keys($array_select);//可选值，但需要再加一个空值进去
			$array_keys['']='';
			$c[]=array($f,23,null,$array_keys);
			$s=kc_htm_select($f,$array_select,$data[$f],' multiple="multiple" style="width:'.$rs['nstylewidth'].'px;height:'.$rs['nstyleheight'].'px;"');
			$h=$this->help($rs['kid'],$rs['khelp']);
		break;

		case 7://复选框
			$c[]=array($f,0,0,255);
			$array_def=explode(NL,$rs['koption']);
			$array_radio=array();
			foreach($array_def as $val){
				if(isset($val{0})){//不能为空值
					$array_val=explode('|',$val,2);
					if(count($array_val)>1){//有分割符号
						$array_radio[$array_val[0]]=$array_val[1];
					}else{
						$array_radio[$val]=$val;
					}
				}
			}
			$array_keys=array_keys($array_radio);//可选值，但需要再加一个空值进去
			$array_keys['']='';

			$c[]=array($f,23,null,$array_keys);
			$s=kc_htm_checkbox($f,$array_radio,$data[$f]);
			$h=$this->help($rs['kid'],$rs['khelp']);
		break;

		case 8://文件上传(图片类型)
			$c[]=array($f,0,$rs['nsizemin'],$rs['nsizemax']);
			$s='<input type="text" name="'.$f.'" id="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in" style="width:'.$rs['nstylewidth'].'px;" maxlength="'.$rs['nsizemax'].'" />';
			$h=kc_f_brow($f,$king->config('uppath').'/image',0).$this->help($rs['kid'],$rs['khelp']).kc_htm_setvalue_nl($f,$rs['koption']);
		break;

		case 9://图片列表
			$c[]=array($f,0,$rs['nsizemin'],$rs['nsizemax']);// class="w800"
			$s=kc_htm_hidden(array($f=>$data[$f]));
			$src=$king->lang->get('portal/label/imgsrc');
			$alt=$king->lang->get('portal/label/imgalt');
			$s.=kc_htm_input("S$f",$src,400,400," onClick=\"\$(this).val(this.value=='$src'?'':this.value)\"")."<br/>";
			$s.=kc_htm_input("A$f",$alt,400,300," onClick=\"\$(this).val(this.value=='$alt'?'':this.value)\"");
			$s.=" <input type=\"button\" value=\"".$king->lang->get('system/common/add')."\" id=\"B$f\" />";// onClick=\"F$f()\"
			$s.="<div id=\"W$f\" class=\"imglist\" style=\"width:{$rs['nstylewidth']}px\"></div>";
			//JavaScript中判断Src值是否为远程图像，如果是的话，则直接抓取图片到本地
			$s.="<script>
				//点击“添加”按钮的时候的效果
				function F$f(){
					var t=String.fromCharCode(9);
					var S=\$('#S$f').val().replace(t,'');
					var A=\$('#A$f').val().replace(t,'');
					//判断是否填写图片地址
					if(S=='' || S=='$src'){
						alert('".$king->lang->get('portal/error/notimgsrc')."');
						return;
					}

					if(A=='' || A=='$alt') A=S;
					var s=S+t+A;
					var ss=\$('#$f').val();
					//如果S为空，则提示错误
					if(ss==''){
						\$('#$f').val(s);
					}else{
						//判断图片是否已经存在
						var ss_1=ss.split(t+t);
						var ss_2,src;
						for(i=0;i<ss_1.length;i++){
							ss_2=ss_1[i].split(t);
							src=ss_2[0];
							if(src==S){
								alert('".$king->lang->get('portal/error/retimgsrc')."');
								\$('#S$f').val('');
								return;
							}
						}
						\$('#$f').val(ss+t+t+s);
					}
					\$('#S$f').val('');
					\$('#A$f').val('');
					S$f();
				}
				//显示数据
				function S$f(){
					var t=String.fromCharCode(9);
					var ss=\$('#$f').val();
					if(ss==''){
						\$('#W$f').html('');
						return;
					}
					var s='';
					var ss_1=ss.split(t+t);
					var ss_2,alt,src;
					for(i=0;i<ss_1.length;i++){
						ss_2=ss_1[i].split(t);
						src=ss_2[0];
						alt=ss_2[1];

						s+='<span class=\"img\" title=\"'+alt+'\">';
						s+='<a href=\"javascript:;\" onClick=\"E$f('+i+')\" class=\"img\" title=\"".$king->lang->get('system/common/edit')."\">';
						s+='<img class=\"img\" src=\"'+(ss_2[0].match(/^[a-zA-Z]{3,10}:\/\/[^\s]+\$/) ? ss_2[0] : '../'+ss_2[0])+'\"/>';
						s+='</a>';

						s+='<a class=\"title\" href=\"javascript:;\" onClick=\"D$f('+i+')\" title=\"".$king->lang->get('system/common/del')."\">';
						s+=\$.kc_icon('j2')+alt;
						s+='</a>';
						s+='</span>';
					}
					\$('#W$f').html(s);
				}
				//删除图片
				function D$f(num){
					var t=String.fromCharCode(9);
					var ss=\$('#$f').val();
					if(ss==''){
						\$('#W$f').html('');
						return;
					}
					var ss_1=ss.split(t+t);
					
					ss_1.splice(num,1);

					\$('#$f').val(ss_1.join(t+t));

					S$f();
					
				}
				//编辑图片
				function E$f(num){
					var t=String.fromCharCode(9);
					var ss=\$('#$f').val();
					if(ss=='') return;
					var ss_1=ss.split(t+t);

					var ss_num=ss_1.slice(num,num+1);
					var ss_2=ss_num[0].split(t);

					\$('#S$f').val(ss_2[0]);
					\$('#A$f')
						.val(ss_2[1])
						.width(250);

					\$('#B$f')
						.val('".$king->lang->get('system/common/up')."')
						.unbind('click')
						.click(function(){U$f(num)});
					if(!\$('#B$f').next('input').length){
						\$('#B$f').after('<input type=\"button\" value=\"".$king->lang->get('system/common/cancel')."\" onClick=\"C$f()\"/>');
					}
				}
				//提交编辑结果
				function U$f(num){
					var t=String.fromCharCode(9);
					var ss=\$('#$f').val();

					var S=\$('#S$f').val().replace(t,'');
					var A=\$('#A$f').val().replace(t,'');

					//判断是否填写图片地址
					if(S=='' || S=='$src'){
						alert('".$king->lang->get('portal/error/notimgsrc')."');
						return;
					}

					var ss_1=ss.split(t+t);
					ss_1.splice(num,1,S+t+A);

					\$('#$f').val(ss_1.join(t+t));

					\$('#S$f').val('');
					\$('#A$f').val('')
						.width(300);

					\$('#B$f').val('".$king->lang->get('system/common/add')."')
						.unbind('click')
						.click(function(){F$f()})
						.next('input').remove();

					S$f();

				}
				//取消按钮
				function C$f(){

					\$('#S$f').val('');
					\$('#A$f').val('')
						.width(300);

					\$('#B$f').val('".$king->lang->get('system/common/add')."')
						.unbind('click')
						.click(function(){F$f()})
						.next('input').remove();

				}
				S$f();
				\$('#B$f').click(function(){F$f()});
				</script>";
			$h=kc_f_brow("S$f",$king->config('uppath').'/image/',0,1,"F$f()");
			$h.=$this->help($rs['kid'],$rs['khelp']);
		break;

		case 10://文件上传(文件类型)
			$c[]=array($f,0,$rs['nsizemin'],$rs['nsizemax']);
			$s='<input type="text" name="'.$f.'" id="'.$f.'" value="'.htmlspecialchars($data[$f]).'" class="k_in" style="width:'.$rs['nstylewidth'].'px;" maxlength="'.$rs['nsizemax'].'" />';
			$h=kc_f_brow($f,$king->config('uppath').'/file/',1).$this->help($rs['kid'],$rs['khelp']).kc_htm_setvalue_nl($f,$rs['koption']);
		break;

		case 11://文件列表
			$c[]=array($f,0,$rs['nsizemin'],$rs['nsizemax']);
			$s=kc_htm_hidden(array($f=>$data[$f]));
			$src=$king->lang->get('portal/label/filesrc');
			$alt=$king->lang->get('portal/label/filealt');
			$s.=kc_htm_input("S$f",$src,400,400," onClick=\"\$(this).val(this.value=='$src'?'':this.value)\"")."<br/>";
			$s.=kc_htm_input("A$f",$alt,400,300," onClick=\"\$(this).val(this.value=='$alt'?'':this.value)\"");
			$s.=" <input type=\"button\" value=\"".$king->lang->get('system/common/add')."\" id=\"B$f\" />";
			$s.="<div id=\"W$f\" class=\"filelist\" style=\"width:{$rs['nstylewidth']}px\"></div>";
			$s.="<script>
				//点击“添加”按钮的时候的效果
				function F$f(){
					var t=String.fromCharCode(9);
					var S=\$('#S$f').val().replace(t,'');
					var A=\$('#A$f').val().replace(t,'');
					//判断是否填写文件地址
					if(S=='' || S=='$src'){
						alert('".$king->lang->get('portal/error/notimgsrc')."');
						return;
					}

					if(A=='' || A=='$alt') A=S;
					var s=S+t+A;
					var ss=\$('#$f').val();
					//如果S为空，则提示错误
					if(ss==''){
						\$('#$f').val(s);
					}else{
						//判断文件是否已经存在
						var ss_1=ss.split(t+t);
						var ss_2,src;
						for(i=0;i<ss_1.length;i++){
							ss_2=ss_1[i].split(t);
							src=ss_2[0];
							if(src==S){
								alert('".$king->lang->get('portal/error/retfilesrc')."');
								\$('#S$f').val('');
								return;
							}
						}
						\$('#$f').val(ss+t+t+s);
					}
					\$('#S$f').val('');
					\$('#A$f').val('');
					S$f();
				}
				//显示数据
				function S$f(){
					var t=String.fromCharCode(9);
					var ss=\$('#$f').val();
					if(ss==''){
						\$('#W$f').html('');
						return;
					}
					var s='';
					var ss_1=ss.split(t+t);
					var ss_2,alt,src;
					for(i=0;i<ss_1.length;i++){
						ss_2=ss_1[i].split(t);
						src=ss_2[0];
						alt=ss_2[1];

						s+='<span class=\"file\" title=\"'+alt+'\">';

						s+='<a href=\"javascript:;\" class=\"icon\" onClick=\"D$f('+i+')\" title=\"".$king->lang->get('system/common/del')."\">';
						s+=\$.kc_icon('j2','".$king->lang->get('system/common/del')."')+'</a>';

						s+='<a href=\"javascript:;\" onClick=\"E$f('+i+')\" class=\"title\" title=\"".$king->lang->get('system/common/edit')."\">';
						s+=alt;
						//s+='<img class=\"file\" src=\"'+(ss_2[0].match(/^[a-zA-Z]{3,10}:\/\/[^\s]+\$/) ? ss_2[0] : '../'+ss_2[0])+'\"/>';
						s+='</a>';

						s+='<a title=\"".$king->lang->get('system/common/down')."\" class=\"src\" href=\"'+(ss_2[0].match(/^[a-zA-Z]{3,10}:\/\/[^\s]+\$/) ? ss_2[0] : '../'+ss_2[0])+'\">';
						s+=\$.kc_icon('e3','".$king->lang->get('system/common/down')."');
						s+=src+'</a>';
						s+='</span>';
					}

					\$('#W$f').html(s);
				}
				//删除文件
				function D$f(num){
					var t=String.fromCharCode(9);
					var ss=\$('#$f').val();
					if(ss==''){
						\$('#W$f').html('');
						return;
					}
					var ss_1=ss.split(t+t);
					
					ss_1.splice(num,1);

					\$('#$f').val(ss_1.join(t+t));

					S$f();
					
				}
				//编辑图片
				function E$f(num){
					var t=String.fromCharCode(9);
					var ss=\$('#$f').val();
					if(ss=='') return;
					var ss_1=ss.split(t+t);

					var ss_num=ss_1.slice(num,num+1);
					var ss_2=ss_num[0].split(t);

					\$('#S$f').val(ss_2[0]);
					\$('#A$f')
						.val(ss_2[1])
						.width(250);

					\$('#B$f')
						.val('".$king->lang->get('system/common/up')."')
						.unbind('click')
						.click(function(){U$f(num)});
					if(!\$('#B$f').next('input').length){
						\$('#B$f').after('<input type=\"button\" value=\"".$king->lang->get('system/common/cancel')."\" onClick=\"C$f()\"/>');
					}
				}
				//提交编辑结果
				function U$f(num){
					var t=String.fromCharCode(9);
					var ss=\$('#$f').val();

					var S=\$('#S$f').val().replace(t,'');
					var A=\$('#A$f').val().replace(t,'');

					//判断是否填写图片地址
					if(S=='' || S=='$src'){
						alert('".$king->lang->get('portal/error/notfilesrc')."');
						return;
					}

					var ss_1=ss.split(t+t);
					ss_1.splice(num,1,S+t+A);

					\$('#$f').val(ss_1.join(t+t));

					\$('#S$f').val('');
					\$('#A$f').val('')
						.width(300);

					\$('#B$f').val('".$king->lang->get('system/common/add')."')
						.unbind('click')
						.click(function(){F$f()})
						.next('input').remove();

					S$f();

				}
				//取消按钮
				function C$f(){

					\$('#S$f').val('');
					\$('#A$f').val('')
						.width(300);

					\$('#B$f').val('".$king->lang->get('system/common/add')."')
						.unbind('click')
						.click(function(){F$f()})
						.next('input').remove();

				}
				S$f();
				\$('#B$f').click(function(){F$f()});
				</script>";
			/*
			$s='<input type="hidden" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'"/><br/>';
			$s.='<input type="hidden" id="'.$f.'_temp" name="'.$f.'_temp"/>';
			$s.='<table class="k_side" cellspacing="0"><tr><td id="k'.$f.'show" class="k_in filelist" style="width:'.$rs['nstylewidth'].'px;height:'.$rs['nstyleheight'].'px;">';
			$s.='Loading...';
			$s.='</td></tr></table>';
			$h=kc_f_brow($f.'_temp',$king->config('uppath').'/file/',1,1,$jsfun='k'.$f.'show();');

			$s.="<script>function k{$f}js(title,file,label){var s='<span class=\"file\" title=\"'+title+'\">";
			$s.="<a class=\"img k_ajax\" href=\"javascript:;\" rel=\"{CMD:\'filetitle\',file:\''+file+'\',label:\''+label+'\',VAL:\''+label+','+label+'_temp,listid\',METHOD:\'GET\'}\">";
			$s.="<img class=\"file\" src=\"../'+file+'\"/></a>";
			$s.="<em class=\"title\">";
			$s.="<a class=\"k_ajax\" href=\"javascript:;\" rel=\"{CMD:\'filesdel\',delfile:\''+file+'\',label:\''+label+'\',VAL:\''+label+','+label+'_temp,listid\',IS:1}\">";
			$s.="'+\$.kc_icon('j2')+'</a>'+title+'</em></span>';return s;};";

			$s.="function k{$f}show(){\$.kc_ajax('{URL:\'manage.content.php\',ID:\'k{$f}show\',CMD:\'filesload\',label:\'{$f}\',VAL:\'{$f}_temp,{$f},listid\'}')};k{$f}show();</script>";
			*/
			$h=kc_f_brow("S$f",$king->config('uppath').'/file/',1,1,"F$f()");
			$h.=$this->help($rs['kid'],$rs['khelp']);
		break;

		case 12://颜色框
			$c[]=array($f,0,7,7);
			$c[]=array($f,13);
//			$s='<table class="k_side" cellspacing="0"><tr><td><input class="k_in k_color" type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" '.(kc_validate($data[$f],13)?' style="background:'.$data[$f].'"':'').' maxlength="7" /></td>';
//			$s.='<td>'.kc_f_color($f).'</td></tr></table>';
			$s='<input class="k_in w50" type="text" id="'.$f.'" name="'.$f.'" value="'.htmlspecialchars($data[$f]).'" '.(kc_validate($data[$f],13)?' style="background:'.$data[$f].'"':'').' maxlength="7" />';
			$s.=kc_f_color($f);
			//"<a href=\"javascript:;\" class=\"k_color\" rel=\"{id:'{$f}'}\"><img src=\"../system/images/white.gif\" class=\"os a8\"/></a>";
		break;

		case 13://是/否
			$c[]=array($f,20,null,array('',1));
			$checked=$data[$f]?' checked="checked"':'';
			$s='<input'.$checked.' type="checkbox" id="'.$f.'" name="'.$f.'" value="1" />';
			$s.='<label for="'.$f.'">'.($group?$king->lang->get('system/common/selectyes'):htmlspecialchars($rs['ktitle'])).'</label>';
		break;

		case 14://日期
//			$c[]=array($f,9);
			$c[]=array($f,0,$rs['nsizemin'],$rs['nsizemax']);
			if($rs['nvalidate']!=0)
				$c[]=array($f,$rs['nvalidate']);

			$date=$data[$f]=='TODAY'?kc_formatdate(time(),"Y-m-d"):$data[$f];
			$s=kc_htm_input($f,$date,30,100);
			$s.="<a href=\"javascript:;\" class=\"k_calendar\" rel=\"{id:'{$f}'}\"><img src=\"../system/images/white.gif\" class=\"os k9\"/></a>";
			/*
			$h="<script>";//<script type=\"text/javascript\" src=\"../system/js/calendar.js\"></script>
			for($i=0;$i<7;$i++){
				$h.="k_lang['week{$i}']='".$king->lang->get('system/time/week'.$i)."';";
			}
			$h.="$.kc_calendar('{$f}')</script>";
			*/
		break;

		case 99:
			$kid=$rs['kid'];

			$isshow=($is==1)?"isadmin{$at}":"isuser{$at}";

			if(!$res2=$king->db->getRows("select * from %s_field where modelid={$info['modelid']} and {$isshow}=1 and kid1={$kid} order by norder,kid;"))//全部调用
				return;//若没有可见项，返回空值

			$s='';
			$c2=array();
			$h2='';//($s,$c,$h)
			foreach($res2 as $rs2){

				list($s2,$c2,$h2)=$this->formdecode($rs2,$data,$info,$is,$at,2);
				$s.='<span class="k_field">';
				if($rs2['istitle'])
					$s.='<label>'.addslashes($rs2['ktitle']).'</label>';
				$s.=$s2.'</span>';

				$c=array_merge($c,$c2);
				$h.=$h2;
			}

		break;


		default:$s=$rs['ktitle'].'='.$rs['ntype'];
	}

	if($group==1){
		if($is==1){//后台调用
			if(($rs['ntype']==0&&$f=='nattrib')||$rs['ntype']==99){//组输出,不需要标签
				return $king->htmForm($rs['ktitle'],$s,$c,'',$h);
			}else{
				return $king->htmForm($rs['ktitle'],$s,$c,substr($f,1),$h);
			}
		}else{
			return $king->htmForm($rs['ktitle'],$s,$c);
		}
	}else{//组调用
		if($is==1){
			return array($s,$c,$h);
		}else{
			return array($s,$c,'');
		}
	}

//	return $htmlForm;
}


/**
	输出路径[PAGE]
	@param array $info  列表信息
	@param int $kid     项目id
	@param string $kpath  路径
	@param int $is      1输出相对物理路径(文件生成),0输出完整URL路径，带site地址
	@param int $pid  页
	@return string
*/
public function pathPage($info,$kid,$kpath,$is=0,$pid=1){
//public function pathPage($listid,$kid,$pagepath,$pid,$is=0){
	global $king;

	$inst=$king->config('inst');
	$line=$king->config('rewriteline');
	$end=$king->config('rewriteend');
	$file=$king->config('file');
	$pidline=$king->config('pidline');

	$site=$this->infoSite($info['siteid']);
	$siteurl=$site['siteurl'];
	$listid=$info['listid'];

	switch((int)$info['npage']){
		case 0:
			if($info['npagenumber']==1){//如果每页显示数为1，则直接返回路径
				if(substr($kpath,-1,1)=='/'){//目录结构
					$path=$is ? $kpath.$file : $siteurl.$inst.$kpath;
				}else{//文件类型
					$path=$is ? $kpath : $siteurl.$inst.$kpath;
				}
			}else{
				$path=kc_formatPath($kpath,$pid,$is);
				$path=$is ? $path : $siteurl.$inst.$path;
			}
		break;

		case 1:
			if($info['npagenumber']==1){
				$path=$siteurl.$inst."index.php/page$line$listid$line$kid".($pid==1 ? '' : $line.$pid)."$end";
			}else{
				$path=$siteurl.$inst."index.php/page$line$listid$line$kid$line$pid$end";
			}
		break;

		case 2:
			if($info['npagenumber']==1){
				$path=$siteurl.$inst."page$line$listid$line$kid".($pid==1 ? '' : $line.$pid)."$end";
			}else{
				$path=$siteurl.$inst."page$line$listid$line$kid$line$pid$end";
			}
		break;

		case 3:
			$path="javascript:alert('".$king->lang->get('portal/error/stopmsg')."');";
			return $path;
		break;

	}

	return $path;

}
/**
	输出路径[LIST]
	@param array $info  列表信息
	@param int $is      1输出根相对路径,物理生成,0完整URL路径
	@param int $pid     第x页
	@return string
*/
public function pathList($info,$is=0,$pid=1){
	global $king;

	//初始化静态变量
	$inst=$king->config('inst');
	$line=$king->config('rewriteline');
	$end=$king->config('rewriteend');

	$site=$this->infoSite($info['siteid']);
	$siteurl=$site['siteurl'];
	$listid=$info['listid'];
	$modelid=$info['modelid'];

	if($is && (int)$info['nlist']!==0) return False;//输出静态路径并nlist不等于0的时候，返回 False

	//0静态1动态2伪静态3不显示4订单、留言类
	switch((int)$modelid){
	case 0://单页面
		switch((int)$info['nlist']){
		case 0:
			if($info['klistpath']==''){//根
				$path=$is ? False : $siteurl.$inst;
			}else{
				$path=kc_formatPath($info['klistpath'],1,1);
				if(!$is){
					$path=$siteurl.$inst.$path;
					//当输入值为/结尾的时候，也对$path进行二次处理
					if(substr($info['klistpath'],-1,1)=='/'){
						$path=substr($path,0,strlen($path)-strlen($king->config('file')));
					}
				}
//				$path=$is ? $path : $siteurl.$inst.$path;
			}
		break;

		case 1:
			$path=($info['klistpath']=='') ? $siteurl.$inst: $siteurl.$inst."index.php/list$line$listid$end";
		break;

		case 2:
			$path=($info['klistpath']=='') ? $siteurl.$inst: $siteurl.$inst."list$line$listid$end";
		break;

		case 3:
			$path="javascript:alert('".$king->lang->get('portal/error/stop')."');";
		break;
		}
	break;

	case -1://超级链接
		$path=$info['klistpath'];
	break;

	default://自定义模型

		switch((int)$info['nlist']){
		case 0:
			$path=kc_formatpath($info['klistpath'],$pid,$is);
			$path=$is? $path : $siteurl.$inst.$path;
		break;

		case 1:
			$path=$siteurl.$inst."index.php/list$line$listid$line$pid$end";
		break;

		case 2:
			$path=$siteurl.$inst."list$line$listid$line$pid$end";
		break;

		case 3:
			$path="javascript:alert('".$king->lang->get('portal/error/stopmsg')."');";
			return $path;
		break;

		case 4:
			$path=$siteurl.$inst."portal/index.php?action=edt&listid=$listid";
		break;
		}

	}

	return $path;
}
public function pathTag($tagname){
	global $king;
	$link=$king->config('inst');
	$link.=$king->config('rewritetag') ? "" : "index.php/";
	$link.="tag".$king->config('rewriteline').urlencode($tagname).$king->config('rewriteend');
	return $link;
}
/**

	更新nupdatePage和nupdateList

	@param int    $listid : 列表ID
	@param string $type   : 只能取值list或page

*/
public function lastUpdated($listid,$type=null){
	global $king;
	if($type=='page'||$type=='list'){
		$king->db->update('%s_list',array('nupdate'.$type=>time()),"listid=$listid");
	}else{
		$king->db->update('%s_list',array('nupdatepage'=>time(),'nupdatelist'=>time()),"listid=$listid");
	}
}
/**
	Model解压
	@param string $code
	@return bool
*/
public function unModelCode($code,$modelname,$modeltable){
	if(!$GLOBALS['ismethod']) return False;

	global $king;

	$modeltable=strtolower($modeltable);

	$array=unserialize(base64_decode(trim($code)));

//	kc_error($array['model'][0]);

	if(is_array($array) && isset($modelname{0}) && isset($modeltable{0})){//判断是否为数组
		if(is_array($array['model'][0])){//model
			$array_model=$array['model'][0];
			$array_model['modelname']=$modelname;
			$array_model['modeltable']=$modeltable;
			$array_model['norder']=$king->db->neworder('%s_model');

			$array_model=array_diff_key($array_model,array('modelid'=>'去掉modelid'));

			$modelid=$king->db->insert('%s_model',$array_model);
		}else{
			return False;
		}

		//创建对应的数据表
		$this->installmodeltable($modeltable);


		if($modelid && is_array($array['field'])){//有modelid、同时field也为数组
			$kids=array();//这个是用来临时存储新旧kid的，为的是倒子字段

			$s='<table>';

			foreach($array['field'] as $val){
				$val['modelid']=$modelid;

				if($val['kid1']!=0){//子字段的时候
					$val['kid1']=$kids[$val['kid1']];//从$kid1数组中获得新的kid
				}
				$kid=$val['kid'];//原始的kid

				$val=array_diff_key($val,array('kid'=>''));//删除kid
				$kids[$kid]=$king->db->insert('%s_field',$val);//获得新的kid,key为原始的kid

				//往modeltable数据库增加自定义字段
				if(in_array($val['ntype'],$this->array_varchar)){//varchar类型
					$king->db->query('alter table %s__'.$king->db->escape($modeltable).' add '.$king->db->escape($val['kfield']).' varchar('.$val['nsizemax'].') null;');
				}else{
					$king->db->query('alter table %s__'.$king->db->escape($modeltable).' add '.$king->db->escape($val['kfield']).' text null;');
				}

				$s.="<tr><th>{$val['ktitle']}</th><td>{king:".substr($val['kfield'],1)."/}</td></tr>";
			}

			$s.='</table>';

		}else{
			return False;
		}


		//(当没有默认内部模板的时候)创建对应的默认内部模板
		$path=$king->config('templatepath')."/inside/{$modeltable}[page]/".$king->config('templatedefault');
		if(!is_file(ROOT.$path)){
			kc_f_put_contents($path,$s);
		}

		$path=$king->config('templatepath')."/inside/{$modeltable}[list]/".$king->config('templatedefault');
		if(!is_file(ROOT.$path)){
			$s="{king:portal.{$modeltable} type=\"list\"}
					<p><a href=\"{king:path/}\">{king:title/}</a></p>
				{/king:portal.{$modeltable}}

				{king:pagelist/}";
			kc_f_put_contents($path,$s);
		}

		return True;
	}else{
		return False;
	}
}
/**
	Model编码
	@param int $modelid
	@return string
*/
public function enModelCode($modelid){
	global $king;

	if(!$resModel=$king->db->getRows("select * from %s_model where modelid={$modelid}"))
		kc_error($king->lang->get('system/error/param').kc_clew(__FILE__,__LINE__));

	if(!$resField=$king->db->getRows("select * from %s_field where modelid={$modelid} order by kid"))
		$resField=array();

	$array=array(
		'model'=>$resModel,
		'field'=>$resField,
	);

	return base64_encode(serialize($array));

}
/* ------>>> Info信息 <<<---------------------------- */

public function infoSite($siteid){
	global $king;

	$cachepath='portal/site/info';

	$array=$king->cache->get($cachepath);

	if(!$array){
		if(!$site=$king->db->getRows("select siteid,siteurl,sitename from %s_site"))
			$site=array();

		foreach($site as $rs){
			$array[$rs['siteid']]=$rs;
			/**
			array(
				'siteid'=>$rs['siteid'],
				'siteurl'=>$rs['siteurl'],
				'sitename'=>$rs['sitename'],
			);
			*/
		}
		$king->cache->put($cachepath,$array);
	}

	return $array[$siteid];


}
/**
	模型结构
	@param int $modelid : 模型ID
	@return array
*/
public function infoModel($modelid){
	global $king;

	$cachepath='portal/model/model'.$modelid;

/**/
	if($king->cache->get($cachepath)){
		return $king->cache->get($cachepath);
	}
/**/

	if(!($model=$king->db->getRows_one("select * from %s_model where modelid={$modelid}"))){
		return;
	}else{
		$array=array();
		foreach($model as $key => $val){
			if(!kc_validate($key,2))
			$array[$key]=$val;
		}
	}

	//预排一下，print_r来输出的时候视觉效果会好一些
	$array['field']['id']=array('kid','listid','kid1','userid');
	$array['field']['attrib']=array('ndate','ncomment','ndigg1','ndigg0','ndigg','nfavorite','nhit','nhitlate','nnumber','ncount');
	$array['field']['issearch']=array();
	$array['field']['text']=array();
	$array['field']['html']=array();
	$array['field']['islist']=array();//是否在列表页显示
	$array['field']['isadmin1']=array();//在保存字段的值的时候，需要isshow属性，这是最重要的应用
	$array['field']['isadmin2']=array();
	$array['field']['isuser1']=array();
	$array['field']['isuser2']=array();
	$array['field']['default']=array();//默认值
	$array['field']['offon']=array();//是否类型的字段
	$array['field']['image']=array();//图片类型，好抓图
	$array['field']['images']=array();//图片组类型，数组形式输出
	$array['field']['file']=array();//文件类型
	$array['field']['files']=array();//文件组组类型

	//默认选项？特别是多选等项目，严格限制提交值是很有必要的。


/**
	//$model['nattrib']
	$array_is=array('isshow','ishead','iscommend','isup','isfocus','ishot');
	foreach($array_is as $val)
		if($model[$val]) $array_nattrib[]=$val;
	$array['nattrib']=$array_nattrib;
*/

	if(!($res=$king->db->getRows("select kfield,ktitle,isadmin1,isadmin2,isuser1,isuser2,islist,kdefault,ntype,isrelate,issearch from %s_field where modelid={$modelid} order by norder asc")))
		return;
//kc_error('<pre>'.(print_r($res,1)));

	foreach($res as $rs){

		$kfield=$rs['kfield'];
		if($rs['ntype']==0){//系统字段
		/*
			if($rs['kfield']=='kcontent'){
				$array['field']['html']['kcontent']=$rs['ktitle'];
			}
			if($rs['kfield']=='ktitle'){

			}
			*/
			switch($kfield){
				case 'ktitle':
					$array['field']['issearch'][$kfield]=htmlspecialchars($rs['ktitle']);
					$array['field']['text'][$kfield]=htmlspecialchars($rs['ktitle']);
				break;

				case 'kcontent':
					$array['field']['html'][$kfield]=htmlspecialchars($rs['ktitle']);
				break;

				case 'nattrib':break;

				default:

					if(in_array($kfield,array('nprice','nweight','nshow','nhead','ncommend','nup','nfocus','nhot'))){
						$array['field']['html'][$kfield]=htmlspecialchars($rs['ktitle']);
					}else{
						$array['field']['text'][$kfield]=htmlspecialchars($rs['ktitle']);
					}

				if($kfield=='kimage')
					$array['field']['image'][$kfield]=htmlspecialchars($rs['ktitle']);
			}


		}else{//自定义字段

			$array['field']['default'][$kfield]=htmlspecialchars($rs['kdefault']);

			if($rs['islist']==1)
				$array['field']['islist'][$kfield]=htmlspecialchars($rs['ktitle']);

			if($rs['isrelate']==1)
				$array['field']['isrelate'][$kfield]=htmlspecialchars($rs['ktitle']);

			if($rs['issearch']==1)
				$array['field']['issearch'][$kfield]=htmlspecialchars($rs['ktitle']);

			if($rs['ntype']==3){//html编辑器
				$array['field']['html'][$kfield]=htmlspecialchars($rs['ktitle']);
			}else{
				$array['field']['text'][$kfield]=htmlspecialchars($rs['ktitle']);
			}

			switch((int)$rs['ntype']){
				case 13:
					$array['field']['offon'][$kfield]=htmlspecialchars($rs['ktitle']);
				break;
			
				case 8:
					$array['field']['image'][$kfield]=htmlentities($rs['ktitle']);
				break;
				case 9:
					$array['field']['images'][$kfield]=htmlentities($rs['ktitle']);
				break;
				case 10:
					$array['field']['file'][$kfield]=htmlentities($rs['ktitle']);
				break;
				case 11:
					$array['field']['files'][$kfield]=htmlentities($rs['ktitle']);
				break;
			
			}
		}

		if($rs['isadmin1']&&$rs['ntype']!=99&&$rs['kfield']!=='nattrib')
			$array['field']['isadmin1'][$kfield]=htmlspecialchars($rs['ktitle']);

		if($rs['isadmin2']&&$rs['ntype']!=99&&$rs['kfield']!=='nattrib')
			$array['field']['isadmin2'][$kfield]=htmlspecialchars($rs['ktitle']);

		if($rs['isuser1']&&$rs['ntype']!=99&&$rs['kfield']!=='nattrib')
			$array['field']['isuser1'][$kfield]=htmlspecialchars($rs['ktitle']);

		if($rs['isuser2']&&$rs['ntype']!=99&&$rs['kfield']!=='nattrib')
			$array['field']['isuser2'][$kfield]=htmlspecialchars($rs['ktitle']);

	}

	$king->cache->put($cachepath,$array);
	return $array;

}
/**
	分页列表信息
	@param int listid : 列表id
	@return array
*/
public function infoList($listid=null){
	global $king;

	if(!$listid)
		$listid=kc_get('listid',2,1);//必须的

	if($listid==0)
		return;

	$cachepath='portal/list/'.$listid;

	$array=$king->cache->get($cachepath);//缓存中的listInfo
	if(!$array){
		$array=array();
//kc_error($listid);
		if($list=$king->db->getRows_one("select * from %s_list where listid={$listid}")){
			foreach($list as $key => $val){
				if(!kc_validate($key,2))
					$array[$key]=$val;
			}
		}else{
			kc_error($king->lang->get('system/error/param').kc_clew(__FILE__,__LINE__,$king->lang->get('portal/msg/listname'))."<p>LISTID: $listid</p>");
		}

		$modelid=$list['modelid'];
		if($modelid>0){//非内置模型的时候
			$model=$this->infoModel($modelid);

			$resCount=$king->db->getRows_one("select count(*) AS ncount from %s__{$model['modeltable']} where listid=$listid and nshow=1 and kid1=0;");//显示的主题内容
			$resCountAll=$king->db->getRows_one("select count(*) AS ncount from %s__{$model['modeltable']} where listid=$listid and kid1=0;");//所有的主题内容


			if($list['ncount']!=$resCount['ncount']){//当前列表数量和实际的数字不同的时候，更新结构缓存
				$array['ncount']=$resCount['ncount'];
			}
			$array['ncountall']=$resCountAll['ncount'];

			$array['pcount']=($list['ncount']==0) ? 1:ceil($array['ncount']/$list['nlistnumber']);//列表，共pcount页，前台的

		}

		$isexist= $king->db->getRows_number('%s_list',"listid1=$listid")>0 ? 1 : 0;//是否存在子栏目
		//更新列表数据
		$array_list_up=array(
			'ncount'=>$array['ncount'],
			'ncountall'=>$array['ncountall'],
			'isexist'=>$isexist,
			);
		$king->db->update('%s_list',$array_list_up,"listid=$listid");

		$array['isexist']=$isexist;

		$site=$this->infoSite($list['siteid']);
		$array['klisttitle']=$list['ktitle'];

		//直接在这个列表信息获取函数里转换的话，无需再次进行转换
		$array_htmlspecialchars=array('ktitle','klisttitle','klistname','kkeywords','klistpath','kdescription','kimage','klanguage');//需要转换为htmlspecialchars的字段
		foreach($array_htmlspecialchars as $key => $val){
			$array[$val]=htmlspecialchars($array[$val]);
		}

		$king->cache->put($cachepath,$array);
	}
	return $array;
}
/**
	分页评论信息
	@param int listid	列表id
	@param int kid		文章id
	@return array
*/
public function infoComment($modelid=null,$kid=null){
	global $king;

	if(!$modelid)
		$modelid=kc_get('modelid',2,1);//必须的
	if($modelid==0)
		return;

	if(!$kid)
		$kid=intval(kc_get('kid',2,1));//必须的
	$cachepath='portal/comment/'.$modelid.'/'.$kid;
	$where='kid='.$kid;
	$array=$king->cache->get($cachepath);//缓存中的commentInfo
	if(!$array){
		$array=array();
//kc_error($listid);
		if($comment=$king->db->getRows_one("select * from %s_comment where $where and modelid={$modelid} order by cid desc;")){
			foreach($comment as $key => $val){
				if(!kc_validate($key,2))
					$array[$key]=$val;
			}
		}else{
			kc_error($king->lang->get('system/error/param').kc_clew(__FILE__,__LINE__,$king->lang->get('portal/msg/listname'))."<p>LISTID: $listid</p>select * from %s_comment where $where;");
		}
		
		$model=$this->infoModel($modelid);//模板属性
		$array['ktemplatecomment']=$model['ktemplatecomment'];//评论模板
		$array['ncommentnumber']=$model['ncommentnumber'];//评论分页条数
		if(!$array['ncommentnumber'])$array['ncommentnumber']=30;
		$array['modeltable']=$model['modeltable'];//表名
		
		if(!$id=$king->db->getRows_one("select ktitle,ncomment from %s__{$array['modeltable']} where $where;"))
			return False;
		$array['ktitle']=$id['ktitle'];//文章标题
		
		$resCount=$king->db->getRows_one("select count(*) AS ncount from %s_comment where $where and modelid={$modelid} and isshow=1;");//显示的评论内容
		$resCountAll=$king->db->getRows_one("select count(*) AS ncount from %s_comment where $where and modelid={$modelid};");//所有的评论内容
		$array['ncount']=$resCount['ncount'];
		if($id['ncomment']!=$resCount['ncount']){//当前评论数量和实际的数字不同的时候，更新结构缓存
			//更新文章的评论计数
			$king->db->update('%s__'.$array['modeltable'],array('ncomment',$array['ncount']),'$where');
		}
		$array['ncountall']=$resCountAll['ncount'];
		
		$array['pcount']=($array['ncount']==0) ? 1:ceil($array['ncount']/$array['ncommentnumber']);//列表，共pcount页，前台的

		//直接在这个评论列表信息获取函数里转换的话，无需再次进行转换
		$array_htmlspecialchars=array('ktitle','kcontent','username');//需要转换为htmlspecialchars的字段
		foreach($array_htmlspecialchars as $key => $val){
			$array[$val]=htmlspecialchars($array[$val]);
		}

		$king->cache->put($cachepath,$array);
	}
	return $array;
}
/**
	获得id对应的信息,用xml文档进行缓存
	@param int $listid 列表ID
	@param int $kid    内容id
	@return array
*/
public function infoID($listid,$kid){
/*

修改方案
用XML文件进行数据缓存

*/
	global $king;

	$info=$this->infoList($listid);

	$xmlpath=$king->config('xmlpath','portal').'/portal/'.$info['modelid'].'/'.wordwrap($kid,1,'/',1).'.xml';

	$xml=new KC_XML_class;
	if(is_file(ROOT.$xmlpath)){
		$xml->load_file($xmlpath);
		$id=$xml->xml2array();
	}else{
		$model=$this->infoModel($info['modelid']);
		if(!$id=$king->db->getRows_one("select * from %s__{$model['modeltable']} where kid={$kid} and listid={$listid}"))
			return False;

		if($id['kid1']==0){//根文章才记录ncount值
			if(!$res=$king->db->getRows("select kid from %s__{$model['modeltable']} where listid={$listid} and nshow=1 and (kid={$kid} or kid1={$kid})"))
				$res=array();
			$count=count($res);

			if($id['ncount']!=$count){
				$id['ncount']=$count;
				$king->db->update("%s__{$model['modeltable']}",array('ncount'=>$count),"kid={$kid}");
			}
			//获得ids列表
			$ids=array();
			foreach($res as $rs){
				$ids[]=$rs['kid'];
			}
			$ids=array_diff($ids,array($kid));
			//增加subkid列表
			$id['subkid']=implode(',',$ids);

		}

		$array_htmlspecialchars=$model['field']['text'];//需要转换为htmlspecialchars的字段

		foreach($array_htmlspecialchars as $key => $val){
			$id[$key]=htmlspecialchars(kc_val($id,$key));
		}

		$str=$xml->array2xml($id);
		kc_f_put_contents($xmlpath,$str);

	}
	return $id;

}
public function infoTag($tagname){
	global $king;

	$md5path=preg_replace('/(\w{2})(\w+)/',"\$1/\$2",md5($tagname));
	$xmlpath=$king->config('xmlpath','portal').'/portal/tag/'.$md5path.'.xml';

	$xml=new KC_XML_class;
	if(is_file(ROOT.$xmlpath)){
		$xml->load_file($xmlpath);
		$tag=$xml->xml2array();
	}else{
		if(!$res=$king->db->getRows_one("select * from %s_tag where ktag='".$king->db->escape($tagname)."'"))
			$this->error(htmlspecialchars($tagname),$king->lang->get('portal/error/nottag'));

		$tag=array();
		foreach($res as $key => $rs){
				$tag[$key]=htmlspecialchars($rs);
		}

		$str=$xml->array2xml($tag);
		kc_f_put_contents($xmlpath,$str);

	}
	return $tag;
}

/* ------>>> GET部分 <<<----------------------------- */
/**
	返回不同场合下要用的字段列表
	@param string $type 返回类型 field,site,id,else
	@return array
*/
public function getField($type1,$type2){

	$array=array(
		'list'=>array(//列表
			'field'=>array('ktitle','klisttitle','klistname','kkeywords','klistpath','kdescription','kimage','kcontent','klanguage'),//字段
			'site'=>array('siteurl','sitename'),
			'id'=>array('listid','listid1','siteid','modelid'),
			'else'=>array('ncount','nlistnumber'),
		),
	);
	return $array[$type1][$type2];
}
/**
	返回首页域名对应的listid值
	@param  int $siteid
	@return int listid
*/
public function getSiteHome($siteid){
	global $king;

	$cachepath='portal/site/home';

	$array=$king->cache->get($cachepath);
	if(!$array){
		if(!$res=$king->db->getRows("select siteid,listid from %s_list where klistpath='' and modelid=0;"))
			$res=array();

		$array=array();
		foreach($res as $rs){
			$array[$rs['siteid']]=$rs['listid'];
		}

		$king->cache->put($cachepath,$array);
	}
	if($array){
		if(isset($array[$siteid])){
			$listid=$array[$siteid];
		}else{
			kc_error($king->lang->get('portal/error/nothome'));
		}
	}else{
		kc_error($king->lang->get('portal/error/nothome'));
	}
	return $listid;
}
/**
	返回域名当前网址对应的SiteID
	@return int
*/
public function getSiteid(){
	global $king;
	$url=strtolower($_SERVER['SERVER_NAME']);

	$cachepath='portal/site/id';

	$array=$king->cache->get($cachepath);

	if(!$array){
		$res=$king->db->getRows("select siteid,siteurl from %s_site;");
		$array=array();
		foreach($res as $rs){
			$pUrl=parse_url($rs['siteurl']);
			if(isset($pUrl['host'])){//如果符合http://bbs.kingcms.com这种格式，不符合的时候为空
				$array[strtolower($pUrl['host'])]=$rs['siteid'];
			}else{
				$array['']=$rs['siteid'];
			}
		}
		$king->cache->put($cachepath,$array);

	}

	if(isset($array[$url])){
		$siteid=$array[$url];
	}else{
		if(isset($array[''])){//若有空值，则返回空值对应的值
			$siteid=$array[''];
		}else{
			$this->error($king->lang->get('system/common/error'),$king->lang->get('portal/error/noturl'));
		}
	}

	return $siteid;
}
/**
	返回所有的listid列表
	@return array
*/
public function getListids(){
	global $king;
	$cachepath='portal/list/id';
	$array=$king->cache->get($cachepath);

	if(!$array){
		$array=array();
		if(!$res=$king->db->getRows("select listid from %s_list;"))
			$res=array();
		foreach($res as $val){
			$array[]=$val['listid'];
		}
		$king->cache->put($cachepath,$array);
	}
	return $array;

}
/**
	获得栏目管理员列表
	@param int $listid
	@param bool $is 是否为递归调用，默认不是
	@param array $array 管理员列表
	@return array
*/
public function getListEditor($listid,$is=False,$array=array()){
	global $king;

	if(!$is){
		$cachepath='portal/listeditor/'.$listid;
		if($array=$king->cache->get($cachepath,1)){
			return $array;
		}
	}

	if($res=$king->db->getRows("select userid,issub from %s_list_editor where listid=$listid")){
		foreach($res as $rs){
			if(!$is || ($rs['issub']==1 && $is)){
				$array[]=$rs['userid'];
			}
		}
	}
	$info=$this->infoList($listid);
	$listid1=$info['listid1'];
	if($listid1!=0){
		$array=$this->getListEditor($listid1,True,$array);
	}

	if(!$is){
		$king->cache->put($cachepath,$array);
	}
	return $array;
}
/**
		返回模型数据库表名称，不带前缀  modelid=>modeltable
		@return array
*/
public function getModelTables(){
	global $king;
	$cachepath='portal/model/table';
	$array=$king->cache->get($cachepath,1);
	if(!$array){
		$array=array();

		if(!$res=$king->db->getRows("select modelid,modeltable from %s_model;"))
			$res=array();

		foreach($res as $rs){
			$array[$rs['modelid']]=strtolower($rs['modeltable']);
		}
		$king->cache->put($cachepath,$array);
	}
	return $array;
}
/**
		返回模型名称  modelid=>modelname
		@return array
*/
public function getModelNames(){
	global $king;
	$cachepath='portal/model/name';
	$array=$king->cache->get($cachepath);
	if(!$array){
		$array=array(
			0=>$king->lang->get('portal/label/onepage'),
			-1=>$king->lang->get('portal/label/hyperlink'),
		);
		if(!$res=$king->db->getRows("select modelid,modelname from %s_model;"))
			$res=array();

		foreach($res as $rs){
			$array[$rs['modelid']]=strtolower($rs['modelname']);
		}
		$king->cache->put($cachepath,$array);
	}
	return $array;
}
/*
返回所有的物流公司信息
*/
public function getExpress(){
	global $king;

	$cachepath='portal/express';

	$array=$king->cache->get($cachepath);

	if(!$array){
		if(!$res=$king->db->getRows("select eid,kname,nsprice,niprice,kaddress from %s_express"))
			$res=array();

		foreach($res as $rs){
			$array[$rs['eid']]=$rs;
		}
		$king->cache->put($cachepath,$array);
	}

	return $array;

}
/**

	从关键字列表中返回所包含的关键字 或 补充&更新系统关键字组

	@param string $key   已有的关键字，如果有值，则读取系统关键字组进行更新
	@param string $str   如果$key为空值，则根据$str返回关键字

*/
public function getKey($str,$key=''){
	global $king;

	$keywords=$king->config('keywords','portal');


	if(isset($key{0})){//若有关键字列表，则进行更新操作

		$key_array=preg_split("/[\s,]+/",$key);
		if(isset($keywords{0})){//更新关键字组，并返回$key
			$keywords_array=explode('|',$keywords);
			foreach($key_array as $val){
				$keywords_array[]=$val;
			}
			$new_array=array_unique($keywords_array);//删除重复值
			$new_array=array_diff($new_array,array(''));//删掉空格
			$s=implode('|',$new_array);
			$king->db->update('%s_system',array('kvalue'=>$s),'kname=\'keywords\'');
		}else{//更新关键字组
			$s=implode('|',$key_array);
//			$s=str_replace(',','|',$key);
			$king->db->update('%s_system',array('kvalue'=>$s),'kname=\'keywords\'');
		}
		$king->cache->del('system/config/portal');
		return implode(',',$key_array);
	}else{
		if(isset($keywords{0})){
			$array=explode('|',$keywords);
			$arr=array();

			foreach($array as $val){
				$num=stripos($str,$val);

				if(kc_validate($num,2))
					$arr[]=$val;
			}

			array_unique($arr);//删除重复值

			$arr=array_diff($arr,array(null));//去掉空值

			return implode(',',$arr);//输出内容
		}
	}
}
/**

	返回Tag列表 或 添加Tag到列表中

	@param string $key   已有的Tag，如果有值，则对%s_tag进行更新
	@param string $str   如果$key为空，则根据$str来获得Tag

*/
public function getTag($str,$key=''){
	global $king;

	if($key!=''){//若有关键字列表，则进行更新操作
		$array_key=preg_split("/[\s,]+/",$key);
		foreach($array_key as $val){
			if(!$_res=$king->db->getRows_one("select kid from %s_tag where ktag='".$king->db->escape($val)."'")){
				$array=array(
					'ktag'=>$val,
					'kcolor'=>'#000000',
					'ktemplate1'=>$king->config('templatepath')."/".$king->config('templatedefault'),
					'ktemplate2'=>$king->config('templatepath')."/inside/tag/".$king->config('templatedefault'),//template/inside/tag/default.htm
					'norder'=>$king->db->neworder('%s_tag'),
				);
				$king->db->insert('%s_tag',$array);
			};
		}
		return implode(',',$array_key);
	}else{
		if(!$res=$king->db->getRows("select ktag from %s_tag limit 200;"))
			$res=array();

		$arr=array();

		foreach($res as $rs){
			$num=stripos($str,$rs['ktag']);
			if(kc_validate($num,2))
				$arr[]=$rs['ktag'];
		}
		array_unique($arr);//删除重复
		$arr=array_diff($arr,array(null));
		return implode(',',$arr);
	}
}
/**
	tab转换为数组类型，用在标签解析
	@param string $s    文本
	@param string $type 类型
		images
		files
	@return
*/
private function tab2array($s,$type){
	if(empty($s)) return false;

	global $king;

	$_array=explode("\t\t",$s);
	$title='';
	$value='';
	$array=array();

	foreach($_array as $key => $val){
		list($value,$title)=kc_explode("\t",$val,2);
		switch($type){
			case 'images':
				$array[]=array('title'=>$title,'image'=>$value);
			break;
		
			case 'files':
				$array[]=array('title'=>$title,'file'=>(kc_validate($value,6) ? $value : $king->config('inst').$value));
			break;
		
		}
	}

	return $array;
}
/* ------>>> 安装部分 <<<---------------------------- */
 //安装数据库 modeltable
//installmodeltable
public function installmodeltable($modeltable){
	global $king;

	$modeltable=strtolower($modeltable);

	//__[modeltable]
	$sql='kid1 INT NOT NULL DEFAULT 0,
	ncount INT NOT NULL DEFAULT 1,
	listid INT NOT NULL DEFAULT 0,
	ktitle VARCHAR(100) NULL,
	ksubtitle VARCHAR(20) NULL,
	nsublength TINYINT(2) NOT NULL DEFAULT 0,
	norder INT NOT NULL DEFAULT 0,
	isstar TINYINT(1) NOT NULL DEFAULT 0,
	ndate INT(10) NOT NULL DEFAULT 0,
	nlastdate INT(10) NOT NULL DEFAULT 0,
	kkeywords VARCHAR(100) NULL,
	ktag VARCHAR(100) NULL,
	kdescription VARCHAR(255) NULL,
	kimage VARCHAR(255) NULL,
	kcontent TEXT NULL,
	kpath VARCHAR(255) UNIQUE NOT NULL,
	nshow TINYINT(1) NOT NULL DEFAULT 1,
	nhead TINYINT(1) NOT NULL DEFAULT 0,
	ncommend TINYINT(1) NOT NULL DEFAULT 0,
	nup TINYINT(1) NOT NULL DEFAULT 0,
	nfocus TINYINT(1) NOT NULL DEFAULT 0,
	nhot TINYINT(1) NOT NULL DEFAULT 0,
	nprice REAL NOT NULL DEFAULT 0,
	nweight INT NOT NULL DEFAULT 0,
	nnumber INT(10) NOT NULL DEFAULT 0,
	nbuy INT(10) NOT NULL DEFAULT 0,
	ncomment INT NOT NULL DEFAULT 0,
	krelate VARCHAR(255) NULL,
	ndigg1 INT NOT NULL DEFAULT 0,
	ndigg0 INT NOT NULL DEFAULT 0,
	ndigg INT NOT NULL DEFAULT 1,
	nfavorite INT NOT NULL DEFAULT 0,
	nhit INT NOT NULL DEFAULT 0,
	nhitlate INT NOT NULL DEFAULT 0,
	userid INT NOT NULL DEFAULT 0,
	ulock TINYINT(1) NOT NULL DEFAULT 0,
	adminid INT NOT NULL DEFAULT 0,
	isok TINYINT(1) NOT NULL DEFAULT 0,
	nip INT(10) NOT NULL DEFAULT 0,
	aid INT NOT NULL DEFAULT 0,
	INDEX(kid1),
	INDEX(aid),
	INDEX(nshow),
	INDEX(nhead),
	INDEX(nfocus),
	INDEX(nhot),
	INDEX(userid),
	INDEX(adminid),
	INDEX(ndigg),
	INDEX(listid)';

	$king->db->createTable("%s__{$modeltable}",$sql,'kid');






	//创建默认模板
	$path=$king->config('templatepath')."/inside/{$modeltable}[page]/".$king->config('templatedefault');
	if(!is_file(ROOT.$path)){
		$s = '<p><strong>主题</strong>{king:title/}</p>
				<p><strong>副标题</strong>{king:subtitle/}</p>
				<p><strong>缩略图</strong>{king:image/}</p>
				<p><strong>内容</strong>{king:content/}</p>
				<p><strong>META关键字</strong>{king:keywords/}</p>
				<p><strong>TAG标签</strong>{king:tag/}</p>
				<p><strong>META简述</strong>{king:description/}</p>
				<p><strong>路径</strong>{king:path/}</p>
				<p><strong>相关内容</strong>{king:relate/}</p>
				<p><strong>优惠价</strong>{king:price/}</p>
				<p><strong>数量</strong>{king:number/}</p>
				<p><strong>重量</strong>{king:weight/}</p>
				<p><strong>显示</strong>{king:show/}</p>
				<p><strong>头条</strong>{king:head/}</p>
				<p><strong>推荐</strong>{king:commend/}</p>
				<p><strong>置顶</strong>{king:up/}</p>
				<p><strong>焦点</strong>{king:focus/}</p>
				<p><strong>热卖</strong>{king:hot/}</p>
				<p><strong>市场价</strong>{king:_Market/}</p>
				<p><strong>产品型号</strong>{king:_Serial/}</p>';
		kc_f_put_contents($path,$s);
	}

	$path=$king->config('templatepath')."/inside/{$modeltable}[list]/".$king->config('templatedefault');
	if(!is_file(ROOT.$path)){
		$s="{king:portal.{$modeltable} type=\"list\"}
				<p><a href=\"{king:path/}\">{king:title/}</a></p>
			{/king:portal.{$modeltable}}
			{king:pagelist/}";
		kc_f_put_contents($path,$s);
	}



}
 //安装数据库
//install
public function install(){
	global $king;



	//_site
	$sql='sitename char(100) not null,
	siteurl char(100) null';

	$king->db->createTable('%s_site',$sql,'siteid');

	if(!$king->db->getRows_one('SELECT * FROM %s_site')){
		$array=array(
			'sitename'=>$king->lang->get('system/common/default'),
			'siteurl'=>'',
		);
		$newsiteid=$king->db->insert('%s_site',$array);
	}

	//_list
	$sql='listid1 int not null default 0,
	modelid int not null default 0,
	siteid int not null default 1,
	norder int not null default 0,
	ncount int not null default 0,
	ncountall int not null default 0,
	ktitle char(100) not null,
	klistname char(100) not null,
	kkeywords char(100) null,
	kdescription char(255) null,
	kimage char(255) null,
	isblank tinyint(1) not null default 0,

	iscontent tinyint(1) not null default 0,
	kcontent text null,

	klistpath char(255) null,
	ktemplatelist1 char(255) null,
	ktemplatelist2 char(255) null,
	nlistnumber tinyint(3) not null default 20,

	kpathmode char(255) null,
	ktemplatepage1 char(255) null,
	ktemplatepage2 char(255) null,
	npagenumber tinyint(3) not null default 1,


	ispublish1 tinyint(1) not null default 0,
	ispublish2 tinyint(1) not null default 0,
	norder1 int not null default 0,
	norder3 int not null default 0,
	norder4 int not null default 0,
	norder5 int not null default 0,
	nupdatelist int(10) not null default 0,
	nupdatepage int(10) not null default 0,
	isexist tinyint(1) not null default 0,

	nlist tinyint(1) not null default 0,
	npage tinyint(1) not null default 0,
	gid int not null default 0,

	ismenu1 tinyint(1) not null default 0,
	ismenu2 tinyint(1) not null default 0,
	ismenu3 tinyint(1) not null default 0,
	ismenu4 tinyint(1) not null default 0,
	ismenu5 tinyint(1) not null default 0,
	ismap tinyint(1) not null default 0,
	klanguage char(30) null,

	INDEX(listid1),
	INDEX(modelid)';

	$king->db->createTable('%s_list',$sql,'listid');

	if(!$king->db->getRows_one('SELECT * FROM %s_list')){
		$_array=array(
			'norder'=>1,
			'norder1'=>1,
			'norder3'=>1,
			'norder4'=>1,
			'norder5'=>1,
			'ktitle'=>'欢迎使用KingCMS内容管理系统！',
			'klistname'=>'HOME',
			'iscontent'=>1,
			'kcontent'=>'<p>感谢您选择KingCMS内容管理系统！</p>'.NL.'<p>我们一如既往的专注于开发小巧、灵活、自由的内容管理系统。</p>'.NL.NL.'<p>上传所有文件到空间，设置目录和文件的权限(*nic系统设置为0777)。</p>',

			'klanguage'=>'zh-cn',
			'ktemplatelist1'=>'template/home.htm',
			'ismenu1'=>1,
			'ismap'=>1,
			'klistpath'=>'',
			'ismenu3'=>1,
			'siteid'=>$newsiteid,
		);
		$king->db->insert('%s_list',$_array);
	}

	// _List_editor
	$sql='listid int not null default 0,
	userid int not null default 0,
	issub tinyint(1) not null default 0,
	INDEX(listid)';

	$king->db->createTable('%s_list_editor',$sql,'kid');

	// _model
	$sql='modelname char(50) not null,
	modeltable char(50) not null,
	norder int not null default 0,
	klanguage char(30) null,
	issearch tinyint(1) not null default 0,
	klistorder char(255) null,
	kpageorder char(255) null,
	nlocktime int not null default 0,
	nshowtime int not null default 0,

	ispublish1 tinyint(1) not null default 0,
	ispublish2 tinyint(1) not null default 0,
	nlistnumber int not null default 20,
	npagenumber int not null default 1,

	ktemplatepublish char(255) null,
	ktemplatesearch char(255) null,
	isid tinyint(1) not null default 0';

	$king->db->createTable('%s_model',$sql,'modelid');

	// _field
	$sql='modelid int not null default 0,
	kid1 int not null default 0,
	istitle tinyint(1) not null default 0,
	norder int not null default 0,
	ktitle char(100) not null,
	kfield char(30) not null,
	ntype tinyint(2) not null default 0,
	issearch tinyint(1) not null default 0,
	nvalidate tinyint(2) not null default 0,
	nsizemin int not null default 0,
	nsizemax int not null default 0,
	kdefault char(255) null,
	koption text null,
	nstylewidth int not null default 0,
	nstyleheight int not null default 0,
	isadmin1 tinyint(1) not null default 1,
	isadmin2 tinyint(1) not null default 1,
	isuser1 tinyint(1) not null default 1,
	isuser2 tinyint(1) not null default 1,
	islist tinyint(1) not null default 0,
	isrelate tinyint(1) not null default 0,
	khelp text null,
	nupfile tinyint(1) not null default 0,
	INDEX(modelid),
	INDEX(ntype),
	INDEX(isadmin1),
	INDEX(isadmin2),
	INDEX(isuser1),
	INDEX(isuser2)';

	$king->db->createTable('%s_field',$sql,'kid');

	// _orders
	$sql='ono char(16) not null,
	nstatus tinyint(2) not null default 2,
	kname varchar(30) not null,
	userid int not null default 0,
	kcontent text null,
	nnumber int not null default 0,
	nip int(10) not null default 0,
	ndate int(10) not null default 0,
	npaydate int(10) not null default 0,
	nsenddate int(10) not null default 0,
	eid int not null default 0,
	expressnumber char(30) null,

	realname char(30) not null,
	useraddress char(250) null,
	userpost char(10) null,
	usertel char(30) null,
	usermail char(32) null,

	kremark text null,
	kfeedback char(255) null,
	ntotal real not null default 0,
	nexpress real not null default 0,
	nweight int not null default 0,

	paymethod varchar(32) null,
	tid varchar(32) null,
	buyer_id varchar(65) null,
	seller varchar(65) null,

	INDEX(userid)';

	$king->db->createTable('%s_orders',$sql,'oid');

	// _comment
	$sql='kid int not null default 0,
	modelid int not null default 0,
	username char(12) null,
	kcontent text null,
	nip int(10) not null default 0,
	ndate int(10) not null default 0,
	isshow tinyint(1) not null default 0,
	INDEX(kid),
	INDEX(modelid),
	INDEX(isshow),
	INDEX(ndate),
	INDEX(nip)';

	$king->db->createTable('%s_comment',$sql,'cid');

	// _user
	$sql='username char(15) UNIQUE not null,
	gid int not null default 0,
	uid int not null default 0,
	userpass char(32) not null,
	usermail char(32) not null,
	userask char(30) null,
	useranswer char(16) null,
	userhead char(255) null,
	userpoint int not null default 0,
	regip int(10) not null default 0,
	regdate int(10) not null default 0,
	lastloginip int(10) not null default 0,
	isdelete tinyint(1) not null default 0,
	islock tinyint(1) not null default 0,
	lastlogindate int(10) not null default 0,
	ksalt char(6) null,
	nickname varchar(15) null,

	realname char(30) null,
	usertel char(30) null,
	useraddress char(250) null,
	userpost char(10) null,

	kremark text null,
	INDEX(islock),
	INDEX(gid),
	INDEX(isdelete)';

	$king->db->createTable('%s_user',$sql,'userid');

	// _usergroup
	$sql='kname char(30) not null,
	norder int not null default 0,
	kaccess text null,
	kremark varchar(255) null,
	kmenu text null';

	$king->db->createTable('%s_usergroup',$sql,'gid');

	// _express
	$sql='kname char(50),
	nsprice int not null default 0,
	niprice int not null default 0,
	kremark text null,
	norder int not null default 0,
	isdefault tinyint(1) not null default 0,
	kaddress char(255)';

	$king->db->createTable('%s_express',$sql,'eid');

	if(!$king->db->getRows_one('SELECT * FROM %s_express')){
		$array=array(
			array('kname'=>'EMS','nsprice'=>20,'niprice'=>20,'kaddress'=>'http://www.ems.com.cn/qcgzOutQueryAction.do?reqCode=gotoSearch','norder'=>1,'isdefault'=>1),
			array('kname'=>'平邮','nsprice'=>10,'niprice'=>5,'kaddress'=>'http://intmail.183.com.cn/','norder'=>0),
		);
		foreach($array as $val){
			$king->db->insert('%s_express',$val);
		}
	}

	// _tag
	$sql='ktag char(100) UNIQUE not null,
	kimage char(255) null,
	kkeywords char(120) null,
	kdescription char(255) null,
	kcolor char(7) not null,
	nsize tinyint(2) not null default 12,
	isbold tinyint(1) not null default 0,
	nhit int not null default 0,
	nhitlate int not null default 0,
	iscommend tinyint(1) not null default 0,
	norder int not null default 0,
	ktemplate1 char(255) null,
	ktemplate2 char(255) null,
	INDEX(iscommend)';

	$king->db->createTable('%s_tag',$sql,'kid');
/*
	// _portal_log
	$sql.='kids char(255) null,
	userid int not null default 0,
	modelid int not null default 0';
*/
	$king->db->createTable('%s_portal_log',$sql,'kid');

	// _Favorite
	$sql='kid int not null default 0,
	userid int not null default 0,
	listid int not null default 0,
	INDEX(kid),
	INDEX(userid)';

	$king->db->createTable('%s_favorite',$sql,'fid');

	if(!$king->db->getRows_one('SELECT * FROM %s_model')){

		for($i=0;$i<5;$i++){//加入5个无效值并删除
			$array=array(
				'modeltable'=>$i,
				'modelname'=>$i
			);
			$king->db->insert('%s_model',$array);
		}
		$king->db->query("delete from %s_model");

		if(is_file(ROOT.'portal/_model.inc.php')){//先判断文件是否存在，如果不存在，则不初始化模型
			$array=require_once ROOT.'portal/_model.inc.php';
			foreach($array as $val){
				$this->unModelCode($val['code'],$val['name'],$val['table']);
			}
		}
	}

	if(!$king->db->getRows_one('SELECT * FROM %s_system where kmodule=\'portal\'')){
		$license='<p>用户单独承担传输内容的责任。</p>
<p>用户必须遵循：</p>
<p>1)使用网站服务不作非法用途。</p>
<p>2)不干扰或混乱网络服务。</p>
<p>3)不发表任何与政治相关的信息。</p>
<p>4)遵守所有使用网站服务的网络协议、规定、程序和惯例。</p>
<p>5)不得利用本站危害国家安全、泄露国家秘密，不得侵犯国家社会集体的和公民的合法权益。</p>
<p>6)不得利用本站制作、复制和传播下列信息：<br/>
1、煽动抗拒、破坏宪法和法律、行政法规实施的；<br/>
2、煽动颠覆国家政权，推翻社会主义制度的；<br/>
3、煽动分裂国家、破坏国家统一的；<br/>
4、煽动民族仇恨、民族歧视，破坏民族团结的；<br/>
5、捏造或者歪曲事实，散布谣言，扰乱社会秩序的；<br/>
6、宣扬封建迷信、淫秽、色情、赌博、暴力、凶杀、恐怖、教唆犯罪的；<br/>
7、公然侮辱他人或者捏造事实诽谤他人的，或者进行其他恶意攻击的；<br/>
8、损害国家机关信誉的；<br/>
9、其他违反宪法和法律行政法规的；<br/>
10、进行商业广告行为的。</p>
	';
		$transfer='
<h5>工商银行</h5>
<p>开户行：工商银行珲春分行</p>
<p>开户名：申青松</p>
<p>卡&nbsp; 号：<font face="Arial">9558<font color="#ff0000">8008</font>0811</font><span style="color: #ff0000"><font face="Arial">2242322</font></span></p>

<h5>交通银行</h5>
<p>开户名：申青松</p>
<p>卡&nbsp; 号：<span style="color: #ff0000"><font face="Arial">622260</font></span><font face="Arial">332<span style="color: #ff0000">0000</span>855541</font></p>

<h5>中国银行</h5>
<p>开户行：中国银行珲春分行</p>
<p>开户名：申青松</p>
<p>卡&nbsp; 号：<font color="#ff0000">4563</font>5106<font color="#ff0000">0001</font>3017<font color="#ff0000">341</font></p>

<h5>建议您</h5>
<p>如果您是在工行或中行进行了汇款操作，请把汇款单扫描或用数码相机拍照后发给我们，以便确认；若汇款额为200元，建议您多汇几分钱，如200.02等，以区分其他汇款人。这个不仅方便我们迅速进行确认，同时也保护了您的利益。</p>
';
		$i=1;

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'portal','kpath'=>'phrase'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'keywords','kmodule'=>'portal',
				'kvalue'=>'中国|吉林|延边|珲春|内容管理系统|KingCMS',
				'ntype'=>2,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>999999,
				'nstylewidth'=>600,
				'nstyleheight'=>300,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'blackcontent',
				'kmodule'=>'portal',
				'kvalue'=>'傻逼|宋祖德|周杰伦|臭装逼|范跑跑|没家教|陈冠希|大色狼',
				'ntype'=>2,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>999999,
				'nstylewidth'=>600,
				'nstyleheight'=>200,
				'khelp'=>'',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){
			$king->db->insert('%s_system',$val);
		}

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'portal','kpath'=>'xml'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'xmlpath',
				'kmodule'=>'portal',
				'kvalue'=>'_XML',
				'ntype'=>1,
				'nvalidate'=>4,
				'nsizemin'=>1,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>100,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'rss',
				'kmodule'=>'portal',
				'kvalue'=>'50',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>3,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'atom',
				'kmodule'=>'portal',
				'kvalue'=>'50',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>3,
				'koption'=>'',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'help/atom',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){
			$king->db->insert('%s_system',$val);
		}

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'portal','kpath'=>'orders'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'isuserbuy',
				'kmodule'=>'portal',
				'kvalue'=>'1',
				'ntype'=>4,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Yes'.NL.'0|No',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'templateorders',
				'kmodule'=>'portal',
				'kvalue'=>$king->config('templatepath').'/'.$king->config('templatedefault'),
				'ntype'=>13,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'transfer',
				'kmodule'=>'portal',
				'kvalue'=>$transfer,
				'ntype'=>3,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>999999,
				'nstylewidth'=>800,
				'nstyleheight'=>250,
				'khelp'=>'',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){
			$king->db->insert('%s_system',$val);
		}

/*
		//财付通设置
		//需要的表单有：
		////财付通帐号
		////sign  密钥，密钥设置地点，先登录财付通后访问：https://www.tenpay.com/med/admin_opentrans_key.shtml
		////物品类型
*/

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'portal','kpath'=>'tenpay'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'tenpayseller',//收款方财付通帐号
				'kmodule'=>'portal',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>15,
				'koption'=>'',
				'nstylewidth'=>150,
				'nstyleheight'=>0,
				'khelp'=>'help/tenpayseller',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'tenpaykey',//密钥设置
				'kmodule'=>'portal',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>32,
				'koption'=>'',
				'nstylewidth'=>150,
				'nstyleheight'=>0,
				'khelp'=>'help/tenpaykey',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){
			$king->db->insert('%s_system',$val);
		}
/*
		//支付宝付款设置
*/

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'portal','kpath'=>'alipay'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'alipayregmail',//支付宝注册邮箱
				'kmodule'=>'portal',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>250,
				'koption'=>'',
				'nstylewidth'=>150,
				'nstyleheight'=>0,
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'alipaypartner',//商户id
				'kmodule'=>'portal',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>0,
				'nsizemax'=>25,
				'koption'=>'',
				'nstylewidth'=>150,
				'nstyleheight'=>0,
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'alipaykey',//密钥设置
				'kmodule'=>'portal',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>32,
				'koption'=>'',
				'nstylewidth'=>150,
				'nstyleheight'=>0,
				'khelp'=>'help/alipaykey',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){
			$king->db->insert('%s_system',$val);
		}


		//user

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'user','kpath'=>'basic'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'isregister',
				'kmodule'=>'user',
				'kvalue'=>'1',
				'ntype'=>4,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Yes'.NL.'0|No',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'reglicense',
				'kmodule'=>'user',
				'kvalue'=>$license,
				'ntype'=>3,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>999999,
				'nstylewidth'=>800,
				'nstyleheight'=>450,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'registertip',
				'kmodule'=>'user',
				'kvalue'=>'<p>对不起,目前本站禁止新用户注册,请返回!</p>',
				'ntype'=>3,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>999999,
				'nstylewidth'=>780,
				'nstyleheight'=>300,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'blackuser',
				'kmodule'=>'user',
				'kvalue'=>'admin|李洪志|毛泽东|江泽民|温家宝|胡锦涛|宋祖德|范跑跑',
				'ntype'=>2,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>999999,
				'nstylewidth'=>780,
				'nstyleheight'=>300,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'templatelogin',
				'kmodule'=>'user',
				'kvalue'=>$king->config('templatepath').'/'.$king->config('templatedefault'),
				'ntype'=>13,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'templateregister',
				'kmodule'=>'user',
				'kvalue'=>$king->config('templatepath').'/'.$king->config('templatedefault'),
				'ntype'=>13,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'templateuser',
				'kmodule'=>'user',
				'kvalue'=>$king->config('templatepath').'/'.$king->config('templatedefault'),
				'ntype'=>13,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'userpre',
				'kmodule'=>'user',
				'kvalue'=>'KingCMS',
				'ntype'=>1,
				'nvalidate'=>1,
				'nsizemin'=>1,
				'nsizemax'=>20,
				'nstylewidth'=>100,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'regtime',
				'kmodule'=>'user',
				'kvalue'=>'3600',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>1,
				'nsizemax'=>10,
				'koption'=>'3600|1 hour'.NL.'86400|One day',
				'nstylewidth'=>50,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
		);
		foreach($array as $val){
			$king->db->insert('%s_system',$val);
		}

		$cid=$king->db->insert('%s_system_caption',array('kmodule'=>'user','kpath'=>'ucenter'));
		$array=array(
			array(
				'cid'=>$cid,
				'kname'=>'usercenter',
				'kmodule'=>'user',
				'kvalue'=>'0',
				'ntype'=>4,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Yes'.NL.'0|No',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_connect',
				'kmodule'=>'user',
				'kvalue'=>'mysql',
				'ntype'=>4,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>10,
				'koption'=>'mysql|MySQL'.NL.'0|NULL',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'help/uc_connect',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'ucpath',
				'kmodule'=>'user',
				'kvalue'=>'user/client',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_dbhost',
				'kmodule'=>'user',
				'kvalue'=>'localhost',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'koption'=>'localhost',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_dbname',
				'kmodule'=>'user',
				'kvalue'=>'test',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_dbtablepre',
				'kmodule'=>'user',
				'kvalue'=>'test.uc_',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>100,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_dbuser',
				'kmodule'=>'user',
				'kvalue'=>'root',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_dbpw',
				'kmodule'=>'user',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_dbcharset',
				'kmodule'=>'user',
				'kvalue'=>'utf8',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>30,
				'koption'=>'',
				'nstylewidth'=>100,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_dbconnect',
				'kmodule'=>'user',
				'kvalue'=>'1',
				'ntype'=>4,
				'nvalidate'=>0,
				'nsizemin'=>1,
				'nsizemax'=>1,
				'koption'=>'1|Yes'.NL.'0|No',
				'nstylewidth'=>0,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_key',
				'kmodule'=>'user',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_api',
				'kmodule'=>'user',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_ip',
				'kmodule'=>'user',
				'kvalue'=>'',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>100,
				'koption'=>'',
				'nstylewidth'=>200,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_charset',
				'kmodule'=>'user',
				'kvalue'=>'utf8',
				'ntype'=>1,
				'nvalidate'=>0,
				'nsizemin'=>0,
				'nsizemax'=>10,
				'koption'=>'',
				'nstylewidth'=>100,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),
			array(
				'cid'=>$cid,
				'kname'=>'uc_appid',
				'kmodule'=>'user',
				'kvalue'=>'1',
				'ntype'=>1,
				'nvalidate'=>2,
				'nsizemin'=>0,
				'nsizemax'=>10,
				'koption'=>'',
				'nstylewidth'=>100,
				'nstyleheight'=>0,
				'khelp'=>'',
				'norder'=>$i++,
			),

		);
		foreach($array as $val){
			$king->db->insert('%s_system',$val);
		}
	}

	$this->install_update(100);

	//写模块安装记录
	if(!$king->db->getRows_one('SELECT * FROM %s_module where kpath=\'portal\';')){
		$_array=array(
			'kname' =>$king->lang->get('portal/name'),
			'kpath' =>$this->path,
			'kdb'   =>'',
			'ndate' =>time(),
			'ndbver'=>$this->dbver,
			'norder'=>$king->db->neworder('%s_module'),
			);
		$kid1=$king->db->insert("%s_module",$_array);
		//安装子USER子模块
		$_array=array(
			'kid1'=>$kid1,
			'kname' =>$king->lang->get('user/name'),
			'kpath' =>'user',
			'kdb'   =>'',
			'ndate' =>time(),
			'ndbver'=>100,
			'norder'=>$king->db->neworder('%s_module'),
			);
		$king->db->insert("%s_module",$_array);

		return true;
	}else{
		return false;
	}



} //!install
public function install_update($ver){

	global $king;

	if($ver<101){//版本101或更小版本的时候升级
		$sql='gidpublish int(10) NOT NULL default 0';
		$king->db->alterTable('%s_list',$sql);
//		$king->db->query("ALTER TABLE %s_list ADD ($sql);");
	}

	if($ver<102){
		$array=array(
			'modelid'=>-1,
		);
		$king->db->update('%s_list',$array,"modelid=1");
	}

	if($ver<103){//增加评论相关设置
		$sql='ktemplatecomment char(255) null';
		$king->db->alterTable('%s_model',$sql);
		$sql='ncommentnumber int not null default 20';
		$king->db->alterTable('%s_model',$sql);
		$_array=array(
					  'ktemplatecomment'=>$king->config('templatepath').'/default.htm',
					  );
		$king->db->update('%s_model',$_array);
	}
	
	return True;
}





/* ------>>> 生成部分 <<<---------------------------- */



/**

	创建列表页

	@param string $listid : 列表id
	@param int    $pid    : 指定列表页生成
	@param int    $is   : : 生成或是返回值？1返回值
	@param int    $order  : 排序

	@return string

*/
public function createList($listid,$pid=1,$is=null,$order=0){
	global $king;

	$info=$this->infoList($listid);
	$model=$this->infoModel($info['modelid']);
	$site=$this->infoSite($info['siteid']);

	if($info['nlist']!=0&&$is==null)
		return;

	$_order=($order ? $order : $model['klistorder']);

	$tmp=new KC_Template_class($info['ktemplatelist1'],$info['ktemplatelist2']);

	$tmp->assign('type','list');
	foreach($this->getField('list','site') as $val){
		$tmp->assign($val,$site[$val]);
	}
	foreach($this->getField('list','field') as $val){
		$tmp->assign(substr($val,1),$info[$val]);
	}
	foreach($this->getField('list','id') as $val){
		$tmp->assign($val,$info[$val]);
	}
	foreach($this->getField('list','else') as $val){
		$tmp->assign($val,$info[$val]);
	}
	$tmp->assign('pid',$pid);

	$tmp->assign('add',$king->config('inst')."portal/index.php?action=edt&listid=$listid");//添加新话题

	if($is){
		$tmp->assign('#order',$_order);
		return $tmp->output();
	}else{
		$filepath=$this->pathList($info,1,$pid);
		if($filepath!==False){
			kc_f_put_contents($filepath,$tmp->output(),1);
		}
	}

}
/**

	创建内容页

	@param string $listid: 列表
	@param int    $kid   : 要生成的id
	@param int    $pid   : 第?页
	@param int    $is    : 生成或是返回值？1返回值
	@return string

	*这里还要加入当kids=0的时候，生成listid列表下的所有内容的功能

*/
//创建页面
public function createPage($listid,$kid,$pid=1,$is=null){
	global $king;
	if(!kc_validate($kid,2))
		return false;

	$info=$this->infoList($listid);
	$model=$this->infoModel($info['modelid']);
	$site=$this->infoSite($info['siteid']);

	if($info['npage']!=0&&$is==null) return;

	if($info['modelid']<=0)
		kc_error($king->lang->get('system/error/param').kc_clew(__FILE__,__LINE__));

	$id=$this->infoID($listid,$kid);
	//读取模板
	$tmp=new KC_Template_class($info['ktemplatepage1'],$info['ktemplatepage2']);

	$tmp->assign('type','page');
	foreach($this->getField('list','field') as $val){
		$tmp->assign(substr($val,1),$info[$val]);
	}
	foreach($this->getField('list','site') as $val){
		$tmp->assign($val,$site[$val]);
	}
	foreach($this->getField('list','id') as $val){
		$tmp->assign($val,$info[$val]);
	}

	foreach($model['field']['html'] as $key=>$val){
		$tmp->assign(substr($key,1),kc_val($id,$key));
	}
	foreach($model['field']['text'] as $key=>$val){
		$tmp->assign(substr($key,1),kc_val($id,$key));
	}
	foreach($model['field']['attrib'] as $val){
		$tmp->assign(substr($val,1),kc_val($id,$val));
	}
	foreach($model['field']['id'] as $val){
		$tmp->assign($val,$id[$val]);
	}

	foreach($model['field']['images'] as $key => $val){//图片组类型
		$array=$this->tab2array(kc_val($id,$key),'images');
		$tmp->assign(substr($key,1),$array);//替换已经提交过的字符串值
	}

	foreach($model['field']['files'] as $key => $val){//文件组类型
		$array=$this->tab2array(kc_val($id,$key),'files');
		$tmp->assign(substr($key,1),$array);
	}

	$kpath=$this->pathPage($info,$kid,$id['kpath'],0,1);//第一页的路径，不要指定为$pid，会出错

	$tmp->assign('path',$kpath);

	$tmp->assign('pid',$pid);
	//前置命令
	$tmp->assign('add',$king->config('inst')."portal/index.php?action=edt&listid=$listid");//添加新话题
	$tmp->assign('reply',$king->config('inst')."portal/index.php?action=edt&listid=$listid&kid1=$kid");//回复话题
	$tmp->assign('commentlink',$king->config('inst')."comment.php?modelid={$info['modelid']}&listid=$listid&kid=$kid");
	$tmp->assign('edit',$king->config('inst')."portal/index.php?action=edt&listid=$listid&kid=$kid");//编辑话题
	$tmp->assign('hit',"<em id=\"k_hit\">". ($info['npage']==0 ? 'Loading...' : ($id['nhit']+1) ) ."</em><script type=\"text/javascript\" charset=\"UTF-8\"><!--\n\$.kc_ajax('{URL:\'".$king->config('inst')."portal/index.php\',CMD:\'hit\',kid:$kid,modelid:{$info['modelid']},IS:1}')\n--></script>");//访问统计
	$tmp->assign('comment',"<em id=\"k_comment\">". 'Loading...'."</em><script type=\"text/javascript\" charset=\"UTF-8\"><!--\n\$.kc_ajax('{URL:\'".$king->config('inst')."portal/index.php\',CMD:\'commentcount\',kid:$kid,modelid:{$info['modelid']},IS:1}')\n--></script>");//评论统计
	$tmp->assign('digg',"<div id=\"k_digg\"><p id=\"k_digg1\" onclick=\"\$.kc_ajax('{URL:\'".$king->config('inst')."portal/index.php\',CMD:\'digg\',kid:$kid,modelid:{$info['modelid']},type:1,IS:1}');\">Loading...</p><p id=\"k_digg0\" onclick=\"\$.kc_ajax('{URL:\'".$king->config('inst')."portal/index.php\',CMD:\'digg\',kid:$kid,modelid:{$info['modelid']},type:0,IS:1}');\">Loading...</p><script type=\"text/javascript\" charset=\"UTF-8\"><!--\n\$.kc_ajax('{URL:\'".$king->config('inst')."portal/index.php\',CMD:\'digg\',kid:$kid,modelid:{$info['modelid']},type:2,IS:1}')\n--></script></div>");//顶踩
	if($is||$info['npage']!=0){
		return $tmp->output();
	}else{
		kc_f_put_contents($this->pathPage($info,$kid,$id['kpath'],1,$pid),$tmp->output(),1);
	}

}
public function createTag($tagname,$pid){
	global $king;

	$tag=$this->infoTag($tagname);

	$tmp=new KC_Template_class($tag['ktemplate1'],$tag['ktemplate2']);

	$array=array('ktag','kimage','kkeywords','kdescription','kcolor','nsize');
	foreach($array as $val){
		$tmp->assign(substr($val,1),$tag[$val]);
	}
	$tmp->assign('type','tag');
	$tmp->assign('title',$tag['ktag']);
	$tmp->assign('hit',$tag['nhit']);
	$tmp->assign('bold',$tag['isbold']);
	$tmp->assign('commend',$tag['iscommend']);
	$tmp->assign('path',$this->pathTag($tag['ktag']));

	return $tmp->output();

}
/* ------>>> 外部调用 <<<---------------------------- */

/**

	@param array $url  系列化的url参数

	@return string




*/
public function index($url){
	global $king;
	$listid=isset($url[1]) ? $url[1] :'';
	if($listid=='' && in_array($url['path_info'],array('','/'))){
		$siteid=$this->getSiteid();
		$listid=$this->getSiteHome($siteid);
		$url[0]='list';
	}

	$s='';

	switch($url[0]){
		case 'page':
			$kid=$url[2];
			$pid=$url[3];
			$info=$this->infoList($listid);
			//当设置为不显示的时候，显示错误提示
			if($info['npage']==3){
				$this->error($king->lang->get('system/common/error'),$king->lang->get('portal/error/stop'));
			}
			//页面访问权限验证
			if((int)$info['gid']===0 || $info['gid']>1){
				$king->Load('user');
				$king->user->access($info['gid']);
			}

			$s=$this->createPage($listid,$kid,$pid,1);
		break;

		case 'list':
//			kc_error('<pre>'.print_r($url,1));
			$pid=empty($url[2])? 1 : $url[2];
			$order=empty($url[3]) ? '' : $url[3];
			$info=$this->infoList($listid);
			//当设置为不显示的时候，显示错误提示
			if($info['nlist']==3){
				$this->error($king->lang->get('system/common/error'),$king->lang->get('portal/error/stop'));
			}
			//页面访问权限验证
			if($info['modelid']!=0 and ((int)$info['gid']===0 || $info['gid']>1)){
				$king->Load('user');
				$king->user->access($info['gid']);
			}
			$s=$this->createList($listid,$pid,1,$order);
		break;

		case 'tag':
			$line=$king->config('rewriteline');
			$end=$king->config('rewriteend');
			//这个不能split，而是直接从path_info中分析
			preg_match('/^\/tag'.preg_quote($line).'(.+?)('.preg_quote($line).'([0-9]+))?'.preg_quote($end).'$/',$url['path_info'],$array);
			$pid=isset($array[3]) ? $array[3] : 1;
			$s=$this->createTag($url[1],$pid);
		break;
	}


	return $s;

}
/**
输出错误提示
*/
public function error($title=NULL,$main,$template=NULL){

	global $king;

	$title= ($title==NULL) ? $king->lang->get('system/common/error') : $title;
	$template=($template==NULL) ? $king->config('templatepath').'/'.$king->config('templatedefault') : $template;

	$tmp=new KC_Template_class($template,$king->config('templatepath').'/inside/system/error.htm');
	$tmp->assign('title',$title);
	$tmp->assign('main',$main);

	exit($tmp->output());
}


/* ------>>> 标签解析 <<<---------------------------- */

/**

	@param string $name   标签名  portal.article
	@param string $inner  循环体内的
	@param array $ass     assign 内容
	@param array $attrib  属性数组

	@return string

*/

public function tag($name,$inner,$ass,$attrib){

	global $king;

	$names=explode('.',$name);
	$modeltable=kc_val($names,1);//[1];
	$modeltables=$this->getModelTables();
	$where='';$order='';


	if(!in_array($modeltable,$modeltables)){//如果模型列表中没有这个类型，则单独进行判断
//	kc_error($modeltable);
		switch($modeltable){

			case '':
				$modelid=kc_val($attrib,'modelid');
				if($modelid>0){
					$modeltable=$modeltables[$modelid];
				}
			break;//多模型联合调用
			case 'list':return $this->tag_list($inner,$ass,$attrib);
			case 'model':return $this->tag_model($inner,$ass,$attrib);
			case 'pagelist':return $this->tag_pagelist($inner,$ass,$attrib);
			case 'nav':return $this->tag_nav($ass,$attrib);

			case 'menu':return $this->tag_list($inner,$ass,$attrib);
			case 'menu1':return $this->tag_menu($inner,$ass,$attrib,$modeltable);
			case 'menu2':return $this->tag_menu($inner,$ass,$attrib,$modeltable);
			case 'menu3':return $this->tag_menu($inner,$ass,$attrib,$modeltable);
			case 'menu4':return $this->tag_menu($inner,$ass,$attrib,$modeltable);
			case 'menu5':return $this->tag_menu($inner,$ass,$attrib,$modeltable);
			case 'sitemap': return $this->tag_menu($inner,$ass,$attrib,$modeltable);

			case 'action':return $this->tag_action($names[2],$ass,$attrib);
			case 'orders':return $this->tag_orders($inner,$ass,$attrib);

			case 'tag':return $this->tag_tag($inner,$attrib);
			
			case 'comment':return $this->tag_comment($inner,$attrib);

			default:return;
		}
	}


	$number=isset($attrib['number']) ? $attrib['number'] :10;
	if(!kc_validate($number,2)) $number=10;

	$zebra=isset($attrib['zebra']) ? $attrib['zebra'] :2;
	if(!kc_validate($zebra,2)) $zebra=2;

	$skip=isset($attrib['skip']) ? $attrib['skip'] : 0;//跳过skip个后读取
	if(!kc_validate($skip,2)) $skip=0;

	$type=kc_val($attrib,'type');
	switch(strtolower($type)){
		case 'relate':
			$relate=$ass['relate'];
			isset($relate{0})
				? $where="and kid in ($relate)"
				: $where='and kid=0';
		break;

		case 'hot':
			$where='and nhot=1';
			$order='kid desc';
		break;//热门
		case 'focus':
			$where='and nfocus=1';
			$order='kid desc';
		break;
		case 'commend'://推荐
			$where='and ncommend=1';
			$order='kid desc';
		break;
		case 'head':
			$where='and nhead=1';
			$order='kid desc';
		break;
		case 'good'://好
			$where='and kid1=0';
			$order='ndigg desc';
		break;
		case 'bad'://差
			$where='and kid1=0';
			$order='ndigg asc';
		break;
		case 'hit'://热点
			$where='and kid1=0';
			$order='nhit desc';
		break;
		case 'lately'://最近热门点击
			$where='and kid1=0';
			$order='nhitlate desc';
		break;
		case 'comment'://热评
			$where='and kid1=0';
			$order='ncomment desc,norder desc';
		break;
		case 'chill':
			$where='and kid1=0';
			$order='nhit asc';
		break;

		case 'rand':
			//联合调用不支持随机
			if($modeltable=='')
				return False;

			$resTemp=$king->db->getRows_one("SELECT MAX(kid) max,MIN(kid) min FROM %s__$modeltable;");
			$arrayKid=array();
			for($i=0;$i<$number*3;$i++){
				$arrayKid[]=rand($resTemp['min'],$resTemp['max']);
			}
			if($arrayKid){
				$where='and kid in ('.implode(',',$arrayKid).')';
			}
		break;

		case 'list':
			//联合调用不支持列表
			if($modeltable=='')
				return False;

			$pid=kc_val($ass,'pid');
			$listid=$ass['listid'];

			$info=$this->infoList($listid);
			$model=$this->infoModel($info['modelid']);
			$order=$model['klistorder'];
			$where=" and (listid={$listid}) and kid1=0";

			$info=$this->infoList($listid);

			if($pid==0){//这个是列表首页
				$limit='limit '.$info['nlistnumber'];
			}else{//列表分页
				$limit='limit '.($info['nlistnumber']*($pid-1)).','.$info['nlistnumber'];
			}
		break;

		case 'pagelist':
			//联合调用不支持分页列表
			if($modeltable=='')
				return False;

			$order='norder asc';
			if($ass['type']=='page'){
				if($ass['kid1']==0){
					$where=" and (kid={$ass['kid']} or kid1={$ass['kid']})";
				}else{
					$where=" and (kid={$ass['kid1']} or kid1={$ass['kid1']})";
				}
				$listid=$ass['listid'];

				$info=$this->infoList($listid);

				if($info['npagenumber']!=1) return False;

				$model=$this->infoModel($info['modelid']);
				$order=$model['kpageorder'];
				$limit=" limit 0,999";
			}else{
				$where=' and kid=0';
			}
		break;

/**
createPage页中需要获取的参数
kid
listid
pid
type=page
modelid
*/
		case 'page':
			//联合调用不支持分页列表
			if($modeltable=='')
				return False;

			$order='norder asc';
			if($ass['type']=='page'){
				if($ass['kid1']==0){
					$where=" and (kid={$ass['kid']} or kid1={$ass['kid']})";
				}else{
					$where=" and (kid={$ass['kid1']} or kid1={$ass['kid1']})";
				}
				$listid=$ass['listid'];

				$info=$this->infoList($listid);

				if($info['npagenumber']==1) return False;

				$model=$this->infoModel($info['modelid']);
				//order
				$order=$model['kpageorder'];
				//limit
				$pid=$ass['pid']?$ass['pid']:1;
				$limit=' limit '.($info['npagenumber']*($pid-1)).','.$info['npagenumber'];
			}else{
				$where=' and kid=0';
			}
//kc_error("select listid,kid from %s__$modeltable where  nshow=1 {$where} {$order} {$limit}");

		break;

		case 'tag':
			$tagname=kc_val($ass,'tag');
			$where=" and ktag like '%".$king->db->escape($tagname)."%'";
		break;

		case 'recent':
			//联合调用不支持浏览过的信息
			if($modeltable=='')
				return False;

			$order='';
			$listid=$ass['listid'];
			$info=$this->infoList($listid);
			$model=$this->infoModel($info['modelid']);
			$type=$ass['type'];
			if($info['n'.$type]==0)
				return ;

			$kids=addslashes($_COOKIE[$model['modeltable']]['recent']);
			if(kc_validate($kids,3)){
				$where=" and kid in ($kids)";
			}else{
				$where=' and kid=0';
			}
		break;

		case 'irregular':
			//联合调用不支持不规则新闻
			if($modeltable=='')
				return False;

			$length=kc_val($attrib,'length',30);
			if($length>40) $length=40;
			//如果$number为奇数，则改成偶数
			$number=floor($number/2)*2;
			//对不规则新闻的处理在下面where结束后
		break;

		case 'search':
			//联合调用若让他支持搜索，不知道会不会有很多问题
			if($modeltable=='')
				return False;

			/*和database类中的一样，获得每页显示数等参数*/
			$pid=kc_val($ass,'pid');
			$rn=kc_val($ass,'rn');

			$where=kc_val($ass,'search');
			$modelid=kc_val($ass,'modelid');

			$model=$this->infoModel($modelid);
			$order=$model['klistorder'];

			$limit="limit ".($rn*($pid-1)).",$rn";
		break;

		case 'new':
			$order=' norder desc';
			$where=" and kid1=0";
		break;

		default:
			if(!isset($attrib['orderby'])){
				$order=' norder desc,kid desc';
			}
	}


	if(!isset($limit{0})){//如果没有设置limit值
		$limit='limit '.$skip.','.$number;
	}

	if($modeltable==''){//联合调用

	}else{//普通的调用
		$modelid=array_search($modeltable,$modeltables);

		$model=$this->infoModel($modelid);

		//关联字段
		if(isset($model['field']['isrelate'])){
			$relate=$model['field']['isrelate'];
			if(is_array($relate)){
				foreach($relate as $key=>$val){
					if(isset($attrib[$key]))
						$where.=" and k{$val}='".$king->db->escape($attrib[$key])."'";
					//不等于验证
					if(isset($attrib[$key.'!']))
						$where.=" and k{$val}<>'".$king->db->escape($attrib[$key.'!'])."'";

				}
			}
		}
		//限定listid等等
		$field_id=$model['field']['id'];
		foreach($field_id as $val){
			if(isset($attrib[$val])){
				if(kc_validate($attrib[$val],2)){
					$where.=" and {$val}=".$king->db->escape($attrib[$val]);
				}elseif(kc_validate($attrib[$val],3)){
					$where.=" and {$val} in (".$king->db->escape($attrib[$val]).")";
				}
			}
			//不等于限制 !=
			if(isset($attrib[$val.'!'])){
				if(kc_validate($attrib[$val.'!'],2)){
					$where.=" and {$val}<>".$king->db->escape($attrib[$val.'!']);
				}elseif(kc_validate($attrib[$val.'!'],3)){
					$where.=" and {$val} not in (".$king->db->escape($attrib[$val.'!']).")";
				}
			}
		}
	}
	//siteid属性 分析如果有指定siteid，则先获得listid列表进行限定
	$siteid=isset($attrib['siteid']) ? $attrib['siteid'] : '';
	if(kc_validate($siteid,2) || kc_validate($siteid,3)){
		$lists=array();
		if($sites=$king->db->getRows("select listid from %s_list where ".(kc_validate($siteid,2) ? "siteid=$siteid" :"siteid in ($siteid)"))){
			foreach($sites as $rs){
				$lists[]=$rs['listid'];
			}
		}
		if(count($lists)===1){
			$where.=" and listid={$lists[0]}";
		}elseif(count($lists)>1){
			$where.=" and listid in (".implode(',',$lists).")";
		}
	}

	//判断是否有图片，需要对$inner进行预搜索
	if(stripos($inner,'{king:image')!==False && !in_array($type,array('list','page','pagelist','relate','search'))){
		$where.=" and kimage<>''";
	}

	//SQL扩展属性where,这个功能是标签解析不稳定的隐患功能。
	if(isset($attrib['where'])){
		$where.=" and ".$attrib['where'];
	}



	//SQL扩展属性orderby
	if(isset($attrib['orderby'])){
		$order=$attrib['orderby'];
	}

	$order=isset($order{0}) ? 'order by '.$order : '';

	//不规则新闻
	if($type=='irregular' && $modeltable!=''){
		$kids=array();
		$kids_not=array();//过滤名单
		$order=isset($order{0}) ? $order.",ndate desc" : 'order by norder ndate desc,kid desc';
		$sql_not='';
		$i=0;$j=0;

		while( ($i < ($number/2))){// && $j++<100
			$sql="select kid,nsublength from %s__$modeltable where nshow=1 $where and kid1=0".$sql_not;

			if($rs1=$king->db->getRows_one("$sql and nsublength>3 $order limit 1")){
				$nsublength=($length-$rs1['nsublength']);
				if($rs2=$king->db->getRows_one("$sql and nsublength BETWEEN ".($nsublength-1)." AND ".($nsublength+1)." $order limit 1")){
					$kids[]=$rs1['kid'];
					$kids[]=$rs2['kid'];
					$i++;
				}else{//正好没有匹配的，则把rs1['kid']加入到过滤名单里，否则死循环
					$kids_not[]=$rs1['kid'];
				}
				$sql_not=" and kid not in (".implode(',',array_merge($kids,$kids_not)).")";
			}else{
				break;//没有对应的记录集的时候，推出while循环，否则进入死循环
			}
		}
		if(!empty($kids)){
			$where=" and kid in (".implode(',',$kids).")";
		}else{
			$where=" and kid=0";//kids为空，没啥可读的。
		}

	}

	if($modeltable==''){
		$sql_array=array();
		foreach($modeltables as $table){
			$sql_array[]="select * from(select listid,kid from %s__$table where nshow=1 {$where} {$order}) as tmp_$table";
		}
		if(!empty($sql_array)){
			$sql=implode(' UNION ALL ',$sql_array)." {$limit}";
		}else{
			return False;
		}

	}else{
		$sql="select listid,kid from %s__$modeltable where  nshow=1 {$where} {$order} {$limit}";
	}
	if(!$res=$king->db->getRows($sql))
		return False;

	if($type=='irregular' && !empty($kids) && $modeltable!=''){//如果是随机类型的话，重新给他排序
		$array=array();
		foreach($res as $val){
			$array[$val['kid']]=$val;
		}
		$res=array();//初始化$res
		foreach($kids as $val){
			$res[]=$array[$val];
		}

	}

	$tmp=new KC_Template_class;

	$s='';
	$i=1;

	foreach($res as $rs){
		$listid=$rs['listid'];
		$kid=$rs['kid'];

		$info=$this->infoList($listid);
		$model=$this->infoModel($info['modelid']);
		$id=$this->infoID($listid,$kid);

		foreach($model['field']['html'] as $key=> $val){
			$tmp->assign(substr($key,1),kc_val($id,$key));
		}
		foreach($model['field']['text'] as $key=>$val){
			$tmp->assign(substr($key,1),kc_val($id,$key));
		}
		foreach($model['field']['attrib'] as $val){
			$tmp->assign(substr($val,1),kc_val($id,$val));
		}
		foreach($model['field']['id'] as $val){
			$tmp->assign($val,kc_val($id,$val));
		}

		foreach($model['field']['images'] as $key => $val){//图片类型
			$array=$this->tab2array(kc_val($id,$key),'images');
			$tmp->assign(substr($key,1),$array);//替换已经提交过的字符串值
		}

		foreach($model['field']['files'] as $key => $val){//文件
			$array=$this->tab2array(kc_val($id,$key),'files');
			$tmp->assign(substr($key,1),$array);
		}

		$tmp->assign('this',isset($ass['kid']) ? (int)($kid==$ass['kid']) :0);

		$tmp->assign('zebra',(($i-1) % $zebra)==0 ? 1 : 0);

		$tmp->assign('i',$i++);

		$tmp->assign('edit',$king->config('inst')."portal/index.php?action=edt&listid=$listid&kid=$kid&kid1={$id['kid1']}");//编辑话题

		$kpath=$this->pathPage($info,$kid,$id['kpath'],0, 1);

		$tmp->assign('path',$kpath);

		$s.=$tmp->output($inner);


	}

	if(empty($s) && strtolower($type)=='search'){
		$s="<p class=\"k_error\">".$king->lang->get('portal/error/notq')."</p>";
	}
	return $s;
}
/**
	位置导航

	@param array $ass     assign 内容
	@param array $attrib  属性数组

*/
public function tag_nav($ass,$attrib){
	global $king;

	$line=kc_val($attrib,'line');//分割线

	//listid是总是存在的，判断kid，如果kid有值，则输出“正文”
	$type=kc_val($ass,'type');
	if($type=='page'){//正文
		$listid=$ass['listid'];

		$s=$this->getNav($listid,$line);
		$s.=$line.'<strong>'.$king->lang->get('system/common/text').'</strong>';
	}elseif($type=='list'){//列表
		$listid=$ass['listid1'];
		$s=$this->getNav($listid,$line);
		$s.=(isset($s{0})?$line:'').'<strong>'.$ass['listname'].'</strong>';

	}elseif(in_array($type,array('edit','add','reply'))){
		$listid=$ass['listid'];
		$s=$this->getNav($listid,$line);
/**/
		$s.=(isset($s{0})?$line:'').'<strong>'.$king->lang->get('system/common/'.$type).'</strong>';

	}else{
		$s=isset($ass['nav']) ? $ass['nav'] : $ass['title'];
	}

	return $s;
}

/**
	递归 nav

	@param int    $listid : 列表ID
	@param string $line   : 分割线

	@return string
*/
private function getNav($listid,$line){
	if($listid==0)
		return;

	$info=$this->infoList($listid);
	$listid1=$info['listid1'];
	$path=$this->pathList($info);
	$s='<a title="'.$info['klistname'].'" href="'.$path.'">'.$info['klistname'].'</a>';
	if($listid1!=0){
		$s=$this->getNav($listid1,$line).$line.$s;
	}
	return $s;

}
/**
	联动下拉菜单
	@param string $id     :表单对象的ID
	@param int    $listid :默认列表ListID,开始值
	@param int    $is     :是否显示当前列表的下级列表,0不显示
	@param int    $def    :最原始的值,当$is==0的时候需要设置
	@param int    $sub    :关联的列表ListID,为设置下拉选项的默认值
	@param int    $iss    :第一次调用1,无需外部设置
*/
public function LinkAge($id,$listid,$is=1,$def='',$sub=NULL,$iss=0){
	global $king;

	$s='';
	if($res=$king->db->getRows("select listid,klistname from %s_list where listid1=$listid order by norder,listid;")){
		$s.="<select onChange=\"javascript:\$.kc_ajax('{CMD:\'linkage\',URL:\'../portal/manage.php\',ID:\'listid_select\',IS:1,listid:\''+\$(this).val()+'\',id:\'$id\',is:\'$is\',def:\'$def\',sub:\'$listid\'}')\">";
		if($listid==0){
			$s.='<option value="0">-'.$king->lang->get('portal/list/root').'-</option>';
		}else{
			$s.='<option value="'.$listid.'">-'.$king->lang->get('system/common/please').'-</option>';
		}
		$str='';
		foreach($res as $rs){
			if(($def!=$rs['listid'] && $is==0) || $is==1){
				$str.='<option'.($sub==$rs['listid']?' selected="selected"':'').' value="'.$rs['listid'].'">'.htmlspecialchars($rs['klistname']).'</option>';
			}
		}
		$s.=$str.'</select> ';

		if($str=='') $s='';
	}

	if($listid!=0){
		$info=$this->infoList($listid);
		$s=$this->LinkAge($id,$info['listid1'],$is,$def,$listid,1).$s;
	}

	$s= $iss===0 ? '<span id="listid_select">'.$s.'</span>'.kc_htm_hidden(array($id=>$listid)) : $s;//."<input size=2 name=\"$id\" id=\"$id\" value=\"$listid\" />"

	return $s;

}

/**

	调用列表页

	@param string $inner   INNERHTML
	@param array  $ass     assign 内容
	@param array  $attrib  属性数组

*/
public function tag_list($inner,$ass,$attrib){
	global $king;

	$whereArray=array();
	/* 属性设置 开始 */
	$language=kc_val($attrib,'language');
	if(isset($language{0}))
		$whereArray[]="klanguage='{$language}'";

	$zebra=isset($attrib['zebra']) ? $attrib['zebra'] :2;
	if(!kc_validate($zebra,2)) $zebra=2;

	$array=array('listid1','listid','siteid','modelid','npage','nlist','gid');
	foreach($array as $val){

		$value=kc_val($attrib,$val);
		if(kc_validate($value,2)){
			$whereArray[]="$val={$value}";
		}elseif(kc_validate($value,3)){
			$whereArray[]="$val in ({$value})";
		}
		//排除
		$value_=kc_val($attrib,$val.'!');
		if(kc_validate($value_,2)){
			$whereArray[]="$val<>{$value_}";
		}elseif(kc_validate($value_,3)){
			$whereArray[]="$val not in ({$value_})";
		}

	}

	if(stripos($inner,'{king:image')!==False ){
		$whereArray[]="kimage<>''";
	}


	$array=array('ispublish1','ispublish2','isexist','ismenu1','ismenu2','ismenu3','ismenu4','ismenu5','ismap');
	foreach($array as $val){
		if(isset($attrib[$val])){
			$whereArray[]= $val.'='.($attrib[$val] ? 1 :0);
		}
	}

	/* 属性设置 结束 */

	$where=($whereArray) ? 'where '.implode(' and ',$whereArray):'';
	$orderby=isset($attrib['orderby']) ? ' ORDER BY '.$attrib['orderby'] : ' ORDER BY norder asc,listid desc';
	$limit=isset($attrib['number']) ? "limit {$attrib['number']}" : '';

	if(!$res=$king->db->getRows("select listid from %s_list {$where} $orderby $limit"))
		return;

	$s='';
	$i=1;

	$tmp=new KC_Template_class;

	foreach($res as $rs){
		$info=$this->infoList($rs['listid']);
		$site=$this->infoSite($info['siteid']);

		$tmp->assign('type','list');
		foreach($this->getField('list','site') as $val){
			$tmp->assign($val,kc_val($site,$val));
		}
		foreach($this->getField('list','field') as $val){
			$tmp->assign(substr($val,1),kc_val($info,$val));
		}
		foreach($this->getField('list','id') as $val){
			$tmp->assign($val,$info[$val]);
		}
		foreach($this->getField('list','else') as $val){
			$tmp->assign($val,kc_val($info,$val));
		}
		$tmp->assign('this',isset($ass['listid']) ? (int)($rs['listid']==$ass['listid']) :0);

		$listpath=$this->pathList($info);//完整路径
		$tmp->assign('listpath',$listpath);
		$tmp->assign('path',$listpath);

		$tmp->assign('zebra',(($i-1) % $zebra)==0 ? 1 : 0);

		$tmp->assign('i',$i++);

		$s.=$tmp->output($inner);

	}

	return $s;


}
/**
	调用模型列表
	@param
	@return
*/
public function tag_model($inner,$ass,$attrib){
	global $king;

	$whereArray=array();

	/* 属性设置 开始 */

	$modelid=kc_val($attrib,'modelid');
	if(kc_validate($modelid,22)){
		$whereArray[]="modelid={$modelid}";
	}elseif(kc_validate($modelid,33)){
		$whereArray[]="modelid in ({$modelid})";
	}

	$issearch=kc_val($attrib,'issearch');
	if(kc_validate($issearch,2)){
		$whereArray[]="issearch=". ($issearch ? 1:0);
	}

	$ispublish1=kc_val($attrib,'ispublish1');
	if(kc_validate($ispublish1,2)){
		$whereArray[]="ispublish1=". ($ispublish1?1:0);
	}

	$ispublish2=kc_val($attrib,'ispublish2');
	if(kc_validate($ispublish2,2)){
		$whereArray[]="ispublish2=". ($ispublish2?1:0);
	}

	$language=kc_val($attrib,'language');
	if(isset($language{0}))
		$whereArray[]="klanguage='{$language}'";

	/* 属性设置结束 */

	$where= $whereArray ? 'where '.implode(' and ',$whereArray):'';

	if(!$res=$king->db->getRows("select modelid from %s_model {$where} order by norder desc"))
		return;

	$tmp=new KC_Template_class;
	$s='';
	$array_field=array('modelid','modelname','modeltable','issearch','ispublish1','ispublish2');

	foreach($res as $rs){
		$model=$this->infoModel($rs['modelid']);

		foreach($array_field as $val){
			$tmp->assign($val,$model[$val]);
		}

		$tmp->assign('language',$model['klanguage']);
		$tmp->assign('listnumber',$model['nlistnumber']);
		$tmp->assign('pagenumber',$model['npagenumber']);

		$s.=$tmp->output($inner);
	}
	return print_r($s,1);

}
/**

	列表分页

	@param string $inner   INNERHTML
	@param array  $ass     assign 内容
	@param array  $attrib  属性数组,目前没有可用属性
*/
public function tag_pagelist($inner,$ass,$attrib){
	global $king;
	$type=kc_val($ass,'type');
	$modelid=kc_val($ass,'modelid');
	//列表分页
	if($type=='list' && $modelid>0){//只有在列表页和modelid大于6的时候才调用

		$info=$this->infoList($ass['listid']);
		$site=$this->infoSite($info['siteid']);

		$pagelist=kc_pagelist($this->pathList($info,0,'PID'),$ass['ncount'],$ass['pid'],$ass['nlistnumber'],$inner);

		return $pagelist;
	}

	//内容分页
	if($type=='page' && $modelid>0){

		$info=$this->infoList($ass['listid']);

		if($info['npagenumber']==1){
			return;
		}

		$count=$ass['count'];
		$pagelist=kc_pagelist($ass['path'],$count,$ass['pid'],$info['npagenumber'],$inner);
		return $pagelist;
	}

	//搜索
	if($type=='search' && $modelid>0){
		$model=$this->infoModel($modelid);
		$where='nshow=1'.kc_val($ass,'search');

		//关联字段
		if(isset($model['field']['isrelate'])){
			$relate=$model['field']['isrelate'];
			if(is_array($relate)){
				foreach($relate as $key=>$val){
					if(isset($attrib[$key]))
						$where.=" and k{$val}='".$king->db->escape($attrib[$key])."'";
				}
			}
		}

		//限定listid等等
		$field_id=$model['field']['id'];
		foreach($field_id as $val){
			if(isset($attrib[$val])){
				if(kc_validate($attrib[$val],2)){
					$where.=" and {$val}=".$king->db->escape($attrib[$val])."";
				}elseif(kc_validate($attrib[$val],3)){
					$where.=" and {$val} in (".$king->db->escape($attrib[$val]).")";
				}
			}
		}
		//siteid属性 分析如果有指定siteid，则先获得listid列表进行限定
		$siteid=isset($attrib['siteid']) ? $attrib['siteid'] : '';
		if(kc_validate($siteid,2) || kc_validate($siteid,3)){
			$lists=array();
			if($sites=$king->db->getRows("select listid from %s_list where ".(kc_validate($siteid,2) ? "siteid=$siteid" :"siteid in ($siteid)"))){
				foreach($sites as $rs){
					$lists[]=$rs['listid'];
				}
			}
			if(count($lists)===1){
				$where.=" and listid={$lists[0]}";
			}elseif(count($lists)>1){
				$where.=" and listid in (".implode(',',$lists).")";
			}
		}

		//SQL扩展属性where,这个功能是标签解析不稳定的隐患功能。
		if(isset($attrib['where'])){
			$where.=" and ".$attrib['where'];
		}

		$count=$king->db->getRows_number('%s__'.$model['modeltable'],$where);

		$pid=kc_val($ass,'pid');
		$rn=kc_val($ass,'rn');

		$get=is_array($_GET)?$_GET:array();

		$array=array();
		foreach($get as $key => $val){
			if($key=='pid'||$key=='rn'){
				$array[]="$key=".strtoupper($key);
			}else{
				$array[]="$key=".urlencode($val);
			}
		}
		$url=implode('&',$array);
		if(!array_key_exists('pid',$array))
			$url.='&pid=PID';
		if(!array_key_exists('rn',$array))
			$url.='&rn=RN';

		$pagelist=kc_pagelist('search.php?'.$url,$count,$pid,$rn,$inner);

		return $pagelist;


	}
}
/**
	menu解析
	@param array  $ass     assign 内容
	@param array  $attrib  属性数组,目前没有可用属性
	@param string $menu    栏目类型,menu1-5,map
*/
public function tag_menu($inner,$ass,$attrib,$menu){
	global $king;

	$line=$attrib;

	if(in_array($menu,array('menu3','menu4','menu5'))){
		$sql="select listid from %s_list where is{$menu}=1 order by norder".substr($menu,-1,1);
	}elseif(in_array($menu,array('menu1','menu2'))){
		$listid1=empty($ass['listid']) ? 0 :  $ass['listid'];
		$sql="select listid from %s_list where is{$menu}=1 and listid=$listid1 order by norder". substr($menu,-1,1);
	}elseif(in_array($menu,array('map','sitemap'))){
		$listid1=empty($attrib['listid1']) ? 0: $attrib['listid1'];
		$sql="select listid from %s_list where ismap=1 and listid1=$listid1 order by norder";
	}else{
		return;
	}

	if(!$res=$king->db->getRows($sql))
		$res=array();

	$s='';

	$tmp=new KC_Template_class;

	foreach($res as $rs){
		$info=$this->infoList($rs['listid']);
		$site=$this->infoSite($info['siteid']);

		$tmp->assign('type','list');
		foreach($this->getField('list','site') as $val){
			$tmp->assign($val,kc_val($site,$val));
		}
		foreach($this->getField('list','field') as $val){
			$tmp->assign(substr($val,1),kc_val($info,$val));
		}
		foreach($this->getField('list','id') as $val){
			$tmp->assign($val,$info[$val]);
		}
		foreach($this->getField('list','else') as $val){
			$tmp->assign($val,kc_val($info,$val));
		}
		$tmp->assign('this',isset($ass['listid']) ? (int)($rs['listid']==$ass['listid']) :0);

		$listpath=$this->pathList($info);//完整路径
		$tmp->assign('listpath',$listpath);
		$tmp->assign('path',$listpath);


		$s.=$tmp->output($inner);

	}


	return $s;
}
public function tag_action($name2,$ass,$attrib){
	global $king;
	return $name2;
}

/*
订单解析
*/
public function tag_orders($inner,$ass,$attrib){
	global $king;

	$userid=$ass['userid'];

	if(!isset($userid{0})){
		return;
	}

	//oid限定
	$where='';
	$oid=isset($attrib['oid'])?$attrib['oid']:NULL;

	if(kc_validate($oid,2)){
		$where=" and oid={$oid}";
	}elseif(kc_validate($oid,3)){
		$where=" and oid in ({$oid})";
	}
	//status 状态
	$status=kc_val($attrib,'status');
	if(kc_validate($status,2)){
		$where.=" and nstatus={$status}";
	}elseif(kc_validate($status,3)){
		$where.=" and nstatus in ({$status})";
	}


	$s='';
	if($data=$king->db->getRows("select oid,ono,kname,nnumber,ntotal,ndate,kcontent,nstatus,nexpress from %s_orders where userid={$userid} $where order by oid desc",1,0,10)){

		foreach($data as $rs){
			$tmp=new KC_Template_class;
			$tmp->assign('oid',$rs['oid']);
			$tmp->assign('no',$rs['ono']);
			$tmp->assign('title',htmlspecialchars($rs['kname']));
			$tmp->assign('ntotal',$rs['ntotal']);
			$tmp->assign('total',number_format($rs['ntotal'],2));
			$tmp->assign('nexpress',$rs['nexpress']);
			$tmp->assign('express',number_format($rs['nexpress'],2));
			$tmp->assign('nalltotal',($rs['nexpress']+$rs['ntotal']));
			$tmp->assign('alltotal',number_format(($rs['nexpress']+$rs['ntotal']),2));
			$tmp->assign('number',$rs['nnumber']);
			$tmp->assign('date',$rs['ndate']);
			$tmp->assign('nstatus',$rs['nstatus']);
			$tmp->assign('status',$king->lang->get('portal/orders/status/s'.$rs['nstatus']));
			$tmp->assign('pay',"<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{URL:'../portal/cart.php',CMD:'payment',oid:{$rs['oid']}}\">".$king->lang->get('portal/cart/payment')."</a>");

			$array=unserialize($rs['kcontent']);
			$array_content=array();
			foreach($array as $key => $val){
				list($listid,$kid)=explode('-',$key);

				$array_content[]=array('listid'=>$listid,'kid'=>$kid,'number'=>$val);
			}

			$tmp->assign('content',$array_content);
			$s.=$tmp->output($inner);
		}


	}else{
		$s='<p class="k_err">'.$king->lang->get('portal/tip/notorders').'</p>';
	}




	return $s;
}
/**
	对king:portal.TAG的解析
	@param
	@return
*/
private function tag_tag($inner,$attrib){
	global $king;

	$number=kc_val($attrib,'number',30);
	$number=kc_validate($number,2) ? $number : 30;

	$skip=kc_val($attrib,'skip',0);
	$skip=kc_validate($skip,2) ? $skip : 0;

	$whereArray=array();

	$iscommend=kc_val($attrib,'iscommend');
	if(kc_validate($iscommend,2)){
		$whereArray[]="iscommend=". ($iscommend ? 1:0);
	}

	$isbold=kc_val($attrib,'isbold');
	if(kc_validate($isbold,2)){
		$whereArray[]="isbold=". ($isbold ? 1:0);
	}

	$orderby=isset($attrib['orderby']) ? ' ORDER BY '.$attrib['orderby'] : ' ORDER BY norder desc,kid desc';
	$where= $whereArray ? 'where '.implode(' and ',$whereArray):'';
	$limit='limit '.$skip.','.$number;

	if(!$res=$king->db->getRows("select ktag from %s_tag {$where} {$orderby} {$limit}"))
		return False;


	$tmp=new KC_Template_class();

	$array=array('ktag','kimage','kkeywords','kdescription','kcolor','nsize');
	$s='';
	foreach($res as $rs){

		$tag=$this->infoTag($rs['ktag']);
		foreach($array as $val){
			$tmp->assign(substr($val,1),$tag[$val]);
		}
		$tmp->assign('type','tag');
		$tmp->assign('title',$tag['ktag']);
		$tmp->assign('hit',$tag['nhit']);
		$tmp->assign('bold',$tag['isbold']);
		$tmp->assign('commend',$tag['iscommend']);
		$tmp->assign('path',$this->pathTag($tag['ktag']));

		$s.=$tmp->output($inner);

	}

	return $s;
}

/**
	对king:portal.comment的解析
	Code By: CiBill
	@param
	@return
*/
private function tag_comment($inner,$attrib){
	global $king;
	//读取数量
	$number=kc_val($attrib,'number',30);
	$number=kc_validate($number,2) ? $number : 30;
	//跳过条数
	$skip=kc_val($attrib,'skip',0);
	$skip=kc_validate($skip,2) ? $skip : 0;
	//查询条件
	$whereArray=array();
	$modelid=kc_val($attrib,'modelid');//modelid
	if(!kc_validate($modelid,2)){//如果没有modelid传入，则通过listid获取modelid
		$listid=kc_val($attrib,'listid');//listid
		if(kc_validate($listid,2)){//listid为数字时，读取单个modelid
			if($list=$king->portal->infoList($listid)){
				$modelid=$list['modelid'];
				$whereArray[]="modelid=$modelid";
			}else{
				return false;
			}
		}elseif(kc_validate($listid,3)){
			$listid=explode(',',$listid);
			$modelid=array();
			foreach($listid as $val){
				if($list=$king->portal->infoList($val)){
					$modelid[]=$list['modelid'];
				}
			}
			if($modelid){
				$modelid=implode(',',$modelid);
				$whereArray[]="modelid in ($modelid)";
			}else{
				return false;
			}
		}
	}
	$kid=kc_val($attrib,'kid');//文章id
	if(kc_validate($kid,2)){
		$whereArray[]="kid=$kid";
	}elseif(kc_validate($kid,3)){
		$whereArray[]="kid in ($kid)";
	}
	
	$orderby=isset($attrib['orderby']) ? ' ORDER BY '.$attrib['orderby'] : ' ORDER BY cid desc';
	$where= $whereArray ? 'where '.implode(' and ',$whereArray):'';
	$limit='limit '.$skip.','.$number;

	$tmp=new KC_Template_class();
	/*if($skip==0 && $number==30 && kc_validate($kid,2) && kc_validate($modelid,2)){
		$comment=$king->portal->infoComment($modelid,$kid);
		if(!$comment)return false;
	}else*/
	if(!$comment=$king->db->getRows("select * from %s_comment {$where} {$orderby} {$limit}")){
		return false;
	}
	$s='';
	foreach($comment as $rs){
		$tmp->assign('id',$rs['cid']);
		$tmp->assign('kid',$rs['kid']);
		$tmp->assign('modelid',$rs['modelid']);
		$tmp->assign('username',$rs['username']);
		$content=$rs['kcontent'];
		if(substr($content,0,7)=='[quote]'){
			$rid=intval(substr($content,7,10));
			if($r=$king->db->getRows_One("select * from %s_comment where cid=$rid")){
				$r['kcontent']=preg_replace("/\[quote].*\[\/quote]/siU",'',$r['kcontent']);
				$ypost="Originally posted by <i><b>".($r['username']!=''?$r['username']:'软件村网友')."</b></i> at ".kc_formatdate($r['ndate'],'Y-m-d').":<br>";
				$include="<table border=0 width='100%' cellspacing=1 cellpadding=10 bgcolor='#cccccc'><tr><td width='100%' bgcolor='#FFFFFF' style='word-break:break-all'>".$ypost.$r['kcontent']."</td></tr></table>";
				$content=str_replace("[quote]".$rid."[/quote]",$include,$content);
			}
		}
		$tmp->assign('content',$content);
		$tmp->assign('ip',long2ip($rs['nip']));
		$tmp->assign('date',$rs['ndate']);
		$s.=$tmp->output($inner);
	}
	return $s;
}


}//!portal_class

?>