<?php if (!defined('NOVA_RUN')){echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";exit();} ?>
<?php if ($template_hook=='start'){ ?>
<?php }elseif ($template_hook=='1'){ ?>
		<!-- login form -->
		<table class="forum-board-forum-head" cellpadding="0" cellspacing="0">
			<tr><td class="forum-topic-subject"><?php echo $lang['login_title']; ?></td></tr>
			<tr><td class="forum-index-stats-sub"> </td></tr>
		</table>
		<table class="forum-index" cellpadding="0" cellspacing="0">
			<tr><td class="forum-index-stats-header forum-index-top"> </td></tr>
			<tr><td class="forum-jump-content forum-index-top forum-index-bottom">
				<form name="login" method="post" action="<?php echo nova_link("index.php?page=login", "login"); ?>">
					<?php echo $lang['login_name']; ?><br />
					<input type="text" name="name" size="18" /><br />
					<?php echo $lang['login_pass']; ?><br />
					<input type="password" name="password" size="18" /><br />
					<input type="checkbox" class="checkbox" id="remember" name="remember" /><label for="remember"><?php echo $lang['login_remember']; ?></label>
					<br /><br />
					<a href="<?php echo nova_link("index.php?page=password", "password"); ?>"><?php echo $lang['login_forgot']; ?></a>
			</td></tr>
			<tr><td class="forum-index-stats-header forum-index-top" style="text-align: center;">
					<input type="hidden" name="referer" value="<?php echo "$referer"; ?>" />			
					<input type="hidden" name="token_id" value="<?php echo "$token_id"; ?>" />
					<input type="hidden" name="<?php echo "$token_name"; ?>" value="<?php echo "$token"; ?>" />
					<input type="submit" class="submit-button img-submit" value="<?php echo $lang['button_login']; ?>" />
				</form>
			</td></tr>
		</table>
		<table class="forum-index-forum-footer" cellpadding="0" cellspacing="0">
			<tr><td class="forum-index-forum-footer-contents"> </td></tr>
		</table>
<?php }elseif ($template_hook=='end'){ ?>
<?php } ?>