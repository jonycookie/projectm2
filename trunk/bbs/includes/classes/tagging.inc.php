<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 1.0
//******************************************************//
// tag class
// $Id$
//******************************************************//

/**
 * Post/topic tagging class
 * 
 * @package		IceBB
 * @version		1.0
 * @date		$Date$
 */
class tagging
{
	function add_tag($type,$id,$tag)
	{
		global $icebb,$db;
		
		$tag				= strtolower($tag);
		
		// strip tag of useless punctuation, etc.
		//$tag				= preg_replace('/[^\w\n ]+/',null,preg_replace('/\s{2,}/',' ',$tag));
		$tag				= preg_replace('/[\n]+/',null,preg_replace('/\s{2,}/',' ',$tag));
		
		$db->query("SELECT COUNT(*) FROM icebb_tagged AS tagged LEFT JOIN icebb_tags AS tag ON tag.id=tagged.tag_id WHERE tag.tag='{$tag}' AND tagged.tag_type='{$type}' AND tagged.tag_objid='{$id}'");
		$count				= $db->fetch_row();
		if($count['COUNT(*)'] > 0)
		{
			return false;
		}
		
		$db->query("SELECT * FROM icebb_tags WHERE tag='{$tag}' AND type='{$type}' AND owner='{$icebb->user['id']}'");
		if($db->get_num_rows()>=1)
		{
			$tag			= $db->fetch_row();
			$db->query("UPDATE icebb_tags SET count=count+1 WHERE id='{$tag['id']}' AND owner='{$icebb->user['id']}'");
			$insert_id		= $tag['id'];
		}
		else {
			$db->insert('icebb_tags',array(
				'tag'		=> $tag,
				'type'		=> $type,
				'count'		=> 1,
				'owner'		=> $icebb->user['id'],
			));
		
			$insert_id		= $db->get_insert_id();
		}
		
		$db->insert('icebb_tagged',array(
			'tag_id'		=> $insert_id,
			'tag_uid'		=> $icebb->user['id'],
			'tag_type'		=> $type,
			'tag_objid'		=> $id,
			'tag_time'		=> time(),
		));
	}
	
	function del_tag($type,$id,$tag)
	{
		$db->query("UPDATE icebb_tags SET count=count-1 WHERE tag='{$tag}' AND owner='{$icebb->user['id']}'");
		$db->query("DELETE FROM icebb_tagged WHERE tag_tag_type='{$type}' AND tag_objid='{$id}'");
	}
	
	function split_tags($tags_raw)
	{
		$in_quotes			= 0;
		$counter			= 0;
		for($i=0;$i<=strlen($tags_raw);$i++)
		{
			$chr			= $tags_raw{$i};
			if($chr			== '"')
			{
				$in_quotes= $in_quotes ? 0 : 1;
				$chr		= '';
			}
			
			if(!$in_quotes && $chr==' ')
			{
				$chr		= '';
				$counter++;
			}
			
			$tags[$counter].= $chr;
		}
		
		return $tags;
	}
}
?>
