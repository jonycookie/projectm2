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
// attachment module
// $Id$
//******************************************************//

class attach
{
	function run()
	{
		global $icebb,$db,$std;
		
		$this->lang					= $std->learn_language('topics');
		
		$db->query("SELECT * FROM icebb_uploads WHERE uid='{$icebb->input['upload']}'");
		$u							= $db->fetch_row();
		
		if($db->get_num_rows()	   <= 0)
		{
			$std->error($this->lang['upload_not_found']);
			exit();
		}
		
		$u['upath']					= str_replace($icebb->settings['board_url'],$icebb->settings['board_path'],$u['upath']);
		
		if(!file_exists($u['upath']))
		{
			$std->error($this->lang['upload_not_found']);
			exit();
		}
		
		$mime_types['gif']			= array('image/gif','inline');
		$mime_types['jpg']			= array('image/jpeg','inline');
		$mime_types['jpeg']			= array('image/jpeg','inline');
		$mime_types['png']			= array('image/png','inline');
		$mime_types['php']			= 'application/octet-stream';
		$mime_types['txt']			= 'text/plain';
		$mime_types['html']			= 'text/htm';
		$mime_types['html']			= 'text/html';
		$mime_types['bz2']			= 'application/x-bzip2';
		$mime_types['doc']			= 'application/msword';
		$mime_types['wav']			= 'audio/wav';
		$mime_types['gz']			= 'application/x-gzip';
		$mime_types['swf']			= 'application/x-shockwave-flash';
		$mime_types['tgz']			= 'application/gnutar';
		$mime_types['xpm']			= 'image/x-xpixmap';
		$mime_types['zip']			= 'application/zip';
		
		$uext						= explode('.',$u['uname']);
		$uext						= $uext[1];
		
		if(!in_array($uext,array_keys($mime_types)))
		{
			//echo "Cannot handle this file type";
			//exit();
			$mime_types[$uext]		= 'text/plain';
		}
		
		if(is_array($mime_types[$uext]))
		{
			$disposition			= $mime_types[$uext][1];
			$mime_types[$uext]		= $mime_types[$uext][0];
		}
		else {
			$disposition			= 'attachment';
		}
		
		@ob_clean();
		$icebb->settings['enable_gzip'] ? @ob_start('ob_gzhandler') : @ob_start();
		
		@header("Content-type: {$mime_types[$uext]}");
		@header("Content-disposition: {$disposition};filename=\"".basename($u['uname'])."\"");
		@header("Content-length: ".filesize($u['upath']));
		
		// get the file contents (we might have a binary)
		$fh							= @fopen($u['upath'],'rb');
		@fpassthru($fh);
		@fclose($fh);
		
		@ob_end_flush();
		exit();
	}
}
?>