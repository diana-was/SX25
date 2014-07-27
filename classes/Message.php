<?php
/**	APPLICATION:	SX25
*	FILE:			Comment.php
*	DESCRIPTION:	display comment data from database
*	CREATED:		20 October 2010 by Diana De vargas
*	UPDATED:									
*/

class Message_Class extends Model_Class
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

    
	public function get_messages($table,$id,$domain_id,$maxMessages=10)
	{
		$output = array();

		if (empty($table) && empty($domain_id))
			return $output;
			
		/* Get the domain messages */
		if (empty($table))
		{
			$cQuery = "SELECT * FROM messages WHERE message_domain_id = '$domain_id' and message_table is null AND message_status = '1' ORDER BY message_date DESC LIMIT $maxMessages ";
			$cResults = $this->_db->select($cQuery);
		}
		else
		{
			$question = " message_table = '$table' and message_table_id = '$goal_id' and message_domain_id = '$domain_id' ";
			$cQuery = "SELECT * FROM messages WHERE $question AND message_status = '1' ORDER BY message_date DESC LIMIT $maxMessages ";
			$cResults = $this->_db->select($cQuery);
			
			if($this->_db->row_count == 0) 
			{
				$question = " message_table = '$table' and message_table_id = '$goal_id' ";
				$cQuery = "SELECT * FROM messages WHERE $question AND message_status = '1' ORDER BY message_date DESC LIMIT $maxMessages ";
				$cResults = $this->_db->select($cQuery);
			}	
		}		

		$x=0;
		while(($cRow = $this->_db->get_row($cResults, 'MYSQL_ASSOC')) && ($x < $maxMessages)) 
		{
			$output[$x]['COMMENT_TITLE'] 	= $cRow['message_title'];
			$output[$x]['COMMENT_CONTENT'] 	= $cRow['message_content'];
			$output[$x]['COMMENT_AUTHOR'] 	= $cRow['message_author'];
			$output[$x]['COMMENT_DATE'] 	= $cRow['message_date'];
			$x++;					
		}
		return $output;
	}

	public function get_messageInfo($id) 
	{
		$output = array();
		$sql = "select * From messages where id = '$id' Limit 1 ";

		$result = $this->_db->select($sql);
		if($output = $this->_db->get_row($result, 'MYSQL_ASSOC'))
		{
			return $output;		
		}
		else
			return false;
	}
	
	/*
	 *  Save the messaged data in the messages table
	 *  
	 *  array = an array with the field => value. Special field interest is an array stored in json format.
	 *  id  = id to update, 0 if new message
	 *  
	 *  return id or false if fail
	 */
	public function save_message($domain,$domain_id,$array)
	{
		$MyBlogURL 		= "http://$domain";
		$name 			= !empty($array['name'])?mysql_real_escape_string(strip_tags($array['name'])):'';
		$email 			= !empty($array['email'])?mysql_real_escape_string(strip_tags($array['email'])):'';
		$subject 		= !empty($array['subject'])?mysql_real_escape_string(strip_tags($array['subject'])):'';
		$message 		= !empty($array['message'])?mysql_real_escape_string(strip_tags($array['message'])):'';
		$referer		= !empty($_REQUEST['referer'])?$_REQUEST['referer']:'';
		
		if(!empty($message) && !empty($domain) && !empty($domain_id) && is_numeric($domain_id))
		{
			$akismet = new Akismet($MyBlogURL ,$this->WordPressAPIKey);
			$akismet->setCommentAuthor($name);
			$akismet->setCommentContent($message);
			$akismet->setPermalink($MyBlogURL.$referer);
			if(!$akismet->isCommentSpam())
			{
				$data = array();
				$data['`name`'] 		= $name;
				$data['email'] 			= $email;
				$data['`subject`'] 		= $subject;
				$data['`message`'] 		= $message;
				$data['`domain_id`'] 	= $domain_id;
				$data['`domain`'] 		= $domain;
				$data['`message_date`']	= date("Y-m-d");
				$data['`status`'] 		= (!empty($array['status']) && $array['status'] == 1)?1:0;
				$data['`reply`'] 		= '';
				
				$id = $this->_db->insert_array('messages', $data);
				return $id;
			}
		}

		return false;
	}

	public function del_message($id)
	{
		$dQuery = "DELETE FROM messages WHERE id = '$id'";
		if($this->_db->delete($dQuery))
			return true;
		
		return false;
	}
    
}