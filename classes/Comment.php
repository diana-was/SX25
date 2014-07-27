<?php
/**	APPLICATION:	SX25
*	FILE:			Comment.php
*	DESCRIPTION:	display comment data from database
*	CREATED:		20 October 2010 by Diana De vargas
*	UPDATED:									
*/

class Comment_Class extends Model_Class
{
	private 	$_db;
	private static $_Object; 
	private 	$_WordPressAPIKey = '92763e4423cb';
	
    /**
     * constructor : set up the variables
     *
     * @param dbobject $db db_class object

     * @return object
     */
	public function __construct(db_class $db)
	{
		$this->_db = $db; 
		self::$_Object = $this;
		return self::$_Object;
	}


    /**
     * Get the controller static object
     *
     * @return self
     */
    public static function getInstance(db_class $db) 
    {
    	$class = __CLASS__;
    	if (!isset(self::$_Object)) {
    		return new $class($db);
    	}	
    	return self::$_Object;
    }

    
	public function get_comments($table,$id,$domain_id,$maxComments=10)
	{
		$output = array();

		if (empty($table) && empty($domain_id))
			return $output;
			
		/* Get the domain comments */
		if (empty($table))
		{
			$cQuery = "SELECT * FROM comments WHERE comment_domain_id = '$domain_id' and comment_table is null AND comment_status = '1' ORDER BY comment_date DESC LIMIT $maxComments ";
			$cResults = $this->_db->select($cQuery);
		}
		else
		{
			$question = " comment_table = '$table' and comment_table_id = '$id' and comment_domain_id = '$domain_id' ";
			$cQuery = "SELECT * FROM comments WHERE $question AND comment_status = '1' ORDER BY comment_date DESC LIMIT $maxComments ";
			$cResults = $this->_db->select($cQuery);
			
			if($this->_db->row_count == 0) 
			{
				$question = " comment_table = '$table' and comment_table_id = '$id' ";
				$cQuery = "SELECT * FROM comments WHERE $question AND comment_status = '1' ORDER BY comment_date DESC LIMIT $maxComments ";
				$cResults = $this->_db->select($cQuery);
			}	
		}		

		$x=0;
		while(($cRow = $this->_db->get_row($cResults, 'MYSQL_ASSOC')) && ($x < $maxComments)) 
		{
			$output[$x]['COMMENT_TITLE'] 	= $cRow['comment_title'];
			$output[$x]['COMMENT_CONTENT'] 	= $cRow['comment_content'];
			$output[$x]['COMMENT_AUTHOR'] 	= $cRow['comment_author'];
			$output[$x]['COMMENT_DATE'] 	= $cRow['comment_date'];
			$x++;					
		}
		return $output;
	}

	public function get_commentInfo($id) 
	{
		$output = array();
		$sql = "select * From comments where comment_id = '$id' Limit 1 ";

		$result = $this->_db->select($sql);
		if($output = $this->_db->get_row($result, 'MYSQL_ASSOC'))
		{
			return $output;		
		}
		else
			return false;
	}
	
	/*
	 *  Save the commentd data in the comments table
	 *  
	 *  array = an array with the field => value. Special field interest is an array stored in json format.
	 *  id  = id to update, 0 if new comment
	 *  
	 *  return id or false if fail
	 */
	public function save_comment($table,$id,$domain_id,$domain,$array)
	{
		$MyBlogURL 			= "http://$domain";
		$comment_author 	= !empty($array['author'])?mysql_real_escape_string(strip_tags($array['author'])):'';
		$comment_title 		= !empty($array['title'])?mysql_real_escape_string(strip_tags($array['title'])):'';
		$comment_content 	= !empty($array['content'])?mysql_real_escape_string(strip_tags($array['content'])):'';
		$referer			= !empty($_REQUEST['referer'])?$_REQUEST['referer']:'';
		
		if(!empty($comment_content) && !empty($domain) && !empty($domain_id) && is_numeric($domain_id))
		{
			$akismet = new Akismet($MyBlogURL ,$this->WordPressAPIKey);
			$akismet->setCommentAuthor($comment_author);
			$akismet->setCommentContent($comment_content);
			$akismet->setPermalink($MyBlogURL.$referer);
			if(!$akismet->isCommentSpam())
			{
				$data = array();
				$data['comment_author'] 	= $comment_author;
				$data['comment_title'] 		= $comment_title;
				$data['comment_content'] 	= $comment_content;
				$data['comment_domain_id'] 	= $domain_id;
				$data['comment_domain'] 	= $domain;
				$data['comment_date'] 		= date("Y-m-d");
				$data['comment_status'] 	= (!empty($array['status']) && $array['status'] == 1)?1:0;
				if (!empty($table) && !empty($id) && is_numeric($id))
				{
					$data['comment_table']	= $table;
					$data['comment_table_id']= $id;
				}
				
				$id = $this->_db->insert_array('comments', $data);
				return $id;
			}
		}

		return false;
	}

	public function del_comment($id)
	{
		$dQuery = "DELETE FROM comments WHERE comment_id = '$id'";
		if($this->_db->delete($dQuery))
			return true;
		
		return false;
	}
    
}