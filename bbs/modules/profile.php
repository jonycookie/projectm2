<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3
//******************************************************//
// profile module
// $Id: profile.php 753 2007-02-16 16:56:55Z mutantmonkey0 $
//******************************************************//

class profile
{
	function run()
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		require('includes/classes/post_parser.php');
		$post_parser			= new post_parser;
		
		$this->html				= $icebb->skin->load_template('profile');
		$this->lang				= $std->learn_language('profile');
		
		$u						= $db->fetch_result("SELECT u.*,g.*,s.last_action,s.act,s.func,s.topic,s.forum,s.profile FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid LEFT JOIN icebb_session_data AS s ON u.id=s.user_id WHERE u.id='{$icebb->input['profile']}' ORDER BY s.last_action DESC");
		if($u['result_num_rows_returned']<=0 || $u['id']=='0')
		{
			$std->error($this->lang['member_not_exist']);
		}
		$this->u				= $u;
		
		$n						= sprintf($this->lang['view_profile_u'],$u['username']);
		$icebb->nav[]			= "<a href='{$icebb->base_url}profile={$u['id']}'>{$n}</a>";
		
		switch($icebb->input['func'])
		{
			case 'mail':
				$this->email();
				break;
			case 'warn':
				$this->warn();
				break;
			case 'get_uim_menu':
				$this->get_uim_menu();
				break;
			case 'xml':
				$this->userxml();
				break;
			default:
				$this->main();
				break;
		}
		
		//$u = $db->fetch_result("SELECT u.*,g.* FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE u.id='{$icebb->input['profile']}'");
		
