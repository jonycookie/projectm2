<?php
require('global.php');

class skin_cache
{
	function skin_cache()
	{
		global $icebb;
		
		$this->global		= new skin_global;
	}

	function show_caches($caches=array())
	{
		global $icebb;
		
		$count				= count($caches);
		
		$code				= $this->global->header();
		$code			   .= <<<EOF
<script type='text/javascript'>
<!--
function _check_col(colname,num)
{
	f								= document.adminfrm;

	for(i=0;i<f.elements.length;i++)
	{
		f.elements[i].checked		= f.cache_all.checked;
	}
}
//-->
</script>

<div class='borderwrap'>
	<h3>{$icebb->lang['manage_caches']}</h3>
	<form action='index.php' method='post' name='adminfrm'>
	<input type='hidden' name='s' value='{$icebb->adsess['asid']}' />
	<input type='hidden' name='act' value='cache' />
	<table width='100%' cellpadding='2' cellspacing='1' border='0'>
EOF;

		foreach($caches as $c)
		{
			$code		  .= <<<EOF
		<tr>
			<td class='row2' width='1%'>
				<input type='checkbox' name='cache[{$c['id']}]' value='1' id='cache_{$c['id']}' />
			</td>
			<td class='row1' width='75%'>
				<label for='cache-{$c['id']}'><strong>{$c['desc']}</strong></label>
			</td>
			<td class='row2' style='text-align:right'>
				<a href='{$icebb->base_url}act=cache&amp;func=view&amp;key={$c['name']}'>View</a> &middot;
				<a href='{$icebb->base_url}act=cache&amp;func=recache&amp;key={$c['name']}'>Rebuild</a>
			</td>
		</tr>

EOF;
		}

		$code			   .= <<<EOF
		<tr>
			<td class='row2'>
				<input type='checkbox' name='cache_all' onclick="_check_col('cache',{$count})" />
			</td>
			<td class='buttonstrip' colspan='2'>
				<input type='submit' name='recache_selected' value='Rebuild Selected Caches' class='button' />
			</td>
		</tr>
	</table>
	</form>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
	
	function show_cache($cache,$data)
	{
		global $icebb;

		$code				= $this->global->header();
		$code			   .= <<<EOF
<div class='borderwrap'>
	<h3>View Cache: {$cache['desc']}</h3>

EOF;
		$code			  .= $this->do_cache_array($data);
		$code			   .= <<<EOF
	</table>
</div>

EOF;
		$code			   .= $this->global->footer();
		
		return $code;
	}
	
	function do_cache_array($ca)
	{
		global $icebb;
		
		$code			   .= <<<EOF
	<table width='100%' cellpadding='2' cellspacing='1' border='0'>

EOF;

		foreach($ca as $k => $v)
		{
			$code		   .= <<<EOF
		<tr>
			<td class='row2' width='20%'>
				<strong>{$k}</strong>
			</td>
			<td class='row1'>
				<div style='width:100%;overflow:auto'>

EOF;

			if(is_array($v))
			{
				$code	  .= $this->do_cache_array($v);
			}
			else {
				$code	  .= $v;
			}

			$code		   .= <<<EOF
				</div>
			</td>
		</tr>

EOF;
		}

		$code			   .= "\t</table>\n";
		
		return $code;
	}
}
?>
