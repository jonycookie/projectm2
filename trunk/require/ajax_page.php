<?php
class pager{
	/**
	* 用于标识分页的关键字
	* @var string
	* @access private
	*/
    var $keyword;
    
	/**
	* 分页的基本链接
	* @var string
	* @access private
	*/
    var $baseLink;
    
	/**
	* 总页数
	* @var integer
	* @access private
	*/
    var $pageNum;
    
	/**
	* 每页显示条数
	* @var integer
	* @access private
	*/
    var $pageSize;
   
	/**
	* 总条数
	* @var integer
	* @access private
	*/
    var $total;
    
	/**
	* 当前页号
	* @var integer
	* @access private
	*/
    var $currPageNo;
    
	/**
	* 输入框的class属性
	* @var string
	* @access private
	*/
    var $class;
    
    /**
    * 分页样式
    * @var integer
    * @access private
    */
    var $style;
        
	/**
	* 用于数据库查询的limit
	* @var string
	* @access private
	*/
    var $pageZone;

	/**
	* 构造函数
	* @param integer $total 总条数
	* @param integer $pageSize 每页显示条数
	* @param string $query 查询字符串
	* @param string $class 输入框样式
	* @param integer $style 分页样式 
	* @return void 
	* @access public
	*/
    function pager($total, $pageSize, $query, $class="input", $style=null)
    {
    	preg_match("/\?([^&]+)$/", $query, $m);
        if (preg_match("/&?([^&]+)$/", $query, $m))
        {
            $this->keyword = $m[1];
            
        }
        else
        {
            return false;
        }
        $this->baseLink =  $query;
            	//die($this->baseLink);
        $this->total = intval($total);
        $this->pageSize = intval($pageSize);

        if ($this->pageSize <= 0)
        {
            return false;
        }
        $this->pageNum = ceil($this->total/$this->pageSize);
        $this->setClass($class);
        $this->setStyle($style);
        $this->setCurrPageNo();
    }
    
    /**
    * 对字符串各段进行urlencode
    *
    * @param string $string 要编码的字符串
    * @return string 结果
    * @access private
    */
	function urlEncode($string)
	{
		parse_str($string, $out);
		foreach ($out as $k=>$v)
		{
			if ($k != $this->keyword)
			{
				$out[$k] = $k . "=" . urlencode($v);	
			}
			else 
			{
				$out[$k] = $k;	
			}
		}
		return 	implode("&", $out);
	}	
    
	/**
	* 设置输入框样式
	* @param $class
	* @return void 
	* @access private
	*/
    function setClass($class)
    {
        $this->class = $class;
    }
    
    /** 
    * 设定当前页码显示样式
    * @param integer $style
    * @return void
    * @access private
    */
    function setStyle($style)
    {
    	$this->style = intval($style);	
    }

	/**
	* 设定当前页码
	* @return void
	* @access private
	*/
    function setCurrPageNo()
    {
        $pageNo = GetGP("page");
        if ($pageNo == null)
        {
            $pageNo = 1;
        }
        else
        {
            $pageNo = intval($pageNo);
            if ($pageNo < 1 ) $pageNo = 1;
            if ($pageNo > $this->pageNum) $pageNo = $this->pageNum;
        }
        $this->currPageNo = $pageNo;
    }

	/**
	* 得到页码显示
	* @return string 页码显示 
	* @access public
	*/
    function getPageSet()
    {
    	$page = array();
        $page["begin"] = "<form action=\"\" onsubmit=\"getinfo('".$this->baseLink."',this.page.value);return false\" style=\"display:inline\">";
        $page["input"] = "输入页数 <input type=\"text\" name=\"page\" class=\"{$this->class}\" style=\"width:35px\" onchange=\"getinfo(this.page.value);\" />  ";
        $page["no"] = "第 {$this->currPageNo} 页, 共 {$this->pageNum} 页 |  ";
        $page["first"] = ($this->pageNum > 1) ? "<a href=\"javascript:void(0);\" onclick=\"getinfo('".$this->baseLink."','1'); \" target=\"_self\">首页</a>  ":"首页  ";
        $page["pre"] = ($this->currPageNo - 1 > 0)?"| <a href=\"javascript:void(0);\" onclick=\"getinfo('".$this->baseLink."','". ($this->currPageNo - 1) ."'); \"  target=\"_self\">上一页</a> ":"| 上一页 ";
        $page["next"] = ($this->currPageNo + 1 <= $this->pageNum)?"|  <a href=\"javascript:void(0);\" onclick=\"getinfo('".$this->baseLink."','". ($this->currPageNo + 1) ."'); \"  target=\"_self\">下一页</a> ":"| 下一页 ";
        $page["last"] = ($this->pageNum > 1)?"|<a href=\"javascript:void(0);\" onclick=\"getinfo('".$this->baseLink."','". ($this->pageNum) ."'); \"  target=\"_self\">尾页</a>":"| 尾页";
        $page["end"] = "</form>";

        //供sql查询用的limit
		$this->pageZone = " LIMIT " . ($this->currPageNo - 1) * $this->pageSize . "," . $this->pageSize;    

        //构造页码显示
        switch ($this->style)
        {
        	case "1": 
        		unset($page["input"]);	
        		break;
        	case "2":
        		unset($page["input"]);
        		unset($page["no"]);
        		break;
        }
        $string = implode("", $page);
        
        return $string;
    }

	/**
	* 供静态调用的函数
	* @param integer $total 总条数
	* @param integer $pageSize 每页显示条数
	* @param string $query 查询字符串
	* @param string $class 输入框样式
	* @param string $style 分页样式, 为1则无输入框，为2则无输入框和页码（当前面，总页数)
	* @return string 显示页码 
	* @access public
	* @see getPageSet()
	*/
    function setPager($total, $pageSize, $query, $class="input", $style = null)
    {
        $pager = new pager($total, $pageSize, $query, $class, $style);
        define("PAGE_ZONE", " LIMIT " . ($pager->currPageNo - 1) * $pager->pageSize . "," . $pager->pageSize);
        define("CURR_PAGE_NO", $pager->currPageNo);
        return $pager->getPageSet();
    }
}

?>