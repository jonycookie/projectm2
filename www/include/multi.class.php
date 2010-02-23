<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class multi {
	/**
	* config ,public
	*/
	var $page_name="page";//page标签，用来控制url页。比如说xxx.php?page=2中的page
	var $is_ajax=false;//是否支持AJAX分页模式 

	/**
	* private
	*
	*/ 
	var $pagebarnum=8;//控制记录条的个数。
	var $totalpage=0;//总页数
	var $ajax_action_name='';//AJAX动作名
	var $nowindex=1;//当前页
	var $url="";//url地址头
	var $offset=0;
	var $config='';
	var $dir='';
	var $lang='';
	/**
	 * PHP4 constructor
	 *
	 * @param unknown_type $array
	 * @return page
	 */
	function multi($array){
		$this->__construct($array);
	}
	/**
	* constructor构造函数
	*
	* @param array $array['total'],$array['perpage'],$array['pn'],$array['unit'],$array['nowindex'],$array['url'],$array['ajax'],$array['pnName']...
	*/
	function __construct($array){
		if(is_array($array)){
	 		!array_key_exists('total',$array) &&$this->error(__FUNCTION__,'need a param of total');	 		
	 		$this->total= intval($array['total']);
	 		$nowindex	= array_key_exists('nowindex',$array)?intval($array['nowindex']):'';
		    $url		= array_key_exists('url',$array)?$array['url']:'';
		}else{
			 $this->total= $array;
			 $nowindex	= '';
			 $url		= '';
		}
		$this->perpage	= array_key_exists('perpage',$array)?intval($array['perpage']):10;
		
		if((!is_int($this->total))||($this->total<0))
			$this->error(__FUNCTION__,$this->total.' is not a positive integer!');
		
		if((!is_int($this->perpage))||($this->perpage<=0))
			$this->error(__FUNCTION__,$this->perpage.' is not a positive integer!');
		
		$this->totalpage=ceil($this->total/$this->perpage);
		$GLOBALS['cpn'] && $this->totalpage=$GLOBALS['cpn'];
		if($this->totalpage<1) return; 

		if(!empty($array['page_name']))$this->set('page_name',$array['page_name']);//设置pagename
			
		global $iCMS;
		$this->config	= $iCMS->config;
		$this->dir		= $iCMS->dir;
		$this->lang		= $iCMS->language('page');
		$this->rewrite	= $iCMS->rewrite;	
		$iCMS->pagesize=$this->totalpage;		
		$this->unit	= array_key_exists('unit',$array)?$array['unit']:$this->lang['sql'];
		$this->_set_nowindex($nowindex);//设置当前页
		$this->_set_url($url);//设置链接地址
		$this->nowindex=min($this->totalpage,$this->nowindex);
		$this->offset=($this->nowindex-1<0?0:$this->nowindex-1)*$this->perpage;
		if(!empty($array['ajax']))$this->open_ajax($array['ajax']);//打开AJAX模式
	}
	/**
	* 设定类中指定变量名的值，如果改变量不属于这个类，将throw一个exception
	*
	* @param string $var
	* @param string $value
	*/
	function set($var,$value){
		if(in_array($var,get_object_vars($this)))
	 		$this->$var=$value;
		else
			$this->error(__FUNCTION__,$var." does not belong to PB_Page!");
	}
	function get($var){
		if(in_array($var,get_object_vars($this)))
	 		return $this->$var;
		else
			$this->error(__FUNCTION__,$var." does not belong to PB_Page!");
	}
	
	/**
	* 打开倒AJAX模式
	*
	* @param string $action 默认ajax触发的动作。
	*/
	function open_ajax($action){
		$this->is_ajax=true;
		$this->ajax_action_name=$action;
	}
	
	
	/**
	* 获取显示"下一页"的代码
	* 
	* @param string $style
	* @return string
	*/
	function next_page($style='next_page',$target='_self'){
		if($this->nowindex<$this->totalpage){
			return $this->_get_link($this->_get_url($this->nowindex+1),$this->lang['next'],$style,$target);
		}
		return '<span class="'.$style.'">'.$this->lang['next'].'</span>';
	}

	/**
	* 获取显示“上一页”的代码
	*
	* @param string $style
	* @return string
	*/
	function pre_page($style='pre_page',$target='_self'){
		if($this->nowindex>1){
			return $this->_get_link($this->_get_url($this->nowindex-1),$this->lang['prev'],$style,$target);
		}
		return '<span class="'.$style.'">'.$this->lang['prev'].'</span>';
	}

	/**
	* 获取显示“首页”的代码
	*
	* @return string
	*/
	function first_page($style='index_page',$target='_self'){
		if($this->nowindex==1){
	  		return '<span class="'.$style.'">'.$this->lang['index'].'</span>';
		}
		return $this->_get_link($this->_get_url(1),$this->lang['index'],$style,$target);
	}

	/**
	* 获取显示“尾页”的代码
	*
	* @return string
	*/
	function last_page($style='last_page',$target='_self'){
		if($this->nowindex==$this->totalpage){
	 		 return '<span class="'.$style.'">'.$this->lang['last'].'</span>';
		}
		return $this->_get_link($this->_get_url($this->totalpage),$this->lang['last'],$style,$target);
	}

	function nowbar($style='',$nowindex_style='page_nowindex',$target='_self'){
		$plus=ceil($this->pagebarnum/2);
		if($this->pagebarnum-$plus+$this->nowindex>$this->totalpage)
			$plus=($this->pagebarnum-$this->totalpage+$this->nowindex);
		$begin=$this->nowindex-$plus+1;
		$begin=($begin>=1)?$begin:1;
		$return='';
		for($i=$begin;$i<$begin+$this->pagebarnum;$i++){
			if($i<=$this->totalpage){
				if($i!=$this->nowindex)
		    		$return.=$this->_get_text($this->_get_link($this->_get_url($i),$i,$style,$target));
				else 
		    	$return.=$this->_get_text('<strong class="'.$nowindex_style.'">'.$i.'</strong>');
			}else{
				break;
			}
			$return.="\n";
		}
		unset($begin);
		return $return;
	}
	
	/**
	* 获取显示跳转按钮的代码
	*
	* @return string
	*/
	function select($style='page_select'){
		$return='<select class="'.$style.'" name="Page_Select" onchange="window.location.href=this.value">';
		for($i=1;$i<=$this->totalpage;$i++){
			$url = $this->_get_url($i);
			$this->rewrite && $url = rewrite($url,'','');			
			if($i==$this->nowindex){
				$return.='<option value="'.$url.'" selected>'.$i.'</option>';
			}else{
				$return.='<option value="'.$url.'">'.$i.'</option>';
			}
		}
		unset($i);
		$return.='</select>';
		return $return;
	}
	//文字 说明
	function bartext($style='bartext'){
		return '<span class="'.$style.'">'.$this->lang['other'].$this->total.$this->unit.'，'.$this->perpage.$this->unit.'/'.$this->lang['unit'].' '.$this->lang['other'].$this->totalpage.$this->lang['unit'].'</span>';
	}

	/**
	* 获取mysql 语句中limit需要的值
	*
	* @return string
	*/
	function offset(){
		return $this->offset;
	}

	/**
	* 控制分页显示风格（你可以增加相应的风格）
	*
	* @param int $mode
	* @return string
	*/
	function show($mode=0){
		switch ($mode){
			case '1':
				return $this->pre_page().$this->nowbar().$this->next_page().$this->lang['di'].$this->select().$this->lang['unit'];
				break;
			case '2':
				return $this->first_page().$this->pre_page().$this->lang['format_left'].$this->lang['di'].$this->nowindex.$this->lang['unit'].$this->lang['format_right'].$this->next_page().$this->last_page().$this->lang['di'].$this->select().$this->lang['unit'];
				break;
			case '3':
				return $this->first_page().$this->pre_page().$this->nowbar().$this->next_page().$this->last_page();
				break;
			case '4':
				return $this->nowbar();
				break;
			default:
				return $this->first_page().$this->pre_page().$this->nowbar().$this->next_page().$this->last_page().$this->bartext();
				break;
		}
	}
/*----------------private function (私有方法)-----------------------------------------------------------*/
	/**
	* 设置url头地址
	* @param: String $url
	* @return boolean
	*/
	function _set_url($url=""){
		empty($url) && $url=$_SERVER["REQUEST_URI"];
		$parse_url=parse_url($url);
		if($this->config['ishtm'] && stristr($parse_url["path"],'.php')===false){
			$this->url=$url;
		}else{
			if($this->config['customlink'] && strrpos($url,$this->config['rewrite']['ext'])!==false){
				$url=substr($url,0,strrpos($url,$this->config['rewrite']['ext']));
			}
			if(in_array($this->config['customlink'],array('custom','2')) && $this->config['rewrite']['dir']!='.php?'){
				$url=preg_replace("/^".preg_quote($this->dir,'/')."(\w+)$/i",$this->dir.'\\1.php',$url);
				$url=preg_replace("/^".preg_quote($this->dir,'/')."(\w+)".preg_quote($this->config['rewrite']['dir'],'/')."/i",$this->dir.'\\1.php?',$url);
			}
			$urlArray=parse_url($url);
			$QS=$urlArray["query"];
			if($this->config['ishtm']){
				parse_str($QS, $_QSa);
				$_QSa['page']=(int)$_QSa['page'];
				$QS=http_build_query($_QSa);
			}
			strpos($urlArray["path"],'.php')===false && $url=$_SERVER['PHP_SELF'];
			if($QS){
				if($this->config['rewrite']['split'] && $this->config['customlink']){
					if(count($_GET)<3){
						$pattern=$this->page_name.$this->config['rewrite']['split'];
					}else{
						$pattern=$this->config['rewrite']['split'].$this->page_name.$this->config['rewrite']['split'];
					}
				}else{
					$pattern=$this->page_name."=";
				}
				$QS=str_replace($pattern.$this->nowindex,"",$QS);
				$url=str_replace($urlArray["query"],$QS,$url);
 				$last=substr($url,-1);
 				if($last=='?'||$last=='&'){
     				$url.=$this->page_name;
 				}else{
    				$url.='&'.$this->page_name;
 				}
			}else{
				$url.="?".$this->page_name;
			}
			$this->url=$url."=";
		}
	}
	
	/**
	* 设置当前页面
	*
	*/
	function _set_nowindex($nowindex){
		if(empty($nowindex)){
			//系统获取
			if(isset($_GET[$this->page_name])){
				$this->nowindex=intval($_GET[$this->page_name]);
			}
		}else{
	  		//手动设置
			$this->nowindex=intval($nowindex);
		}
	}

	
	/**
	* 为指定的页面返回地址值
	*
	* @param int $pageno
	* @return string $url
	*/
	function _get_url($pageno=1){
		$url=$this->url.$pageno;
		$this->config['ishtm'] && $url.=$this->config['htmlext'];
		return $url;
	}

	/**
	* 获取分页显示文字，比如说默认情况下_get_text('<a href="">1</a>')将返回[<a href="">1</a>]
	*
	* @param String $str
	* @return string $url
	*/ 
	function _get_text($str){
		return $this->lang['format_left'].$str.$this->lang['format_right'];
	}

	
	/**
	* 获取链接地址
	*/
	function _get_link($url,$text,$style='',$target=''){
		$style=empty($style)?'':'class="'.$style.'"';
		empty($target) && $target='_self';
		if($this->is_ajax){
	  		//如果是使用AJAX模式
			return '<a '.$style.' href="javascript:'.$this->ajax_action_name.'(\''.$url.'\')">'.$text.'</a>';
		}else{
			return '<a '.$style.' href="'.$url.'" target="'.$target.'">'.$text.'</a>';
		}
	}
	
	
	/**
	* 出错处理方式
	*/
	function error($function,$errormsg){
		 die('Error in file <b>'.__FILE__.'</b> ,Function <b>'.$function.'()</b> :'.$errormsg);
	}
}
?>