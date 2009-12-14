<?php
require('global.php');

class skin_calendar
{
	function skin_calendar()
	{
		global $icebb,$global;

		$global				= new skin_global;
	}

	function layout($small_cal,$main)
	{
		global $icebb,$global;

		$code				= $global->header();
		$code			   .= <<<EOF
		<style type='text/css'>@import 'skins/<#SKIN#>/calendar.css';</style>

		<table width='100%' cellpadding='2' cellspacing='1' border='0' class='calendar-table'>
			<tr>
				<td width='300' valign='top' class='left'>
					{$small_cal}

					<div style='margin-top:6px'>
					<form action='{$icebb->base_url}act=calendar' method='post' name='jumper'>
						<fieldset>
							<legend>Jump to:</legend>
							<select name='jump' class='form_dropdown' onchange="document.jumper.submit()">
								<option value=''>--</option>
								<option value='today'>{$icebb->lang['today']}</option>
								<option value='tweek'>{$icebb->lang['tweek']}</option>
								<option value='tmonth'>{$icebb->lang['tmonth']}</option>
							</select>
						</fieldset>
					</form>
					</div>
				</td>
				<td rowspan='2' valign='top'>
					{$main}
				</td>
			</tr>
		</table>

EOF;
		$code			   .= $global->footer();

		return $code;
	}

function small_month($months,$years,$days)
{
global $icebb;

$code .= <<<EOF
<div class='borderwrap calendar calmonth small'>
	<h2 style='text-align:center'>
		<form action='{$icebb->base_url}' method='get' name='selector_small'>
			<input type='hidden' name='act' value='calendar' />
			<select name='month' class='form_dropdown' onchange="document.selector_small.submit()">
				{$months}
			</select>
			<select name='year' class='form_dropdown' onchange="document.selector_small.submit()">
				{$years}
			</select>
		</form>
	</h2>
	<table width='100%' cellpadding='0' cellspacing='1' border='0'>
		<tr>
			<th>{$icebb->lang['su_short']}</th>
			<th>{$icebb->lang['mo_short']}</th>
			<th>{$icebb->lang['tu_short']}</th>
			<th>{$icebb->lang['we_short']}</th>
			<th>{$icebb->lang['th_short']}</th>
			<th>{$icebb->lang['fr_short']}</th>
			<th>{$icebb->lang['sa_short']}</th>
		</tr>
		{$days}
	</table>
</div>

EOF;

return $code;
}

	function small_month_day($day,$class='')
	{
		global $icebb;

		if(!empty($class))
		{
			$e				= " {$class}";
		}

		if(empty($day))
		{
			$code			= <<<EOF
			<td class='row2 day_blank'><!-- --></td>

EOF;
		}
		else {
			$code			= <<<EOF
			<td class='row2{$e}'><a href='{$icebb->base_url}act=calendar&amp;func=day&amp;day={$day}'>{$day}</a></td>

EOF;
		}

		return $code;
	}

function small_month_week($days)
{
global $icebb;

$code .= <<<EOF
	<tr>
		{$days}
	</tr>

EOF;

return $code;
}

function month($months,$years,$days)
{
global $icebb;

$code .= <<<EOF
<div class='borderwrap calendar calmonth'>
	<h2 style='text-align:center'>
		<form action='{$icebb->base_url}' method='get' name='selector_large'>
			<input type='hidden' name='act' value='calendar' />
			<select name='month' class='form_dropdown' onchange="document.selector_large.submit()">
				{$months}
			</select>
			<select name='year' class='form_dropdown' onchange="document.selector_large.submit()">
				{$years}
			</select>
		</form>
	</h2>
	<table width='100%' cellpadding='2' cellspacing='1' border='0'>
		<tr>
			<th style='width:1% !important'>&nbsp;</th>
			<th>{$icebb->lang['sun_short']}</th>
			<th>{$icebb->lang['mon_short']}</th>
			<th>{$icebb->lang['tue_short']}</th>
			<th>{$icebb->lang['wed_short']}</th>
			<th>{$icebb->lang['thu_short']}</th>
			<th>{$icebb->lang['fri_short']}</th>
			<th>{$icebb->lang['sat_short']}</th>
		</tr>
		{$days}
	</table>
</div>

EOF;

return $code;
}

	function month_day($day,$other,$class='')
	{
		global $icebb;

		if(!empty($class))
		{
			$e				= " {$class}";
		}

		if(empty($day))
		{
			$code			= <<<EOF
			<td class='row2 day day_blank'><!-- --></td>

EOF;
		}
		else {
			$code			= <<<EOF
			<td class='row2 day{$e}'><a href='{$icebb->base_url}act=calendar&amp;func=day&amp;day={$day}'>{$day}</a></td>

EOF;
		}

		return $code;
	}

function month_week($days,$other)
{
global $icebb;

$code .= <<<EOF
	<tr>
		<th class='side'><a href='{$icebb->base_url}act=calendar&amp;func=week&amp;week={$other['weeknum']}'>&raquo;</a></th>
		{$days}
	</tr>

EOF;

return $code;
}

function week($months,$years,$days)
{
global $icebb;

$code .= <<<EOF
<div class='borderwrap calendar calweek'>
	<h2>{$icebb->lang['week_of']}</h2>
	{$days}
</div>

EOF;

return $code;
}

function week_day($day,$other)
{
global $icebb;

$code .= <<<EOF
<div class='weekday'>
<h3><a href='{$icebb->base_url}act=calendar&amp;func=day&amp;day={$day}'>{$other['day_of_week']}, {$other['month']} {$day}, {$other['year']}</a></h5>
<div class='pad'>{$other['events']}</div>
</div>

EOF;

return $code;
}

function day($bdays,$events,$other)
{
global $icebb;

$code .= <<<EOF
<div class='borderwrap calendar calday'>
	<h2>{$other['day_of_week']}, {$other['month']} {$other['day']}, {$other['year']}</h2>

EOF;

if(!empty($bdays))
{

$code .= <<<EOF
	<div class='pad' style='margin:2px'>
		<h3 style='text-align:left;font-size:80%'>{$icebb->lang['birthdays']}</h3>
		{$bdays}
	</div>

EOF;

}

if(!empty($events['ongoing']))
{

$code .= <<<EOF
	<div class='pad ongoing_events' style='margin:2px'>
		<ul>
			{$events['ongoing']}
		</ul>
	</div>
EOF;

}

$code .= <<<EOF
	<table width='100%' cellpadding='2' cellspacing='1' border='0' class='events-table'>
		{$events['html']}
	</table>
</div>

EOF;

return $code;
}

function day_ongoing_event($e)
{
global $icebb;

$code .= <<<EOF
<li><a href='{$icebb->base_url}act=calendar&amp;func=event&amp;eventid={$e['eid']}'>{$e['etitle']}</a></li>

EOF;

return $code;
}

function event_details($e,$other)
{
global $icebb;

$code .= <<<EOF
<div class='borderwrap'>
<h2 class='subtitle'>{$e['etitle']}</h2>
{$e['edesc']}
</div>

EOF;

return $code;
}

}
?>
