<?php
/*
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 *	================================
 *	Plugin Name: Calendar/日历
 *	Plugin URI: http://www.iDreamSoft.cn
 *	Description: Calendar/日历
 *	Version: 1.0
 *	Author: 枯木
 *	Author URI: http://G.iDreamSoft.cn
 *	TAG:<!--{iCMS:plugins name='calendar'}-->
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_plugins_calendar($vars="",$iCMS){
	$y=$_GET['y'];
	$m=$_GET['m'];
	list($nowy,$nowm) = explode('-',get_date('','Y-m'));
	$calendar = array();
	$calendar['year'] = !$y ? $nowy : $y;
	$calendar['month'] = !$m ? $nowm : $m;
	$calendar['days']	  = calendar($calendar['month'],$calendar['year'],$iCMS);
	$calendar['nextmonth'] 	  = ($calendar['month']+1)>12 ? 1 : $calendar['month']+1;
	$calendar['premonth'] 	  = ($calendar['month']-1)<1 ? 12 : $calendar['month']-1;
	$calendar['nextyear'] 	  = $calendar['year']+1;
	$calendar['preyear'] 	  = $calendar['year']-1;
	$calendar['cur_date'] = get_date('','Y n.j D');
	$iCMS->value('SELF',__SELF__);
	$iCMS->value('calendar',$calendar);
	$iCMS->output('calendar',$vars['tpl'],'file:');
}
function calendar($m,$y,$iCMS){
	$today		= get_date('','j');
	$weekday	= get_date(mktime(0,0,0,$m,1,$y),'w');
	$totalday	= Days4month($y,$m);
	$start		= strtotime($y.'-'.$m.'-1');
	$end		= strtotime($y.'-'.$m.'-'.$totalday);
	$postdates	= '';
	$rs=$iCMS->db->getArray("SELECT A.*,C.name,C.dir FROM `#iCMS@__article` AS A,#iCMS@__catalog AS C WHERE visible='1' AND A.cid=C.id AND C.ishidden='0' AND pubdate>='$start' AND pubdate<='$end'");
	for ($i=0;$i<count($rs);$i++){
			$postdates .= ($postdates ? ',' : '').get_date($rs[$i]['pubdate'],'Y-n-j');
	}
	$br = 0;
	$days = '<tr>';
	for ($i=1; $i<=$weekday; $i++) {
		$days .= '<td>&nbsp;</td>';
		$br++;
	}
	for ($i=1; $i<=$totalday; $i++) {
		$br++;
		$td = (strpos(",$postdates,",','.$y.'-'.$m.'-'.$i.",") !== false) ? '<a href="index.php?date='.$y.'_'.$m.'_'.$i.'"><b>'.$i.'</b></a>' :$i;
		$days .= '<td>'.$td.'</td>';
		if ($br>=7) {
			$days .= '</tr><tr>';
			$br = 0;
		}
	}
	if ($br!=0) {
		for ($i=$br; $i<7;$i++) {
			$days .= '<td>&nbsp;</td>';
		}
	}
	return $days;
}
function Days4month($year,$month){
	if (!function_exists('cal_days_in_month')) {
		return date('t',mktime(0,0,0,$month+1,0,$year));
	} else {
		return cal_days_in_month(CAL_GREGORIAN,$month,$year);
	}
}
?>