		$icebb->skin->html_insert($this->output);
		$icebb->skin->do_output();
	}
	
	function main()
	{
		global $icebb,$config,$db,$std,$post_parser;
		
		//$u						= $db->fetch_result("SELECT u.*,g.*,s.last_action,s.act,s.func,s.topic,s.forum,s.profile FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid LEFT JOIN icebb_session_data AS s ON u.id=s.user_id WHERE u.id='{$icebb->input['profile']}'");
		//if($u['result_num_rows_returned']<=0 || $u['id']=='0')
		//{
		//	$std->error("This member does not exist!",1);
		//}
		
		$u						= $this->u;
		
		$u['siggie']			= $post_parser->parse(array('TEXT'=>$u['siggie'],'SMILIES'=>0,'BBCODE'=>1,'ME_TAG'=>0),$pdata);
		if(empty($u['siggie']))
		{
			$u['siggie']		= $this->lang['no_signature'];
		}
		
		if(empty($u['location']))
		{
			$u['location']		= $this->lang['no_info'];
		}
		
		if(empty($u['url']))
		{
			$u['url']			= $this->lang['no_info'];
		}
		else {
			$u['url']			= "<a href='{$u['url']}' class='url'>{$u['url']}</a>";
		}
		
		if($u['email_member'] == '1' || $icebb->user['user_group'])
		{
			$u['mail']			= "<br /><a href='{$icebb->base_url}profile={$icebb->input['profile']}&func=mail'>{$this->lang['send_email']}</a>";
		}

		if(empty($u['msn']))
		{
			$u['msn']			= $this->lang['no_info'];
		}
 		else {
			$u['msn']			= "<a href='msnim:chat?contact={$u['msn']}' class='url'>{$u['msn']}</a>";
		}

		if(empty($u['yahoo']))
		{
			$u['yahoo']			= $this->lang['no_info'];
		}
		else {
			$u['yahoo']			= "<a href='ymsgr:sendIM?{$u['yahoo']}' class='url'>{$u['yahoo']}</a>";
		}
		
		if(empty($u['aim']))
		{
			$u['aim']			= $this->lang['no_info'];
		}
		else {
			$u['aim']			= "<a href='aim:goim?screenname={$u['aim']}' class='url'>{$u['aim']}</a>";
		}
		
		if(empty($u['jabber']))
		{
			$u['jabber']		= $this->lang['no_info'];
		}
		
		// don't think we need this really...
		/*if($u['banned']	   == "1" || $u['user_group'] == "5")
		{
			$u['banned']	= "<font color='red'><b>User has been banned!</b></font>";
		}*/
		
		if(!empty($u['birthdate']))
		{
			$u['age']			= floor(gmdate("Y.md",time()+$std->get_offset())-gmdate("Y.md",$u['birthdate']+$std->get_offset()));
			$u['birthdate']		= gmdate("F j, Y",$u['birthdate']);
		}
		else {
			$u['birthdate']		= $this->lang['no_info'];
		}
		
		switch($u['gender'])
		{
			case 'm':
				$u['gender']	= $this->lang['male'];
				break;
			case 'f':
				$u['gender']	= $this->lang['female'];
				break;
			default:
				$u['gender']	= $this->lang['no_info'];
				break;
		}
		
		$u['joindate_orig']		= $u['joindate'];
		$u['joindate']			= gmdate($icebb->user['date_format'],$u['joindate']+$std->get_offset());
     
		if($u['last_action']<(time()-(15*60)))
		{
			$u['blocation']		= $this->lang['offline'];
		}
		else if(!empty($u['topic']))
		{
			$u['blocation']		= sprintf($this->lang['viewing_topic'],"{$icebb->base_url}topic={$u['topic']}");
		}
		else if(!empty($u['forum']))
		{
			$u['blocation']		= sprintf($this->lang['viewing_forum'],"{$icebb->base_url}forum={$u['forum']}");
		}
		else if(!empty($u['profile']))
		{
			$u['blocation']		= sprintf($this->lang['viewing_profile'],"{$icebb->base_url}profile={$u['profile']}");
		}
		else if($u['act']=='ucp')
		{
			$u['blocation']		= $this->lang['viewing_ucc'];
		}
		else if($u['act']=='pm')
		{
			$u['blocation']		= $this->lang['viewing_pm'];
		}
		else if($u['act']=='misc' && $u['func']=='leaders')
		{
			$u['blocation']		= $this->lang['viewing_forum_leaders_list'];
		}
		else if($u['act']=='members')
		{
			$u['blocation']		= $this->lang['viewing_member_list'];
		}
		else {
			$u['blocation']		= $this->lang['viewing_forum_home'];
		}
		
		if(!empty($u['last_visit']))
		{
			$u['last_visit_formatted']= $std->date_format($icebb->user['date_format'],$u['last_visit']);
		}
		else {
			$u['last_visit_formatted']= $this->lang['none'];
		}
		
		// days registered
		$u['days_registered']= (time()-$u['joindate_orig'])/(3600*24);
		$u['posts_per_day']	= round($u['posts']/$u['days_registered'],2);
     
		$this->output		= $this->html->profile_view($u);
	}
	
	function email()
	{
		global $icebb,$config,$db,$std;
		
		$icebb->nav[]				= $icebb->lang['send_mail'];
		
		if($icebb->user['id']=='0')
		{
			$std->error($this->lang['unauthorized'],1);
		}
		
		//$u = $db->fetch_result("SELECT u.*,g.* FROM icebb_users AS u LEFT JOIN icebb_groups AS g ON u.user_group=g.gid WHERE u.id='{$icebb->input['profile']}'");
		$u = $this->u;
		
		if($u['email_member'] != '1' && $icebb->user['user_group'] != '1')
		{
			$std->error($this->lang['unauthorized']);
		}
		
		if(!$icebb->input['subject'] && !$icebb->input['body'])
		{
			$this->output	= $this->html->email($u);
		}
		else if(!$icebb->input['subject'] || !$icebb->input['body'])
		{
			$std->error($this->lang['no_fields_may_be_blank']);
		}
		else {
			$title		= str_replace('<#title#>',$icebb->input['subject'],$this->lang['mail_title']);
			
			$text		= $this->lang['mail_text'];
			$text		= str_replace('<#to#>',$u['username'],$text);
			$text		= str_replace('<#msg#>',$icebb->input['body'],$text);
			
			$headers	= array();
			$headers[]	= "From: {$icebb->user['email']}";
		
			$mail		= $std->send_mail($u['email'],$title,$text,$headers,true);
			
			if($mail)
			{
				$std->bouncy_bouncy($this->lang['message_sent'],$icebb->base_url.'profile='.$icebb->input['profile']);
			}
			else {
				$std->error($this->lang['mail_failed']);
			}
		}
	}
	
	function warn()
	{
		global $icebb,$db,$std;
		
		$icebb->nav[]			= $icebb->lang['warn'];
		
		if($icebb->user['g_is_mod']!='1')
		{
			$std->error($this->lang['unauthorized']);
		}
		
		$u						= $this->u;
		$curr_actions			= array();
		
		if(!empty($icebb->input['submit']))
		{
			$sets[]				= "warn_level='{$icebb->input['warn_level']}'";
		
			if($icebb->input['do_disable_pms']=='1')
			{
				$sets[]			= "disable_pm=1";
			}
			else {
				$sets[]			= "disable_pm=0";
			}
			
			if($icebb->input['do_disable_post']=='1')
			{
				$sets[]			= "disable_post=1";
			}
			else {
				$sets[]			= "disable_post=0";
			}
		
			if($icebb->input['do_suspend']=='1')
			{
				if($icebb->input['suspend_til2']=='days')
				{
					$temp_ban	= time()+(intval($icebb->input['suspend_til'])*86400);
				}
				else {
					$temp_ban	= time()+(intval($icebb->input['suspend_til'])*3600);
				}
				
				$sets[]			= "temp_ban={$temp_ban}";
			}
			else {
				$sets[]			= 'temp_ban=0';
			}
			
			if($icebb->input['do_ban']=='1')
			{
				$sets[]			= "user_group=5";
			}
			
			$set				= implode(',',$sets);
			$db->query("UPDATE icebb_users SET {$set} WHERE id='{$u['id']}'");
			
			$std->bouncy_bouncy($this->lang['warn_actions_taken'],$icebb->base_url."profile={$u['id']}");
		}
		else if(!empty($icebb->input['prune']))
		{
			if($icebb->input['prune']== 'allposts')
			{
				$db->query("DELETE FROM icebb_topics WHERE starter='{$u['username']}'");
				$db->query("DELETE FROM icebb_posts WHERE pauthorid='{$u['id']}'");
				$std->bouncy_bouncy($this->lang['warn_pruned'],$_SERVER['HTTP_REFERER']);
			}
		}
		else if(!empty($icebb->input['ban']))
		{
			if($icebb->input['ban']== 'inf')
			{
				$db->query("UPDATE icebb_users SET user_group=5 WHERE id='{$u['id']}' LIMIT 1");
				$std->bouncy_bouncy($this->lang['warn_banned'],$_SERVER['HTTP_REFERER']);
			}
		}
		
		// warning level
		for($pipon=0;$pipon<$u['warn_level'];$pipon++)
		{
			$u['warn_pips']	  .= "<macro:pip />";
		}
		
		// current actions
		if($u['disable_pm']		== '1')
		{
			$curr_actions['disable_pm']= " checked='checked'";
		}
		
		if($u['disable_post']	== '1')
		{
			$curr_actions['disable_post']= " checked='checked'";
		}
		
		if(!empty($u['temp_ban']))
		{
			$curr_actions['suspend']= " checked='checked'";
			$curr_actions['suspend_div']= " style='display:block !important'";
		}
		
		if($u['user_group']		== '5')
		{
			$curr_actions['ban']= " checked='checked'";
		}

		$this->output			= $this->html->warn($u,$curr_actions);
	}
	
	function get_uim_menu()
	{
		global $icebb,$db,$std;
		
		$u						= $this->u;
		
		$db->query("SELECT * FROM icebb_session_data WHERE user_id='{$u['id']}' AND last_action>".(time()-(15*60))."");
		if($db->get_num_rows()>=1)
		{
			$u['online_offline']= $this->lang['online'];
		}
		else {
			$u['online_offline']= $this->lang['offline'];
		}
		
		echo $this->html->get_uim_menu($u);
		exit();
	}

	function userxml()
	{
		global $icebb,$db,$std;
		
		$db->query("SELECT id,username,avatar,avtype,avsize,title,user_group,posts,joindate,siggie,location,gender,birthdate,interests,icq,msn,yahoo,aim,jabber,url,gmt,dst,date_format,last_visit,skinid,langid FROM icebb_users WHERE id='{$icebb->input['profile']}' LIMIT 1");
		$u					= $db->fetch_row();
		
		foreach($u as $k => $v)
		{
			$uxml		   .= "\t<user key='{$k}'>{$v}</user>\n";
		}
		
		@header("Content-type: application/xml");
		echo "<?xml version='1.0'?>\n<icebb>\n{$uxml}\n</icebb>";
		exit();
	}
}
?>
